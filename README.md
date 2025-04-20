# CSV Processor

Um microserviço para processamento de arquivos CSV, construído com Laravel e Docker.

## Sobre o Projeto

CSV Processor é uma aplicação web que permite o upload, processamento e análise de arquivos CSV. A aplicação utiliza fila de processamento para lidar com arquivos grandes de forma eficiente, oferecendo uma interface intuitiva para visualizar e trabalhar com dados tabulares.

## Tecnologias

- Laravel 12
- PHP 8.4
- MySQL 8.0
- Docker & Docker Compose
- Queue Workers

## Requisitos

- Docker
- Docker Compose

Não é necessário ter PHP, Composer ou MySQL instalados em sua máquina local.

## Configuração e Instalação

### 1. Configurar Variáveis de Ambiente

Copie o arquivo de exemplo `.env.example` para criar seu arquivo `.env`:

```shell script
  cp .env.example .env
```

### 2. Iniciar Contêineres Docker

```shell script
  docker-compose up -d
```

### 3. Acessar a API
Após iniciar os contêineres, a API estará acessível em:
```
http://localhost:8080
```

## Estrutura Docker
- **api**: Servidor web Laravel
- **db**: Banco de dados MySQL
- **worker**: Processador de filas para operações assíncronas

## Comandos Úteis

### Gerar Arquivo CSV
 Onde rows é o número de linhas que deseja criar
```shell script
  docker-compose exec worker php artisan app:generate-csv {rows}
```
O arquivo será gerado em storage/app/uploads/test.csv
Este arquivo também é usado para os teste na FileController.

### Executar Testes

```shell script
  docker-compose exec api php artisan test
```

### Acessar o Banco de Dados

```shell script
  docker-compose exec db mysql -u sail -ppassword laravel
```

## Processamento de Arquivos CSV

```bash
  docker-compose exec db mysql -u sail -ppassword laravel
```
# Guia de Utilização da API
Este guia fornece instruções passo a passo para utilizar a API do sistema CSV Processor. As rotas principais incluem autenticação, upload de arquivos, e monitoramento do status de importação.

## Sobre o Processamento de Arquivos
O sistema foi projetado para lidar com volumes massivos de dados de forma eficiente. O processo de upload envia o arquivo para `/storage/app/uploads` e as linhas são processadas em fila, utilizando uma lógica de processamento em chunks para otimizar o desempenho.
Você pode acompanhar o progresso do processamento através da rota `/api/import-status/{id}`.

## Recursos para Testes
Para auxiliar nos testes, os seguintes recursos estão disponíveis na pasta `workspace`:
- **Collection do Postman**: Importável para testes rápidos da API
- **Arquivo de Ambiente do Postman**: Contém variáveis de ambiente para a collection
- **Arquivo HTTP**: Alternativa para executar requisições diretamente de editores como VS Code ou JetBrains IDEs

Você pode escolher a opção que melhor se adequa ao seu fluxo de trabalho para testar a API.

## Passo a Passo
### 1. Criar um Usuário
Primeiro, você precisa criar um usuário para obter acesso às rotas protegidas:
``` http
POST http://localhost:8000/api/users
Accept: application/json
Content-Type: application/json

{
  "name": "Avaliador Teste",
  "email": "avaliador@example.com",
  "birth_date": "01/01/1990",
  "password": "12345678"
}
```
### 2. Realizar Login
Após criar o usuário, faça login para obter o token JWT:
``` http
POST http://localhost:8000/api/login
Accept: application/json
Content-Type: application/json

{
  "email": "avaliador@example.com",
  "password": "12345678"
}
```
A resposta incluirá um token JWT que você deve copiar para usar nas próximas requisições.
### 3. Acessar Rotas Protegidas
Com o token em mãos, adicione-o ao cabeçalho de autorização das requisições protegidas:
``` http
Authorization: Bearer seu_token_aqui
```
### 4. Upload de Arquivo CSV
Para realizar o upload de um arquivo CSV:
``` http
POST http://localhost:8000/api/upload
Accept: application/json
Authorization: Bearer seu_token_aqui
Content-Type: multipart/form-data

[Selecione o arquivo CSV]
```
Na pasta `Workspace`, há um arquivo CSV disponível para testes. Alternativamente, você pode usar o comando fornecido para gerar um CSV com a quantidade de linhas desejada.
### 5. Verificar Status da Importação
Para acompanhar o status do processamento:
``` http
GET http://localhost:8000/api/import-status/{id}
Accept: application/json
Authorization: Bearer seu_token_aqui
```
Substitua `{id}` pelo ID do processo de importação retornado pela API de upload.
### 6. Verificar Erros do Processo de Importação (se houver)
``` http
GET http://localhost:8000/api/import-process/{id}/errors
Accept: application/json
Authorization: Bearer seu_token_aqui
```
### 7. Verificar Saúde do Worker
``` http
GET http://localhost:8000/api/worker-health?verbose=true
Accept: application/json
```
## Nota sobre Arquivos Grandes
Para arquivos CSV muito grandes (acima de 60MB), pode ser necessário ajustar as configurações do PHP no arquivo `.env`:
``` dotenv
PHP_POST_MAX_SIZE=60
PHP_UPLOAD_MAX_FILESIZE=60
```
Aumente esses valores conforme necessário para permitir o upload de arquivos maiores.


Agora você está pronto para realizar todos os testes da API! 🚀
