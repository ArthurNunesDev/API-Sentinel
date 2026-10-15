# API Sentinel

Plataforma open source de monitoramento de APIs, desenvolvida com **Hack (HHVM)**.

O objetivo é acompanhar a disponibilidade de endpoints HTTP/HTTPS, medir latência, manter histórico de verificações e registrar incidentes.

> **Status:** início do desenvolvimento. O monitoramento agendado, a persistência, os alertas e a API HTTP ainda não estão implementados.

## Objetivos

- Cadastrar endpoints que você tem autorização para monitorar.
- Verificar disponibilidade e medir latência e códigos HTTP.
- Armazenar histórico e calcular disponibilidade por período.
- Registrar incidentes e recuperações.
- Expor uma API HTTP/JSON documentada.
- Automatizar testes e validações com CI.

## Tecnologias planejadas

- **Linguagem:** Hack
- **Runtime:** HHVM
- **Banco de dados:** PostgreSQL
- **Formato da API:** HTTP/JSON
- **Qualidade:** testes automatizados e GitHub Actions

## Estrutura inicial

```text
API-Sentinel/
├── src/
│   └── Health.php
├── .hhconfig
├── .gitignore
└── README.md
```

## Desenvolvimento

Antes de executar o projeto, configure uma versão do HHVM compatível com Hack em um ambiente Linux suportado. Os comandos de execução e a configuração de testes serão documentados depois que o ambiente for validado.

### Roadmap

- [x] Criar repositório e estrutura inicial
- [x] Adicionar um payload básico de saúde do serviço
- [ ] Validar ambiente de desenvolvimento HHVM/Hack
- [ ] Expor `GET /health` por meio de um servidor HTTP
- [ ] Cadastrar e validar endpoints monitorados
- [ ] Executar verificações HTTP e medir latência
- [ ] Persistir histórico no PostgreSQL
- [ ] Calcular uptime e acompanhar incidentes
- [ ] Adicionar testes automatizados e CI
- [ ] Documentar configuração e deploy

## Segurança

- Monitore somente endpoints que você possui ou tem autorização para testar.
- O cadastro de URLs deverá incluir proteções contra SSRF, bloqueando destinos locais, privados, link-local e endereços de metadados de provedores de nuvem.
- Nunca envie tokens, senhas ou segredos para o Git.

## Contribuição

Issues e pull requests são bem-vindos. Mudanças de comportamento devem incluir testes quando a infraestrutura de testes estiver configurada.

## Licença

Uma licença ainda não foi escolhida. Até que uma licença seja adicionada, não considere o repositório como uma autorização ampla para reutilizar ou redistribuir o código.
