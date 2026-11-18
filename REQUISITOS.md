# Horizonte Viagens — Requisitos do Sistema

Documento de referência do que o sistema **faz hoje**, verificado no código e nos testes de fumaça. Serve como base para avaliação acadêmica, manutenção e evolução.

- **Versão:** 1.0.0
- **Stack:** PHP 8.0+ (sem framework), HTML5, CSS3, JavaScript mínimo
- **Banco:** SQLite (arquivo local) **ou** MySQL/MariaDB (hospedagem)
- **Idioma:** interface, mensagens e documentos em português do Brasil

---

## 1. Visão geral

O Horizonte Viagens é um sistema de gestão para uma agência de viagens. Ele cobre o ciclo comercial completo:

```
cliente → destino → pacote → viagem (partida) → agendamento → reserva → pagamento → relatórios
```

Dois perfis usam o sistema:

| Perfil | Alcance |
|---|---|
| **Administrador** | Tudo que o agente faz, mais a gestão de usuários do sistema. |
| **Agente** | Operação comercial: clientes, destinos, pacotes, viagens, agendamentos, reservas, pagamentos, relatórios e o próprio perfil. |

---

## 2. Requisitos funcionais

Convenção: **RF-xx** = requisito funcional; **RN-xx** = regra de negócio; **RNF-xx** = requisito não funcional.

### 2.1 Autenticação e controle de acesso

| Código | Requisito | Situação |
|---|---|---|
| RF-01 | Permitir login com e-mail e senha cadastrados. | Implementado |
| RF-02 | Bloquear o acesso às telas internas quando não houver sessão válida, redirecionando ao login. | Implementado |
| RF-03 | Encerrar a sessão por ação explícita do usuário (logout via POST). | Implementado |
| RF-04 | Exibir mensagem clara para credenciais inválidas, sem revelar se o e-mail existe. | Implementado |
| RF-05 | Restringir a gestão de usuários ao perfil administrador. | Implementado |
| RF-06 | Impedir que o usuário desative a própria conta. | Implementado |
| RF-07 | Impedir a desativação do último administrador ativo. | Implementado |
| RF-08 | Registrar a data do último acesso do usuário. | Implementado |

### 2.2 Clientes

| Código | Requisito | Situação |
|---|---|---|
| RF-09 | Cadastrar cliente com nome, CPF, e-mail, telefone, nascimento e endereço completo. | Implementado |
| RF-10 | Validar CPF (dígitos verificadores) e impedir duplicidade. | Implementado |
| RF-11 | Validar formato de e-mail e impedir e-mail já usado por cliente ou usuário do sistema. | Implementado |
| RF-12 | Listar clientes com busca por nome, e-mail, CPF ou telefone e filtro por situação. | Implementado |
| RF-13 | Editar dados cadastrais do cliente. | Implementado |
| RF-14 | Ativar/desativar cliente sem apagar o histórico. | Implementado |
| RF-15 | Exibir ficha do cliente com reservas, valores e situação financeira. | Implementado |
| RF-16 | Consultar CEP e preencher endereço automaticamente no formulário. | Implementado (via JS/API ViaCEP) |

### 2.3 Destinos

| Código | Requisito | Situação |
|---|---|---|
| RF-17 | Cadastrar destino com nome, cidade, estado, país e continente. | Implementado |
| RF-18 | Impedir destino duplicado (mesmo nome, cidade e país). | Implementado |
| RF-19 | Listar destinos com busca e filtro por situação. | Implementado |
| RF-20 | Exibir, por destino, a quantidade de pacotes ativos e de viagens futuras. | Implementado |
| RF-21 | Ativar/desativar destino preservando pacotes e viagens existentes. | Implementado |

### 2.4 Pacotes

| Código | Requisito | Situação |
|---|---|---|
| RF-22 | Cadastrar pacote vinculado a um destino, com categoria, descrição e preço base. | Implementado |
| RF-23 | Classificar o pacote em uma categoria (praia, aventura, cultural, gastronomia, ecoturismo, luxo, religioso, negócios). | Implementado |
| RF-24 | Listar pacotes em formato de catálogo, com busca por nome/descrição, filtro por destino e situação. | Implementado |
| RF-25 | Exibir preço "a partir de", próxima partida e quantidade de partidas futuras. | Implementado |
| RF-26 | Impedir pacote duplicado (mesmo nome no mesmo destino). | Implementado |
| RF-27 | Ativar/desativar pacote sem afetar viagens já programadas. | Implementado |

### 2.5 Viagens (partidas)

| Código | Requisito | Situação |
|---|---|---|
| RF-28 | Programar viagem a partir de um pacote, com data de partida, retorno, vagas e preço. | Implementado |
| RF-29 | Impedir duas viagens do mesmo pacote na mesma data de partida. | Implementado |
| RF-30 | Controlar o ciclo de status da viagem: planejada → confirmada → em andamento → concluída, ou cancelada. | Implementado |
| RF-31 | Listar viagens com busca, filtro por status, destino e período de partida. | Implementado |
| RF-32 | Exibir resumo de viagens futuras, em andamento e concluídas. | Implementado |
| RF-33 | Mostrar ocupação da viagem (viajantes confirmados ÷ vagas totais) e vagas restantes. | Implementado |
| RF-34 | Exibir receita prevista, valor recebido e saldo em aberto da viagem. | Implementado |
| RF-35 | Impedir exclusão de viagem que possua reservas vinculadas. | Implementado |
| RF-36 | Ao cancelar a viagem, cancelar as reservas vinculadas e anular as cobranças pendentes. | Implementado |

### 2.6 Agendamentos (solicitações do cliente)

| Código | Requisito | Situação |
|---|---|---|
| RF-37 | Registrar solicitação de agendamento vinculando cliente e viagem, com quantidade de viajantes e observações. | Implementado |
| RF-38 | Gerar código único para o agendamento (padrão `AG-AAAA-XXXXXX`). | Implementado |
| RF-39 | Calcular o valor total do agendamento (valor unitário × viajantes). | Implementado |
| RF-40 | Validar disponibilidade de vagas no momento da solicitação. | Implementado |
| RF-41 | Acompanhar o status: solicitado → aprovado → convertido em reserva, ou rejeitado/cancelado. | Implementado |
| RF-42 | Exibir no menu a quantidade de agendamentos aguardando decisão do gestor. | Implementado |
| RF-43 | Converter agendamento aprovado em reserva confirmada, com um clique e sem redigitação. | Implementado |
| RF-44 | Impedir conversão duplicada (mesmo cliente e viagem com reserva ativa). | Implementado |
| RF-45 | Editar e excluir agendamentos conforme o status. | Implementado |

### 2.7 Reservas

| Código | Requisito | Situação |
|---|---|---|
| RF-46 | Criar reserva vinculando cliente e viagem, com quantidade de viajantes, valor unitário e desconto. | Implementado |
| RF-47 | Gerar código único de reserva (padrão `HV-AAAA-XXXXXX`). | Implementado |
| RF-48 | Calcular automaticamente o valor total (unitário × viajantes − desconto). | Implementado |
| RF-49 | Validar se há vagas suficientes antes de confirmar a reserva. | Implementado |
| RF-50 | Controlar o status da reserva: pendente, confirmada, concluída, cancelada. | Implementado |
| RF-51 | Listar reservas com busca e filtros por status e período de criação. | Implementado |
| RF-52 | Exibir resumo de reservas (total, pendentes, confirmadas, canceladas). | Implementado |
| RF-53 | Mostrar na reserva o resumo financeiro: valor total, pago, em aberto e situação. | Implementado |
| RF-54 | Listar os pagamentos vinculados à reserva, com responsável pelo registro. | Implementado |
| RF-55 | Atualizar o status da reserva automaticamente conforme os pagamentos (quitada → confirmada/concluída). | Implementado |

### 2.8 Pagamentos

| Código | Requisito | Situação |
|---|---|---|
| RF-56 | Registrar cobrança vinculada a uma reserva, com valor, forma de pagamento, vencimento e referência. | Implementado |
| RF-57 | Suportar as formas: PIX, cartão de crédito, cartão de débito, boleto, transferência e dinheiro. | Implementado |
| RF-58 | Controlar o status: pendente, pago, atrasado, estornado, cancelado. | Implementado |
| RF-59 | Dar baixa em pagamento pendente, registrando data e usuário responsável. | Implementado |
| RF-60 | Estornar pagamento já quitado, com justificativa. | Implementado |
| RF-61 | Cancelar cobrança pendente ou atrasada. | Implementado |
| RF-62 | Marcar automaticamente como "atrasado" todo pagamento pendente com vencimento no passado. | Implementado |
| RF-63 | Exibir indicadores de recebido no mês, total recebido, a receber e em atraso. | Implementado |
| RF-64 | Listar pagamentos com busca e filtros por status e período de vencimento. | Implementado |
| RF-65 | Impedir exclusão de pagamento já quitado (usar estorno). | Implementado |
| RF-66 | Após registrar um pagamento, retornar à reserva de origem. | Implementado |

### 2.9 Relatórios

| Código | Requisito | Situação |
|---|---|---|
| RF-67 | Emitir relatório gerencial por período, com filtro opcional de destino. | Implementado |
| RF-68 | Apresentar receita recebida, receita do período anterior, ticket médio e ocupação. | Implementado |
| RF-69 | Comparar receita por destino e evolução mensal da receita. | Implementado |
| RF-70 | Exportar o relatório em CSV compatível com Excel (separador `;`, UTF-8 com BOM). | Implementado |
| RF-71 | Oferecer versão para impressão com layout limpo. | Implementado |

### 2.10 Painel (dashboard)

| Código | Requisito | Situação |
|---|---|---|
| RF-72 | Exibir receita do mês com variação em relação ao mês anterior. | Implementado |
| RF-73 | Exibir reservas do mês, reservas aguardando confirmação e clientes ativos. | Implementado |
| RF-74 | Exibir viagens em operação, valores a receber e valores em atraso. | Implementado |
| RF-75 | Gráfico de receita dos últimos seis meses e distribuição de reservas por status. | Implementado |
| RF-76 | Listar próximas partidas e pendências que exigem ação. | Implementado |

### 2.11 Usuários e perfil

| Código | Requisito | Situação |
|---|---|---|
| RF-77 | Administrador cadastra usuários com nome, e-mail, telefone, perfil e senha. | Implementado |
| RF-78 | Senhas armazenadas com hash seguro (`password_hash`). | Implementado |
| RF-79 | Impedir e-mail de usuário duplicado. | Implementado |
| RF-80 | Ativar/desativar usuários (respeitando RF-06 e RF-07). | Implementado |
| RF-81 | Qualquer usuário autenticado atualiza os próprios dados de contato. | Implementado |
| RF-82 | Qualquer usuário autenticado troca a própria senha informando a senha atual. | Implementado |

### 2.12 Portabilidade e instalação

| Código | Requisito | Situação |
|---|---|---|
| RF-83 | Funcionar localmente sem servidor de banco de dados, usando arquivo SQLite. | Implementado |
| RF-84 | Criar a estrutura e os dados de demonstração do SQLite automaticamente na primeira execução. | Implementado |
| RF-85 | Funcionar em hospedagem com MySQL/MariaDB, sem alteração de código. | Implementado |
| RF-86 | Selecionar o banco por variável de ambiente (`DB_DRIVER`, `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_SQLITE_PATH`). | Implementado |
| RF-87 | Permitir configuração local por `config/database.local.php`, que não é versionado. | Implementado |
| RF-88 | Exibir mensagem amigável quando o banco estiver indisponível, em vez de erro bruto do PHP. | Implementado |
| RF-89 | Manter os scripts de estrutura e carga (`database/schema.sql`, `database/seed.sql` e equivalentes SQLite) para instalação manual em hospedagem. | Implementado |
| RF-90 | Restringir o acesso web direto às pastas `app`, `config`, `database` e `storage`. | Implementado |

---

## 3. Regras de negócio

| Código | Regra |
|---|---|
| RN-01 | Toda operação interna exige usuário autenticado. |
| RN-02 | CPF é validado por dígitos verificadores e é único por cliente. |
| RN-03 | E-mail é único entre clientes e também não pode repetir o de um usuário do sistema. |
| RN-04 | Registros históricos não são apagados por padrão: clientes, destinos, pacotes e usuários são **desativados**. |
| RN-05 | Só é possível excluir viagem sem reservas vinculadas. |
| RN-06 | Cancelar viagem cancela reservas e anula cobranças pendentes. |
| RN-07 | Reserva e agendamento não podem exceder as vagas disponíveis da viagem. |
| RN-08 | O valor total da reserva é sempre recalculado no servidor, nunca aceito do formulário. |
| RN-09 | Pagamento quitado não pode ser excluído; o caminho correto é o estorno. |
| RN-10 | Pagamento pendente vencido é marcado como atrasado automaticamente ao abrir uma sessão. |
| RN-11 | Agendamento só se converte em reserva quando está aprovado. |
| RN-12 | Não se converte agendamento se já existir reserva ativa do mesmo cliente para a mesma viagem. |
| RN-13 | O último administrador ativo não pode ser desativado. |
| RN-14 | Nenhum usuário altera o status da própria conta. |

---

## 4. Requisitos não funcionais

| Código | Requisito |
|---|---|
| RNF-01 | **Portabilidade de banco:** mesma aplicação roda em SQLite e MySQL, com SQL compatível com ambos. |
| RNF-02 | **Segurança:** PDO com prepared statements em todas as consultas; saída escapada com `htmlspecialchars`; token CSRF em todos os formulários POST. |
| RNF-03 | **Senhas:** hash `password_hash`/`password_verify`; nenhuma senha em texto puro no banco ou em log. |
| RNF-04 | **Sessão:** cookie `HttpOnly`, `SameSite=Lax`, regeneração de ID no login e expiração por inatividade. |
| RNF-05 | **Arquitetura:** controllers apenas tratam HTTP; regras de negócio nos services; acesso a dados exclusivamente pela classe `Database`. |
| RNF-06 | **Interface:** design system único (paleta institucional, tipografia serifada nos títulos, componentes reaproveitáveis), responsivo de 360 px a desktop. |
| RNF-07 | **Acessibilidade:** navegação por teclado, foco visível, rótulos em formulários, `aria-label` em ações com ícone. |
| RNF-08 | **Desempenho:** listagens paginadas, índices nas colunas de busca e junção, consultas sem `N+1`. |
| RNF-09 | **Observabilidade:** erros técnicos vão para `storage/erros.log`; o usuário vê mensagem amigável. |
| RNF-10 | **Idioma e conteúdo:** 100% em português do Brasil, com textos claros e sem jargão técnico. |
| RNF-11 | **Implantação:** funciona no servidor embutido do PHP (`php -S`) e em Apache com `mod_rewrite`; não exige Composer nem dependências externas. |
| RNF-12 | **Dados de demonstração:** carga inicial fictícia, suficiente para demonstrar todos os fluxos. |

---

## 5. Matriz de rastreabilidade (requisito → implementação)

| Domínio | Controller | Service | Views |
|---|---|---|---|
| Autenticação | `AuthController` | `AuthService` | `auth/login` |
| Clientes | `ClientesController` | `ClienteService` | `clientes/*` |
| Destinos | `DestinosController` | `DestinoService` | `destinos/*` |
| Pacotes | `PacotesController` | `PacoteService` | `pacotes/*` |
| Viagens | `ViagensController` | `ViagemService` | `viagens/*` |
| Agendamentos | `AgendamentosController` | `AgendamentoService` | `agendamentos/*` |
| Reservas | `ReservasController` | `ReservaService` | `reservas/*` |
| Pagamentos | `PagamentosController` | `PagamentoService` | `pagamentos/*` |
| Relatórios | `RelatoriosController` | `RelatorioService` | `relatorios/*` |
| Painel | `DashboardController` | `DashboardService` | `dashboard/index` |
| Usuários | `UsuariosController` | `UsuarioService` | `usuarios/*` |
| Perfil | `PerfilController` | `UsuarioService` | `perfil/index` |
| Banco | `Database` | — | — |

---

## 6. Critérios de aceitação (verificados)

1. Com o banco SQLite inexistente, a primeira requisição cria `storage/banco.sqlite`, aplica a estrutura e carrega os dados de demonstração.
2. Login com `admin@horizonteviagens.com.br` / `admin123` e `rafael@horizonteviagens.com.br` / `agente123` funciona.
3. Todas as telas de leitura respondem **200** sem avisos de PHP no log: dashboard, clientes, destinos, pacotes, viagens, agendamentos, reservas, pagamentos, relatórios, perfil e usuários.
4. Aprovar um agendamento e convertê-lo em reserva gera uma reserva confirmada e redireciona para a reserva criada.
5. Exportação CSV e versão de impressão do relatório são geradas corretamente.
6. As mesmas rotas funcionam com `DB_DRIVER=mysql` apontando para um MySQL 8 com `database/schema.sql` e `database/seed.sql` aplicados.

---

## 7. Fora de escopo

Itens deliberadamente ausentes nesta versão:

- Área do cliente (autoatendimento) — o cliente é cadastrado pela agência.
- Emissão fiscal, integração com gateways de pagamento e conciliação bancária.
- Envio de e-mails transacionais.
- Upload de imagens para destinos e pacotes.
- Multiusuário concorrente com bloqueio otimista.
- Aplicativo móvel nativo.
