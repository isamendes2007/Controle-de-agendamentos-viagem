# Horizonte Viagens

Sistema web de gestão para uma agência de viagens, em **PHP 8+**, HTML, CSS e JavaScript, sem Composer, npm, frameworks ou CDNs.

O projeto tem **um único backend**: a mesma aplicação roda localmente com **SQLite** (arquivo, sem servidor de banco) e em hospedagem com **MySQL/MariaDB**, trocando apenas a configuração de conexão. Nada de código diferente entre os ambientes.

---

## Sumário

- [Arquitetura](#arquitetura)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
  - [Local com SQLite](#local-com-sqlite)
  - [Hospedagem com MySQL](#hospedagem-com-mysql)
  - [Configuração por variáveis de ambiente](#configuração-por-variáveis-de-ambiente)
- [Acesso de demonstração](#acesso-de-demonstração)
- [Estrutura do projeto](#estrutura-do-projeto)
- [Banco de dados](#banco-de-dados)
- [Regras de negócio](#regras-de-negócio)
- [Segurança](#segurança)
- [Solução de problemas](#solução-de-problemas)

---

## Arquitetura

```text
index.php               Front controller: resolve ?r=, aplica acesso/CSRF e despacha
router.php              Roteador do servidor embutido do PHP (php -S)
instalador.php          Instalador visual (SQLite ou MySQL)
config/app.php          Constantes (nome, versão, caminhos, debug, fuso)
config/database.php     Driver e credenciais (arquivo local + variáveis de ambiente)
app/bootstrap.php       Inicialização única: config, autoload, helpers, sessão
app/Controller.php      Controlador base (renderização, mensagens, navegação)
app/Database.php        Camada PDO com suporte a SQLite e MySQL
app/auth.php            Sessão e regras de acesso
app/helpers.php         Formatação, URLs, componentes, regras de apresentação
app/Controllers/        Fluxo HTTP de cada módulo
app/services/           Regras de negócio e SQL (um serviço por domínio)
app/views/              Templates (layout principal, fichas, listas, erros)
database/sqlite/        Estrutura e dados para SQLite (usados automaticamente)
database/schema.sql     Estrutura equivalente para MySQL/MariaDB
database/seed.sql       Dados de demonstração para MySQL/MariaDB
assets/css/style.css    Design system único
assets/js/app.js        Máscaras, confirmações, menu e interações
storage/                Banco SQLite local e logs
```

O fluxo de uma requisição é sempre o mesmo:

```text
index.php → app/bootstrap.php → Controller → Service → Database (SQLite ou MySQL) → View
```

Não há duas camadas de acesso a dados, dois roteadores ou dois conjuntos de telas.

---

## Requisitos

- **PHP 8.0 ou superior** com:
  - `pdo_sqlite` (uso local) **ou** `pdo_mysql` (hospedagem);
  - `mbstring`;
  - `openssl` (recomendado, usado pelo `password_hash`).
- Para hospedagem: **MySQL 8.0+** ou **MariaDB 10.4+**.
- Apache com `mod_rewrite` (opcional) ou o servidor embutido do PHP.

---

## Instalação

### Local com SQLite

```bash
php -S 127.0.0.1:8000 router.php
```

Abra `http://127.0.0.1:8000`. Na primeira execução o sistema cria `storage/banco.sqlite`, aplica `database/sqlite/schema.sql` e carrega `database/sqlite/seed.sql`. Nenhum servidor de banco é necessário.

Sem o `router.php`, use `php -S 127.0.0.1:8000` e acesse `http://127.0.0.1:8000/index.php?r=dashboard`.

### Hospedagem com MySQL

1. Envie os arquivos para o servidor (de preferência com a raiz apontando para a pasta do projeto).
2. Crie o banco no painel da hospedagem (ou pelo instalador).
3. Acesse `https://seudominio.com/instalador.php`, escolha **MySQL / MariaDB**, informe host, porta, banco, usuário e senha e clique em **Instalar sistema**.
4. O instalador grava `config/database.local.php` e importa `database/schema.sql` e `database/seed.sql`.
5. **Remova `instalador.php` do servidor** depois de concluir.

Importação manual (alternativa ao instalador):

```bash
mysql -u usuario -p --default-character-set=utf8mb4 -e "CREATE DATABASE agencia_viagem CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u usuario -p --default-character-set=utf8mb4 agencia_viagem < database/schema.sql
mysql -u usuario -p --default-character-set=utf8mb4 agencia_viagem < database/seed.sql
```

E crie `config/database.local.php`:

```php
<?php
return [
    'driver' => 'mysql',
    'host'   => '127.0.0.1',
    'port'   => '3306',
    'name'   => 'agencia_viagem',
    'user'   => 'usuario',
    'pass'   => 'senha',
];
```

O arquivo não é versionado (veja `.gitignore`). O modelo está em `config/database.local.example.php`.

### Configuração por variáveis de ambiente

As variáveis têm prioridade sobre o arquivo local, o que permite publicar sem credenciais no código:

| Variável | Padrão | Descrição |
| --- | --- | --- |
| `DB_DRIVER` | `sqlite` | `sqlite` ou `mysql` |
| `DB_SQLITE_PATH` | `storage/banco.sqlite` | Caminho do arquivo SQLite |
| `DB_HOST` | `127.0.0.1` | Host do MySQL |
| `DB_PORT` | `3306` | Porta do MySQL |
| `DB_NAME` | `agencia_viagem` | Nome do banco |
| `DB_USER` | `root` | Usuário |
| `DB_PASS` | *(vazio)* | Senha |
| `APP_DEBUG` | `false` | `true` mostra detalhes técnicos de erros |

---

## Acesso de demonstração

| Perfil | E-mail | Senha |
| --- | --- | --- |
| Administrador | `admin@horizonteviagens.com.br` | `admin123` |
| Agente | `rafael@horizonteviagens.com.br` | `agente123` |

O agente não acessa a gestão de **Usuários**, exclusiva do administrador. Em produção, altere as senhas em **Meu perfil** e remova os dados fictícios.

---

## Banco de dados

| Tabela | Finalidade |
| --- | --- |
| `usuarios` | Funcionários e perfis de acesso |
| `clientes` | Clientes/passageiros |
| `destinos` | Destinos turísticos |
| `pacotes` | Produtos vinculados a um destino |
| `viagens` | Partidas programadas de um pacote |
| `agendamentos` | Solicitações de viagem dos clientes |
| `reservas` | Vínculo cliente + viagem |
| `pagamentos` | Cobranças e baixas das reservas |

As duas estruturas (`database/sqlite/schema.sql` e `database/schema.sql`) têm as **mesmas tabelas, colunas e regras**. O SQL dos serviços usa apenas recursos comuns aos dois drivers (`CURRENT_DATE`, `COALESCE`, `SUBSTR`, `CASE`, `LIMIT/OFFSET`), por isso a aplicação não muda entre local e hospedagem.

---

## Regras de negócio

- O total da reserva é sempre recalculado no servidor: `valor unitário × viajantes − desconto`.
- A reserva não pode exceder as vagas disponíveis da viagem; o controle é feito em transação.
- Um agendamento só vira reserva quando está **aprovado** e o cliente ainda não tem reserva ativa para a mesma viagem.
- Pagamentos não podem ultrapassar o saldo devedor da reserva.
- Cobranças pendentes vencidas são marcadas como **atrasadas** automaticamente ao abrir uma sessão.
- Ao cancelar uma reserva, as cobranças pendentes/atrasadas são canceladas e as vagas são liberadas.
- Registros históricos não são apagados: clientes, destinos, pacotes e usuários são **desativados**.
- O último administrador ativo não pode ser desativado, e ninguém altera o status da própria conta.

---

## Segurança

- **PDO com prepared statements** em todas as consultas, nos dois drivers.
- Senhas com `password_hash`/`password_verify`.
- **Token CSRF** obrigatório em todo POST; falha retorna HTTP **419**.
- Sessão com cookie `HttpOnly`, `SameSite=Lax` e regeneração de ID no login.
- Controle de acesso por rota (pública, autenticada, administrador).
- Saída escapada com `htmlspecialchars` (função `e()`).
- `app/`, `config/`, `database/` e `storage/` bloqueados por `.htaccess` e pelo `router.php`.
- Erros técnicos vão para `storage/erros.log`; o usuário vê mensagem amigável.

> Em produção: use HTTPS, `APP_DEBUG=false`, senhas próprias, usuário de banco restrito e backups regulares.

---

## Solução de problemas

**“Vamos conectar o banco de dados”**
- MySQL fora do ar ou credenciais erradas. Abra o `instalador.php` e teste a conexão.
- Confirme `config/database.local.php` ou as variáveis `DB_*`.

**Arquivo SQLite sem permissão de escrita**
- Garanta escrita na pasta `storage/`. O sistema cria `storage/banco.sqlite` na primeira execução.

**Erro 419 ao salvar**
- A sessão expirou. Volte, recarregue o formulário e envie novamente.

**Acentuação incorreta**
- Importe os arquivos SQL com `--default-character-set=utf8mb4`.
- Confirme que banco e tabelas usam `utf8mb4`.

**Reinstalar do zero (local)**
- Apague `storage/banco.sqlite` (e os arquivos `-wal`/`-shm`); a estrutura e os dados de demonstração serão recriados na próxima abertura.

---

## Licença

Projeto fictício, desenvolvido para fins educacionais e de demonstração. Nomes, dados e valores são fictícios.
