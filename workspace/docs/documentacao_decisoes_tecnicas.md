# Documentação de Decisões Técnicas - CSV Processor

Durante o desenvolvimento do sistema de processamento de arquivos CSV, diversas decisões foram necessárias para alcançar um equilíbrio entre eficiência, escalabilidade e simplicidade. A seguir, apresento as principais decisões técnicas tomadas durante o projeto, juntamente com as justificativas que sustentam cada escolha.

---

## Estratégia de Processamento de Arquivos CSV

Uma das decisões mais importantes no desenvolvimento foi a escolha da estratégia para processar as linhas do CSV. O objetivo era encontrar uma solução eficiente do ponto de vista de uso de recursos computacionais (CPU, memória e I/O) e também capaz de lidar com grandes volumes de dados sem comprometer a escalabilidade.

### Primeiras Abordagens para o Processamento de Linhas
1. **Processamento linha a linha**:
    - A implementação inicial realizava a leitura e validação de cada linha do CSV em tempo de execução, salvando os registros individualmente no banco de dados logo após o processamento.
    - **Problemas Identificados**:
        - **Ineficiente**: Este método resultava em alta carga no banco de dados, realizando uma operação `INSERT` por registro.
        - **Tempo de processamento**: Arquivos maiores aumentavam o custo computacional, resultando em risco de timeout e tempo de execução excessivo.

2. **Carregamento completo em memória**:
    - Durante a segunda abordagem, as linhas dos arquivos eram carregadas integralmente na memória antes de serem validadas e inseridas em lote no banco de dados.
    - **Problemas Identificados**:
        - **Consumo elevado de memória**: Ineficiente para arquivos grandes, apresentando o risco de estouro de memória (out of memory).
        - **Baixa escalabilidade**: Tornava o sistema muito dependente de recursos da máquina onde a aplicação está sendo executada.

---

### Solução Implementada: Processamento em Chunks com Transações
Uma abordagem híbrida foi escolhida, combinando o processamento linha a linha com a execução em lote.
- **Como Funciona**:
    - O arquivo CSV é lido em chunks (lotes) de tamanho fixo.
    - Cada chunk é carregado na memória, validado, processado e salvo no banco de dados como uma operação em lote.
    - Após a persistência, a memória é liberada antes de processar o próximo lote.

- **Detalhes Técnicos**:
    - **Transações no Banco de Dados**: Todas as inserções em lote são realizadas dentro de transações para:
        - Evitar inconsistências nos dados em caso de erros.
        - Reduzir o overhead no banco de dados.
        - Prevenir deadlocks durante operações concorrentes.

- **Vantagens**:
    - **Controle de memória**: O uso de memória é constantemente liberado, permitindo que arquivos grandes sejam processados.
    - **Melhor desempenho**: Inserções em lote aumentam significativamente a performance na interação com o banco de dados.
    - **Escalabilidade**: Adapta-se bem a diferentes tamanhos de arquivo e recursos computacionais disponíveis.

---

## Simplificação do Código para Melhor Performance

No início do desenvolvimento, as validações de campos eram realizadas seguindo princípios de Object Calisthenics e SOLID, com a criação de classes independentes para cada campo como Nome, Email e Data de Nascimento.

### Problemas Identificados:
- **Alto custo de criação de objetos**: A criação repetitiva de objetos para cada validação, especialmente para arquivos extremamente grandes, apresentava um impacto significativo no tempo de processamento.
- **Baixa Performance**: Em um teste realizado com um arquivo de 1.000.000 de linhas, o tempo total de processamento foi de 28 minutos.

### Solução: Centralização em Helpers
Foi decidido simplificar a lógica, concentrando todas as validações em um único Helper, evitando a criação desnecessária de objetos.

- **Resultado**:
    - Melhorou exponencialmente a performance do processamento.
    - Reduziu a complexidade operacional durante a execução de validações.
    - Atendeu ao requisito de performance, mesmo sacrificando um pouco os princípios de design arquitetural.

---

## Rastreabilidade e Monitoramento

### Logs
Uma tabela foi criada no banco de dados para registrar todas as requisições feitas aos endpoints. Isso foi implementado para garantir rastreabilidade e possibilitar auditorias.

- **Por que no banco de dados?**
    - Facilita consultas estruturadas e detalhadas diretamente nos logs.
    - Permite agregar todas as informações em um local centralizado.
    - **Implementação via Middleware**: O Middleware simplifica a adição do log a múltiplas rotas.

- **Por que não utilizar uma fila para os logs?**
    - Evitar over-engineering: Filas foram concentradas especificamente para o processamento dos dados do CSV, e logs diretos no banco atendem bem ao requisito.

---

### Erros de Validação
Uma tabela específica foi criada para armazenar erros de validação encontrados durante o processamento.

- **Justificativa**:
    - Manter um histórico claro dos registros que não foram processados corretamente.
    - Permitir que registros válidos sejam processados mesmo quando houver erros em outras linhas.
    - Facilidade para correções futuras: O administrador pode corrigir apenas as linhas com erro sem reprocessar todo o arquivo.

---

## Decisões Adicionais

### Testes
Os testes foram realizados diretamente nas controllers, chamando as rotas correspondentes.

- **Por quê?**
    - Solução prática que atende à necessidade de garantir que as rotas funcionam conforme esperado.
    - Foco em testes de integração como prioridade nesta etapa do desenvolvimento.

---

### Recursos Utilizados
- **HTTP Resources**:
  Utilizados para padronizar e formatar as respostas da API com clareza e consistência.
- **Enums como Inteiros**:  
  Decisões como representação de status utilizando Enums inteiros foram feitas para economizar espaço no banco de dados.
- **Tratamento de Exceptions**:  
  Modifiquei o retorno das exceptions para padronizar a API e garantir maior clareza ao consumidor dos endpoints.

---

## Monitoramento do Status do Processo de Upload

A implementação de monitoramento do progresso de uploads seguiu uma abordagem simples e eficaz:

- Um registro é criado no início do processo com o status `PENDING`.
- A cada chunk processado, este registro é atualizado com a contagem de linhas processadas.
- Ao final do processo, o status é alterado para `DONE`.

- **Por que esta abordagem?**
    - Permite acompanhamento em tempo real do progresso do upload.
    - Solução simples, mas perfeitamente funcional para monitorar grandes processos de importação.
