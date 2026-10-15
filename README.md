# API Sentinel

Plataforma open source de monitoramento de APIs, desenvolvida com **Hack (HHVM)**.

O projeto está começando pela infraestrutura HTTP e pelo endpoint de saúde. A visão de longo prazo é monitorar endpoints autorizados, medir latência, guardar histórico de verificações e acompanhar incidentes.

> **Status:** scaffold inicial. O endpoint `GET /health`, um teste de payload e um build Docker foram adicionados. A execução real e a CI ainda precisam ser confirmadas pelo primeiro workflow; monitoramento agendado, persistência, alertas e painel ainda não foram implementados.

## Tecnologias

- **Linguagem:** Hack
- **Runtime/web server:** HHVM + Proxygen
- **Container:** Docker
- **CI:** GitHub Actions
- **Próxima etapa de persistência:** PostgreSQL

## Endpoint disponível no código

### `GET /health`

Retorna JSON com o estado básico do serviço:

```json
{
  "status": "ok",
  "service": "API Sentinel"
}
```

O endpoint confirma apenas que a aplicação respondeu; ainda não verifica banco de dados nem serviços externos.

- `GET /` retorna informações básicas do serviço.
- Métodos diferentes de `GET` retornam `405 Method Not Allowed`.
- Rotas desconhecidas retornam `404 Not Found`.

## Executar com Docker

É necessário ter Docker instalado e funcionando.

```bash
docker build -t api-sentinel .
docker run --rm -p 8080:80 api-sentinel
```

Em outro terminal, consulte o endpoint:

```bash
curl -i http://localhost:8080/health
```

A imagem usa `hhvm/hhvm-proxygen` e publica o diretório `public/` pelo servidor HHVM. A execução deve ser validada no seu ambiente e no workflow do GitHub Actions antes de considerar o setup estável.

## Testes

O teste inicial valida o payload de saúde diretamente no HHVM:

```bash
docker run --rm -v "$PWD:/workspace" -w /workspace hhvm/hhvm:latest hhvm tests/HealthTest.php
```

O workflow em `.github/workflows/ci.yml` executa esse teste e constrói a imagem a cada push ou pull request para `main`.

## Roadmap

- [x] Criar repositório e estrutura inicial
- [x] Implementar payload de saúde em Hack
- [x] Criar handler HTTP para `GET /health`
- [x] Adicionar teste inicial do payload
- [x] Adicionar Dockerfile e workflow de CI
- [ ] Confirmar execução do endpoint e resultado do workflow
- [ ] Cadastrar e validar endpoints monitorados
- [ ] Executar verificações HTTP e medir latência
- [ ] Persistir histórico no PostgreSQL
- [ ] Calcular uptime e acompanhar incidentes
- [ ] Implementar alertas e documentação de deploy

## Segurança

- Monitore somente endpoints que você possui ou tem autorização para testar.
- Antes de implementar chamadas a URLs cadastradas, adicionar proteções contra SSRF: bloquear destinos locais, privados, link-local e endereços de metadados de provedores de nuvem, incluindo redirecionamentos para esses destinos.
- Nunca envie tokens, senhas ou segredos para o Git.

## Contribuição

Issues e pull requests são bem-vindos. Inclua testes para mudanças de comportamento.

## Licença

Uma licença ainda não foi escolhida. Até que uma licença seja adicionada, não considere o repositório como uma autorização ampla para reutilizar ou redistribuir o código.
