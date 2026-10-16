<p align="center">
  <h1 align="center">🛡️ API Sentinel</h1>
  <p align="center">
    <strong>Monitoramento de APIs com foco em segurança, confiabilidade e transparência.</strong>
  </p>
  <p align="center">
    <a href="https://github.com/ArthurNunesDev/API-Sentinel/actions/workflows/ci.yml"><img src="https://github.com/ArthurNunesDev/API-Sentinel/actions/workflows/ci.yml/badge.svg?branch=main" alt="CI"></a>
    <img src="https://img.shields.io/badge/Hack-HHVM-4F5D95?style=for-the-badge" alt="Hack e HHVM">
    <img src="https://img.shields.io/badge/Docker-supported-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker">
    <img src="https://img.shields.io/badge/status-em%20desenvolvimento-orange?style=for-the-badge" alt="Em desenvolvimento">
  </p>
</p>

O **API Sentinel** é um projeto open source em desenvolvimento para verificar endpoints HTTP/HTTPS autorizados, medir latência e status HTTP e, nas próximas etapas, manter histórico de disponibilidade e acompanhar incidentes.

> [!IMPORTANT]
> **Estado atual:** o serviço ainda está em fase inicial. A API pública oferece apenas `GET /` e `GET /health`. Existem componentes isolados para validar URLs, validar dados de cadastro e executar uma verificação HTTP pontual, mas eles ainda não estão integrados a uma API de monitoramento. Cadastro persistente, agendamento, histórico, uptime, alertas, autenticação e painel ainda não estão implementados.

## Sumário

- [📌 Estado atual](#estado-atual)
- [✨ O que já existe](#funcionalidades)
- [🧱 Tecnologias](#tecnologias)
- [🚀 Como executar](#como-executar)
- [🔌 Endpoints HTTP](#endpoints)
- [🛡️ Segurança](#seguranca)
- [🧪 Testes e integração contínua](#testes)
- [🗂️ Estrutura do projeto](#estrutura)
- [🗺️ Roadmap](#roadmap)
- [🤝 Como contribuir](#contribuir)
- [📄 Licença](#licenca)

<h2 id="estado-atual">📌 Estado atual</h2>

O projeto está sendo construído por etapas, priorizando validações, testes automatizados e proteções de rede antes de disponibilizar o monitoramento de URLs fornecidas por usuários.

| Componente | Situação |
|---|---|
| Serviço HTTP e resposta de saúde | Implementado |
| Container Docker | Implementado |
| Pipeline de CI no GitHub Actions | Configurado |
| Validação inicial de URLs HTTP/HTTPS | Implementada |
| Resolução DNS A/AAAA e validação dos IPs retornados | Implementada como etapa isolada |
| Validação do payload de cadastro | Implementada como etapa isolada |
| Verificação HTTP pontual com cURL | Implementada como etapa isolada |
| Cadastro via API e persistência | Pendente |
| Autenticação e autorização | Pendente |
| Agendamento, histórico e uptime | Pendente |
| Alertas e dashboard | Pendente |

<h2 id="funcionalidades">✨ O que já existe</h2>

- **Endpoint de saúde:** permite verificar se o serviço está respondendo.
- **Validação de URL:** restringe os protocolos a HTTP/HTTPS e rejeita formatos e destinos considerados inseguros pelas regras atuais.
- **Validação de DNS:** consulta registros A e AAAA e valida todos os endereços encontrados, rejeitando listas vazias ou que contenham IPs privados/reservados.
- **Validação de cadastro:** valida nome, URL e intervalo de verificação sem gravar dados ou iniciar requisições.
- **Verificação HTTP isolada:** mede o tempo de resposta, aplica timeouts, desativa redirecionamentos e falha de forma fechada se opções críticas de segurança do cURL não puderem ser configuradas.
- **Testes automatizados:** testes Hack executados no pipeline de integração contínua.

Esses componentes são etapas de fundação do projeto, não um sistema completo de monitoramento. Ainda não há armazenamento de endpoints, verificações recorrentes nem alertas.

<h2 id="tecnologias">🧱 Tecnologias</h2>

<p>
  <img src="https://img.shields.io/badge/Hack-language-4F5D95?style=flat-square" alt="Hack">
  <img src="https://img.shields.io/badge/HHVM-runtime-4F5D95?style=flat-square" alt="HHVM">
  <img src="https://img.shields.io/badge/Proxygen-HTTP%20server-4F5D95?style=flat-square" alt="Proxygen">
  <img src="https://img.shields.io/badge/Docker-containers-2496ED?style=flat-square&logo=docker&logoColor=white" alt="Docker">
  <img src="https://img.shields.io/badge/GitHub%20Actions-CI-2088FF?style=flat-square&logo=githubactions&logoColor=white" alt="GitHub Actions">
  <img src="https://img.shields.io/badge/PostgreSQL-planned-4169E1?style=flat-square&logo=postgresql&logoColor=white" alt="PostgreSQL planejado">
</p>

| Tecnologia | Uso |
|---|---|
| Hack | Linguagem da aplicação |
| HHVM + Proxygen | Runtime e servidor HTTP |
| Docker | Empacotamento e execução do serviço |
| GitHub Actions | Testes automatizados, build da imagem e smoke test |
| PostgreSQL | Banco planejado para a etapa de persistência |

<h2 id="como-executar">🚀 Como executar</h2>

### Pré-requisitos

- [Git](https://git-scm.com/)
- [Docker](https://www.docker.com/) instalado e em execução

### Clonar o repositório

```bash
git clone https://github.com/ArthurNunesDev/API-Sentinel.git
cd API-Sentinel
```

### Construir e iniciar

```bash
docker build -t api-sentinel .
docker run --rm -p 8080:80 api-sentinel
```

Com o container em execução, acesse:

- Serviço: [http://localhost:8080/](http://localhost:8080/)
- Saúde: [http://localhost:8080/health](http://localhost:8080/health)

Para encerrar, pressione `Ctrl+C` no terminal onde o container está rodando.

<h2 id="endpoints">🔌 Endpoints HTTP disponíveis</h2>

### `GET /`

Retorna informações básicas sobre o serviço.

Exemplo de resposta:

```json
{
  "name": "API Sentinel",
  "status": "running",
  "health": "/health"
}
```

### `GET /health`

Retorna a confirmação de que a aplicação respondeu.

```json
{
  "status": "ok",
  "service": "API Sentinel"
}
```

O endpoint de saúde **não** verifica banco de dados, endpoints monitorados ou serviços externos.

### Comportamento das rotas

- Requisições com métodos diferentes de `GET` retornam `405 Method Not Allowed`.
- Caminhos não reconhecidos retornam `404 Not Found`.
- As respostas são enviadas em JSON e incluem `Cache-Control: no-store`.

<h2 id="seguranca">🛡️ Segurança</h2>

A segurança de rede é uma prioridade do projeto porque um monitor de URLs pode ser abusado para alcançar serviços internos ou endereços sensíveis.

As proteções atuais incluem validações de protocolo e URL, rejeição de credenciais embutidas, validação dos IPs resolvidos, tentativa de fixar a conexão ao IP validado, desativação de proxy e redirecionamentos e configuração fail-closed para opções críticas do cURL.

**Isso ainda não representa uma garantia de proteção SSRF completa.** Antes de expor a funcionalidade de verificação para usuários, é necessário testar e endurecer o comportamento de DNS e conexão no runtime de produção, incluindo cenários de DNS rebinding, e revalidar cada destino de rede de forma segura.

- Verifique apenas endpoints que você possui ou tem autorização explícita para monitorar.
- Nunca faça commit de senhas, tokens, chaves ou outras credenciais.
- Não exponha um endpoint público de verificação de URLs antes de concluir as proteções de SSRF e implementar autenticação e autorização.

<h2 id="testes">🧪 Testes e integração contínua</h2>

O workflow em `.github/workflows/ci.yml` executa os testes com HHVM, constrói a imagem Docker e faz um smoke test do endpoint `GET /health`.

Para executar os testes individualmente usando Docker:

```bash
docker run --rm -v "$PWD:/workspace" -w /workspace hhvm/hhvm:latest hhvm tests/HealthTest.php
docker run --rm -v "$PWD:/workspace" -w /workspace hhvm/hhvm:latest hhvm tests/UrlValidatorTest.php
docker run --rm -v "$PWD:/workspace" -w /workspace hhvm/hhvm:latest hhvm tests/EndpointTest.php
```

Execute os comandos a partir da raiz do repositório. A CI também valida se a imagem do serviço é construída e se o endpoint de saúde responde no container.

<h2 id="estrutura">🗂️ Estrutura do projeto</h2>

```text
API-Sentinel/
├── .github/
│   └── workflows/
│       └── ci.yml
├── public/
│   └── index.php
├── src/
│   ├── Endpoint.php
│   ├── Health.php
│   ├── HttpChecker.php
│   └── UrlValidator.php
├── tests/
│   ├── EndpointTest.php
│   ├── HealthTest.php
│   └── UrlValidatorTest.php
├── Dockerfile
└── README.md
```

- `public/index.php`: ponto de entrada HTTP e rotas atualmente públicas.
- `src/Health.php`: payload do endpoint de saúde.
- `src/UrlValidator.php`: validação sintática de URLs e validação de endereços IP resolvidos.
- `src/Endpoint.php`: validação isolada dos dados enviados para um futuro cadastro.
- `src/HttpChecker.php`: implementação isolada de uma verificação HTTP pontual.
- `tests/`: testes automatizados das funcionalidades implementadas.
- `.github/workflows/ci.yml`: pipeline de integração contínua.

<h2 id="roadmap">🗺️ Roadmap</h2>

- [x] Criar a estrutura inicial e o serviço HTTP
- [x] Implementar `GET /` e `GET /health`
- [x] Configurar Docker e GitHub Actions
- [x] Validar URLs HTTP/HTTPS e adicionar testes
- [x] Resolver DNS A/AAAA e validar os IPs retornados
- [x] Criar validação isolada do payload de cadastro de endpoints
- [x] Criar verificação HTTP pontual com timeouts e redirecionamentos desativados
- [x] Configurar falha segura quando opções críticas do cURL não puderem ser aplicadas
- [ ] Ampliar os testes de segurança de SSRF no runtime alvo, incluindo DNS rebinding
- [ ] Definir o schema PostgreSQL e implementar persistência
- [ ] Criar API de cadastro, autenticação e autorização
- [ ] Integrar cadastro, execução e agendamento de verificações
- [ ] Armazenar histórico de resultados e calcular uptime
- [ ] Detectar incidentes e enviar alertas
- [ ] Desenvolver dashboard e documentação de deploy

<h2 id="contribuir">🤝 Como contribuir</h2>

Contribuições são bem-vindas! Para sugerir melhorias ou reportar problemas:

1. Abra uma [issue](https://github.com/ArthurNunesDev/API-Sentinel/issues) descrevendo o problema ou a proposta.
2. Para mudanças de código, crie uma branch e abra um pull request.
3. Inclua ou atualize testes quando alterar comportamentos existentes.
4. Mantenha a documentação alinhada ao que realmente está implementado.

<h2 id="licenca">📄 Licença</h2>

O repositório ainda não declara uma licença. Até que uma licença seja adicionada, não presuma que o código pode ser reutilizado, redistribuído ou incorporado livremente a outros projetos.

---

<p align="center">
  Desenvolvido por <a href="https://github.com/ArthurNunesDev">ArthurNunesDev</a> · <a href="https://github.com/ArthurNunesDev/API-Sentinel">Repositório API Sentinel</a>
</p>
