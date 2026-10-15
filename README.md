# API Sentinel

Plataforma open source de monitoramento de APIs, desenvolvida com **Hack (HHVM)**.

O objetivo é monitorar endpoints autorizados, medir latência e status HTTP, guardar histórico de verificações e acompanhar incidentes. O desenvolvimento está sendo feito em etapas pequenas, com testes automatizados.

> **Status:** fase inicial. O endpoint `GET /health` está validado pela CI. Foi adicionada validação sintática de URLs, resolução de registros DNS A/AAAA e validação dos endereços retornados; o monitoramento real, a persistência, os alertas e o painel ainda não foram implementados.

## Tecnologias

- **Linguagem:** Hack
- **Runtime/web server:** HHVM + Proxygen
- **Container:** Docker
- **CI:** GitHub Actions
- **Persistência planejada:** PostgreSQL

## Endpoints disponíveis

### `GET /health`

Retorna o estado básico do serviço:

```json
{
  "status": "ok",
  "service": "API Sentinel"
}
```

O endpoint confirma apenas que a aplicação respondeu; não verifica banco de dados nem serviços externos.

- `GET /` retorna informações básicas do serviço.
- Métodos diferentes de `GET` retornam `405 Method Not Allowed`.
- Rotas desconhecidas retornam `404 Not Found`.

## Validação inicial de URLs

A função `api_sentinel_validate_target_url()` em `src/UrlValidator.php` verifica o formato da URL, aceita apenas HTTP/HTTPS, rejeita credenciais embutidas, localhost e endereços IP privados/reservados detectáveis diretamente. A função `api_sentinel_resolve_and_validate_host()` consulta registros DNS A e AAAA e valida todos os endereços encontrados. `api_sentinel_validate_resolved_ips()` rejeita a lista inteira se estiver vazia ou contiver algum endereço inválido, privado ou reservado.

**Limitação importante:** `api_sentinel_check_url()` em `src/HttpChecker.php` faz uma verificação HTTP pontual, limita o tempo da conexão e da requisição, fixa hosts DNS ao IP validado com `CURLOPT_RESOLVE` e não segue redirecionamentos. Redirecionamentos são reportados, não seguidos. Ainda é necessário validar o comportamento do cliente HTTP no runtime alvo e ampliar os testes de segurança antes de expor isso publicamente; a função não é chamada pela API pública, não há cadastro nem agendamento de monitoramento, histórico ou proteção SSRF completa garantida. Não exponha um monitor de URLs na internet até implementar e testar essas proteções adicionais.

## Executar com Docker

É necessário ter Docker instalado e funcionando.

```bash
docker build -t api-sentinel .
docker run --rm -p 8080:80 api-sentinel
```

Em outro terminal:

```bash
curl -i http://localhost:8080/health
```

## Testes

O workflow de GitHub Actions executa os testes Hack e verifica se a imagem Docker constrói e se `GET /health` responde:

```bash
docker run --rm -v "$PWD:/workspace" -w /workspace hhvm/hhvm:latest hhvm tests/HealthTest.php
docker run --rm -v "$PWD:/workspace" -w /workspace hhvm/hhvm:latest hhvm tests/UrlValidatorTest.php
```

## Roadmap

- [x] Criar repositório e estrutura inicial
- [x] Implementar endpoint de saúde
- [x] Adicionar Dockerfile e CI
- [x] Criar validação sintática inicial de URLs e testes
- [x] Resolver DNS A/AAAA e validar todos os IPs retornados (etapa isolada)
- [x] Adicionar verificação HTTP pontual com conexão fixada ao IP validado e redirecionamentos desativados (etapa isolada)
- [ ] Testar e endurecer as proteções SSRF no runtime de produção
- [ ] Criar cadastro de endpoints monitorados
- [x] Implementar função isolada de verificação HTTP e medição de latência
- [ ] Integrar verificações HTTP ao cadastro e agendamento de endpoints
- [ ] Persistir endpoints e histórico no PostgreSQL
- [ ] Calcular uptime e acompanhar incidentes
- [ ] Implementar alertas e documentação de deploy

## Segurança

- Monitore somente endpoints que você possui ou tem autorização para testar.
- Não envie tokens, senhas ou segredos para o Git.
- Não use a validação inicial como barreira de segurança para requisições de rede até que as proteções SSRF estejam completas.

## Contribuição

Issues e pull requests são bem-vindos. Inclua testes para mudanças de comportamento.

## Licença

Uma licença ainda não foi escolhida. Até que uma licença seja adicionada, não considere o repositório como uma autorização ampla para reutilizar ou redistribuir o código.
