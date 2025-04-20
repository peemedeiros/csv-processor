<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;


    public function test_index_returns_paginated_users()
    {
        // Criar um usuário para autenticação
        $authUser = User::factory()->create([
            'email' => 'auth@example.com',
            'password' => Hash::make('password'),
        ]);

        // Criar alguns usuários adicionais para teste
        User::factory()->count(25)->create();

        // Fazer login para obter o token JWT
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'auth@example.com',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        // Fazer requisição para o endpoint index com o token de autenticação
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users');

        // Verificar resposta
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'birth_date',
                    ]
                ],
                'links',
                'meta',
            ]);

        // Verificar se a paginação está funcionando (20 itens por página como definido no controller)
        $this->assertCount(20, $response->json('data'));
    }

    /**
     * Teste para verificar se a requisição store cria um usuário com sucesso.
     *
     * @return void
     */
    public function test_store_creates_new_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'birth_date' => '01/01/1990',
        ];

        // Fazer requisição para o endpoint store
        $response = $this->postJson('/api/users', $userData);

        // Verificar resposta
        $response->assertStatus(201) // Assumindo que você retorna 201 Created
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'birth_date',
            ]
        ]);

        // Verificar se o usuário foi criado no banco de dados
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Teste para verificar validação do email na criação do usuário.
     *
     * @return void
     */
    public function test_store_validates_email()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'birth_date' => '01/01/1990',
        ];

        // Fazer requisição para o endpoint store
        $response = $this->postJson('/api/users', $userData);

        // Verificar resposta
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Teste para verificar validação de email único na criação do usuário.
     *
     * @return void
     */
    public function test_store_validates_unique_email()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'birth_date' => '01/01/1990',
        ];

        // Criar um usuário
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'birth_date' => '01/01/1990',
        ]);

        $userData = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'birth_date' => '01/01/1990',
        ];

        // Fazer requisição para o endpoint store
        $response = $this->postJson('/api/users', $userData);

        // Verificar resposta
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Teste para verificar validação da senha na criação do usuário.
     *
     * @return void
     */
    public function test_store_validates_password()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short', // Senha muito curta
            'password_confirmation' => 'short',
            'birth_date' => '01/01/1990',
        ];

        // Fazer requisição para o endpoint store
        $response = $this->postJson('/api/users', $userData);

        // Verificar resposta
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Teste para verificar validação da data de nascimento na criação do usuário.
     *
     * @return void
     */
    public function test_store_validates_birth_date_format()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'birth_date' => 'invalid-date', // Data inválida
        ];

        // Fazer requisição para o endpoint store
        $response = $this->postJson('/api/users', $userData);

        // Verificar resposta
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['birth_date']);
    }

    /**
     * Teste para verificar se todos os campos obrigatórios são validados.
     *
     * @return void
     */
    public function test_store_validates_required_fields()
    {
        // Fazer requisição para o endpoint store sem dados
        $response = $this->postJson('/api/users', []);

        // Verificar resposta
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'birth_date']);
    }

    /**
     * Teste para verificar se o usuário é criado com a senha corretamente hashada.
     *
     * @return void
     */
    public function test_store_creates_user_with_hashed_password()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'birth_date' => '01/01/1990',
        ];

        // Fazer requisição para o endpoint store
        $this->postJson('/api/users', $userData);

        // Buscar o usuário criado
        $user = User::where('email', 'test@example.com')->first();

        // Verificar se a senha está hashada
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password123', $user->password));
    }

    /**
     * Teste para verificar se a resposta do API não expõe dados sensíveis.
     *
     * @return void
     */
    public function test_store_response_does_not_contain_sensitive_data()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'birth_date' => '01/01/1990',
        ];

        // Fazer requisição para o endpoint store
        $response = $this->postJson('/api/users', $userData);

        // Verificar resposta não contém campos sensíveis
        $response->assertStatus(201)
            ->assertJsonMissing(['password'])
            ->assertJsonMissing(['remember_token']);
    }
}
