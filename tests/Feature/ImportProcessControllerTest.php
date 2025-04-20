<?php

namespace Tests\Feature;

use App\Enum\ImportProcessStatus;
use App\Models\ImportProcess;
use App\Models\ImportProcessError;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ImportProcessControllerTest extends TestCase
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
     * Teste para verificar se o processo de importação é retornado com sucesso.
     *
     * @return void
     */
    public function test_can_get_import_process_by_id()
    {
        // Obter token de autenticação
        $token = $this->getAuthToken();

        // Criar um processo de importação completo usando a factory
        $importProcess = ImportProcess::factory()->completed()->create([
            'file_name' => 'test_import.csv',
            'processed_rows' => 100,
            'users_created' => 95,
            'failed_rows' => 5,
        ]);

        // Criar alguns erros de importação associados usando a factory atualizada
        ImportProcessError::factory()->count(3)->create([
            'import_process_id' => $importProcess->id,
        ]);

        // Fazer requisição para o endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/import-status/{$importProcess->id}");

        // Verificar resposta
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'file_name',
                    'status',
                    'processed_rows',
                    'users_created',
                    'failed_rows',
                    'error_message',
                    'completed_at',
                    'created_at',
                ]
            ])
            ->assertJson([
                'message' => 'Processo de importação recuperado com sucesso.',
                'data' => [
                    'file_name' => 'test_import.csv',
                    'processed_rows' => 100,
                    'users_created' => 95,
                    'failed_rows' => 5,
                ]
            ]);
    }

    /**
     * Teste para verificar comportamento quando o processo de importação não existe.
     *
     * @return void
     */
    public function test_returns_404_when_import_process_not_found()
    {
        // Obter token de autenticação
        $token = $this->getAuthToken();

        // Usar um ID que não existe
        $nonExistentId = 999;

        // Fazer requisição para o endpoint com ID inexistente
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/import-status/{$nonExistentId}");

        // Verificar resposta 404
        $response->assertStatus(404);
    }

    /**
     * Teste para verificar se a rota requer autenticação.
     *
     * @return void
     */
    public function test_import_process_route_requires_authentication()
    {
        // Criar um processo de importação para teste
        $importProcess = ImportProcess::factory()->create();

        // Tentar acessar a rota sem autenticação
        $response = $this->getJson("/api/import-status/{$importProcess->id}");

        // Verificar que o acesso foi negado
        $response->assertStatus(401);
    }

    /**
     * Teste para verificar diferentes status do processo de importação.
     *
     * @return void
     */
    public function test_can_get_import_process_with_different_statuses()
    {
        // Obter token de autenticação
        $token = $this->getAuthToken();

        // Testar com status Pendente usando a factory state
        $pendingProcess = ImportProcess::factory()->pending()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/import-status/{$pendingProcess->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Processo de importação recuperado com sucesso.',
                'data' => [
                    'id' => $pendingProcess->id,
                    'file_name' => $pendingProcess->file_name,
                    'status' => ImportProcessStatus::PENDING->name,
                    'processed_rows' => 0,
                    'users_created' => 0,
                    'failed_rows' => 0,
                    'completed_at' => null,
                ]
            ]);

        // Testar com status Em Processamento usando a factory state
        $processingProcess = ImportProcess::factory()->processing()->create([
            'processed_rows' => 50,
            'users_created' => 45,
            'failed_rows' => 5,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/import-status/{$processingProcess->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => ImportProcessStatus::PROCESSING->name,
                    'completed_at' => null,
                ]
            ]);

        // Testar com status com Falha usando a factory state
        $failedProcess = ImportProcess::factory()->failed()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/import-status/{$failedProcess->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => ImportProcessStatus::FAILED->name,
                ]
            ]);

        // Verificar se a mensagem de erro e a data de conclusão estão presentes
        $this->assertNotNull($response->json('data.error_message'));
        $this->assertNotNull($response->json('data.completed_at'));

        // Testar com status Concluído usando a factory state
        $completedProcess = ImportProcess::factory()->completed()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/import-status/{$completedProcess->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => ImportProcessStatus::DONE->name,
                ]
            ]);

        // Verificar se não há mensagem de erro e se a data de conclusão está presente
        $this->assertNull($response->json('data.error_message'));
        $this->assertNotNull($response->json('data.completed_at'));
    }
}
