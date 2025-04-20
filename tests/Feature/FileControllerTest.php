<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FileUpload\CsvImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class FileControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Usuário para autenticação nos testes.
     */
    protected User $user;

    /**
     * Configuração inicial para os testes.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.file_max_size' => 20]);

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
     * Teste de upload de arquivo CSV válido.
     *
     * @return void
     */
    public function test_valid_csv_upload()
    {
        $token = $this->getAuthToken();

        $csvPath = storage_path("app/uploads/test.csv");

        $this->assertFileExists($csvPath, 'Arquivo de teste não encontrado: ' . $csvPath);

        $file = new UploadedFile(
            $csvPath,
            'example.csv',
            'text/csv',
            null,
            true
        );

        // Mock do serviço de importação
        $mockService = Mockery::mock(CsvImportService::class);
        $mockService->shouldReceive('upload')
            ->once()
            ->with(Mockery::type(UploadedFile::class)) // Usamos type() em vez de with() exato
            ->andReturn([
                'import_process_id' => '123'
            ]);

        $this->app->instance(CsvImportService::class, $mockService);

        // Enviar requisição autenticada
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/upload', [
            'file' => $file
        ]);

        // Verificar resposta
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Importação iniciada com sucesso.',
                'data' => [
                    'import_process_id' => '123'
                ]
            ]);
    }

    /**
     * Teste de upload sem enviar arquivo.
     *
     * @return void
     */
    public function test_upload_without_file()
    {
        // Obter token de autenticação
        $token = $this->getAuthToken();

        // Enviar requisição autenticada sem arquivo
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/upload', []);

        // Verificar erro de validação
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /**
     * Teste de upload com arquivo não CSV.
     *
     * @return void
     */
    public function test_upload_non_csv_file()
    {
        // Obter token de autenticação
        $token = $this->getAuthToken();

        Storage::fake('local');

        // Criar um arquivo não CSV
        $file = UploadedFile::fake()->create('test.txt', 1024);

        // Enviar requisição autenticada
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/upload', [
            'file' => $file
        ]);

        // Verificar erro de validação
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /**
     * Teste de upload com arquivo CSV vazio.
     *
     * @return void
     */
    public function test_upload_empty_csv()
    {
        // Obter token de autenticação
        $token = $this->getAuthToken();

        Storage::fake('local');

        // Criar um arquivo CSV vazio
        $file = UploadedFile::fake()->create('empty.csv', 0);

        // Enviar requisição autenticada
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/upload', [
            'file' => $file
        ]);

        // Verificar erro de validação
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /**
     * Teste de upload com arquivo CSV grande demais.
     *
     * @return void
     */
    public function test_upload_too_large_csv()
    {
        // Obter token de autenticação
        $token = $this->getAuthToken();

        Storage::fake('local');

        // Criar um arquivo CSV muito grande (assumindo limite de 10MB)
        $file = UploadedFile::fake()->create('large.csv', 30 * 1024);

        // Enviar requisição autenticada
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/upload', [
            'file' => $file
        ]);

        // Verificar erro de validação
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /**
     * Teste com erro no serviço de importação.
     *
     * @return void
     */
    public function test_service_exception_handling()
    {
        // Obter token de autenticação
        $token = $this->getAuthToken();

        Storage::fake('local');

        // Criar um arquivo CSV de teste
        $file = UploadedFile::fake()->create('test2.csv', 1024);

        // Mock do serviço que lança exceção
        $mockService = Mockery::mock(CsvImportService::class);
        $mockService->shouldReceive('upload')
            ->once()
            ->with($file)
            ->andThrow(new \Exception('Erro ao processar o arquivo CSV'));

        $this->app->instance(CsvImportService::class, $mockService);

        // Enviar requisição autenticada
        $this->withoutExceptionHandling()
            ->expectException(\Exception::class);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/upload', [
            'file' => $file
        ]);
    }

    /**
     * Teste de autenticação para a rota de upload.
     *
     * @return void
     */
    public function test_authentication_required()
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('test.csv', 1024);

        // Enviar requisição sem token de autenticação
        $response = $this->postJson('/api/upload', [
            'file' => $file
        ]);

        // Verificar erro de autenticação
        $response->assertStatus(401);
    }

    /**
     * Teste com token expirado ou inválido.
     *
     * @return void
     */
    public function test_invalid_token()
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('test.csv', 1024);

        // Enviar requisição com token inválido
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
        ])->postJson('/api/upload', [
            'file' => $file
        ]);

        // Verificar erro de autenticação
        $response->assertStatus(401);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
