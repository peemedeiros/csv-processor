<?php

namespace Tests\Feature;

use App\Models\ImportProcess;
use App\Models\ImportProcessError;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ImportProcessErrorControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Configuração inicial para os testes.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Criar usuário para autenticação
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    /**
     * Função auxiliar para obter token JWT.
     *
     * @return string
     */
    protected function getAuthToken(): string
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        return $response->json('access_token');
    }

    /**
     * Teste para verificar se os erros de um processo de importação são retornados corretamente.
     *
     * @return void
     */
    public function test_can_get_import_process_errors()
    {
        // Obter token de autenticação
        $token = $this->getAuthToken();

        // Criar um processo de importação
        $importProcess = ImportProcess::factory()->failed()->create([
            'failed_rows' => 5,
        ]);

        // Criar erros associados ao processo
        $errors = ImportProcessError::factory()->count(5)->create([
            'import_process_id' => $importProcess->id,
        ]);

        // Fazer requisição para o endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/import-process/{$importProcess->id}/errors");

        // Verificar resposta
        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'import_process_id',
                        'row_index',
                        'row_data',
                        'error_message',
                        'error_type',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);

        // Verificar que os IDs dos erros retornados correspondem aos que criamos
        $returnedErrorIds = collect($response->json('data'))->pluck('id')->toArray();
        $createdErrorIds = $errors->pluck('id')->toArray();
        $this->assertEquals(
            sort($createdErrorIds),
            sort($returnedErrorIds)
        );
    }

    /**
     * Teste para verificar se os diferentes tipos de erros são retornados corretamente.
     *
     * @return void
     */
    public function test_returns_different_error_types()
    {
        // Obter token de autenticação
        $token = $this->getAuthToken();

        // Criar um processo de importação
        $importProcess = ImportProcess::factory()->failed()->create([
            'failed_rows' => 3,
        ]);

        // Criar erros com diferentes tipos
        $validationError = ImportProcessError::factory()->validationError()->create([
            'import_process_id' => $importProcess->id,
        ]);

        $databaseError = ImportProcessError::factory()->databaseError()->create([
            'import_process_id' => $importProcess->id,
        ]);

        $formatError = ImportProcessError::factory()->formatError()->create([
            'import_process_id' => $importProcess->id,
        ]);

        // Fazer requisição para o endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/import-process/{$importProcess->id}/errors");

        // Verificar resposta
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        // Verificar que os diferentes tipos de erro estão presentes
        $errors = collect($response->json('data'));

        $this->assertTrue($errors->contains(fn($error) =>
            $error['error_type'] == 'validation'
        ));

        $this->assertTrue($errors->contains(fn($error) =>
            $error['error_type'] == 'database'
        ));

        $this->assertTrue($errors->contains(fn($error) =>
            $error['error_type'] == 'format'
        ));

        // Verificar que o row_data é retornado como array para cada erro
        foreach ($errors as $error) {
            $this->assertIsArray($error['row_data']);
            $this->assertArrayHasKey('name', $error['row_data']);
            $this->assertArrayHasKey('email', $error['row_data']);
        }
    }

    /**
     * Teste para verificar se o endpoint retorna uma lista vazia quando não há erros.
     *
     * @return void
     */
    public function test_returns_empty_collection_when_no_errors_exist()
    {
        // Obter token de autenticação
        $token = $this->getAuthToken();

        // Criar um processo de importação sem erros
        $importProcess = ImportProcess::factory()->completed()->create([
            'failed_rows' => 0,
        ]);

        // Fazer requisição para o endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/import-process/{$importProcess->id}/errors");

        // Verificar resposta
        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJsonStructure([
                'data',
                'links',
                'meta'
            ]);
    }

    public function test_returns_empty_collection_when_import_process_not_found()
    {
        // Obter token de autenticação
        $token = $this->getAuthToken();

        // Usar um ID que não existe
        $nonExistentId = 999;

        // Fazer requisição para o endpoint com ID inexistente
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/import-process/{$nonExistentId}/errors");

        // Verificar que retorna 200 com uma coleção vazia
        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJsonStructure([
                'data',
                'links',
                'meta'
            ]);

        // Verificar que o total é zero
        $this->assertEquals(0, $response->json('meta.total'));
    }

    /**
     * Teste para verificar se a rota requer autenticação.
     *
     * @return void
     */
    public function test_errors_route_requires_authentication()
    {
        // Criar um processo de importação para teste
        $importProcess = ImportProcess::factory()->create();

        // Tentar acessar a rota sem autenticação
        $response = $this->getJson("/api/import-process/{$importProcess->id}/errors");

        // Verificar que o acesso foi negado
        $response->assertStatus(401);
    }

    /**
     * Teste para verificar a paginação dos erros.
     *
     * @return void
     */
    public function test_errors_are_paginated()
    {
        // Obter token de autenticação
        $token = $this->getAuthToken();

        // Criar um processo de importação
        $importProcess = ImportProcess::factory()->failed()->create([
            'failed_rows' => 25,
        ]);

        // Criar mais erros do que cabem em uma página (assumindo paginação padrão de 15)
        ImportProcessError::factory()->count(25)->create([
            'import_process_id' => $importProcess->id,
        ]);

        // Fazer requisição para o endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/import-process/{$importProcess->id}/errors");

        // Verificar resposta
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next'
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links',
                    'path',
                    'per_page',
                    'to',
                    'total'
                ]
            ]);

        // Verificar que há mais de uma página
        $this->assertTrue($response->json('meta.last_page') > 1);

        // Verificar que o total de erros é 25
        $this->assertEquals(25, $response->json('meta.total'));

        // Verificar que o número de erros na primeira página é menor que o total
        $this->assertLessThan(25, count($response->json('data')));

        // Verificar acesso à segunda página
        $responsePage2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/import-process/{$importProcess->id}/errors?page=2");

        $responsePage2->assertStatus(200);

        // Verificar que os erros na página 2 são diferentes dos erros na página 1
        $page1Ids = collect($response->json('data'))->pluck('id')->toArray();
        $page2Ids = collect($responsePage2->json('data'))->pluck('id')->toArray();

        $this->assertEmpty(array_intersect($page1Ids, $page2Ids));
    }
}
