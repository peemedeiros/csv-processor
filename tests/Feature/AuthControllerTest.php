<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste de login com credenciais válidas.
     *
     * @return void
     */
    public function test_user_can_login_with_valid_credentials()
    {
        // Criar usuário de teste
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'birth_date' => '01/01/1990'
        ]);

        // Tentar fazer login
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'birth_date' => '01/01/1990',
        ]);

        // Verificar resposta
        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ]);
    }

    /**
     * Teste de login com email inválido.
     *
     * @return void
     */
    public function test_user_cannot_login_with_invalid_email()
    {
        // Criar usuário de teste
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'birth_date' => '01/01/1990'
        ]);

        // Tentar fazer login com email incorreto
        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'password',
            'birth_date' => '01/01/1990'
        ]);

        // Verificar resposta
        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized',
            ]);
    }

    /**
     * Teste de login com senha inválida.
     *
     * @return void
     */
    public function test_user_cannot_login_with_invalid_password()
    {
        // Criar usuário de teste
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'birth_date' => '01/01/1990'
        ]);

        // Tentar fazer login com senha incorreta
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong_password',
            'birth_date' => '01/01/1990'
        ]);

        // Verificar resposta
        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized',
            ]);
    }

    /**
     * Teste de login com campos ausentes.
     *
     * @return void
     */
    public function test_login_requires_email_and_password()
    {
        // Tentar fazer login sem enviar email e senha
        $response = $this->postJson('/api/login', []);

        // Verificar resposta
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Teste para verificar o formato do token.
     *
     * @return void
     */
    public function test_login_returns_expected_token_format()
    {
        // Criar usuário de teste
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'birth_date' => '01/01/1990'
        ]);

        // Tentar fazer login
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'birth_date' => '01/01/1990'
        ]);

        // Verificar formato do token na resposta
        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ])
            ->assertJson([
                'token_type' => 'bearer',
            ]);

        // Verificar se o token tem conteúdo
        $responseData = $response->json();
        $this->assertNotEmpty($responseData['access_token']);
        $this->assertIsString($responseData['access_token']);
    }

    /**
     * Teste para verificar o middleware de log da API.
     *
     * @return void
     */
    public function test_api_logs_are_created_on_login_attempt()
    {
        // Supondo que você tenha implementado o middleware de log como discutido anteriormente

        // Criar usuário de teste
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'birth_date' => '01/01/1990'
        ]);

        // Tentar fazer login
        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'birth_date' => '01/01/1990'
        ]);

        // Verificar se um log foi criado na tabela api_logs
        $this->assertDatabaseHas('api_logs', [
            'method' => 'POST',
            'url' => url('/api/login'), // Ajuste conforme a URL exata
            'status_code' => 200,
        ]);
    }
}
