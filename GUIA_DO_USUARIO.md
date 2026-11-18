# Guia do usuário — Horizonte Viagens

Este guia apresenta as rotinas do dia a dia no sistema de gestão da **Horizonte Viagens**. Ele foi escrito para administradores e agentes de viagens, sem exigir conhecimento técnico.

> Todos os dados exibidos nos exemplos são fictícios.

---

## Sumário

- [1. Visão geral](#1-visão-geral)
- [2. Acesso ao sistema](#2-acesso-ao-sistema)
- [3. Painel gerencial](#3-painel-gerencial)
- [4. Cadastro de clientes](#4-cadastro-de-clientes)
- [5. Cadastro de destinos](#5-cadastro-de-destinos)
- [6. Cadastro de pacotes](#6-cadastro-de-pacotes)
- [7. Programação de viagens](#7-programação-de-viagens)
- [8. Agendamentos de viagens](#8-agendamentos-de-viagens)
- [9. Criação de reservas](#9-criação-de-reservas)
- [10. Controle de pagamentos](#10-controle-de-pagamentos)
- [11. Relatórios](#11-relatórios)
- [12. Usuários e permissões](#12-usuários-e-permissões)
- [13. Meu perfil](#13-meu-perfil)
- [14. Rotinas recomendadas](#14-rotinas-recomendadas)
- [15. Perguntas frequentes](#15-perguntas-frequentes)
- [16. Glossário](#16-glossário)

---

## 1. Visão geral

O sistema acompanha o fluxo comercial da agência:

```text
Destino → Pacote → Viagem → Agendamento → Reserva → Pagamentos
```

1. Cadastre o **destino**.
2. Crie um **pacote** vinculado a esse destino.
3. Programe uma **viagem** (partida) a partir do pacote.
4. Registre o **agendamento** com a solicitação do cliente (opcional, mas recomendado).
5. Converta o agendamento aprovado em **reserva**, ou registre a reserva diretamente.
6. Lance os **pagamentos** da reserva.
7. Acompanhe os resultados nos **relatórios**.

### Perfis de acesso

| Perfil | O que pode fazer |
| --- | --- |
| **Administrador** | Todas as rotinas operacionais, relatórios e manutenção de usuários. |
| **Agente** | Rotinas de clientes, destinos, pacotes, viagens, agendamentos, reservas, pagamentos, relatórios e o próprio perfil. |

O menu **Usuários** aparece apenas para administradores.

---

## 2. Acesso ao sistema

1. Abra o endereço do sistema no navegador.
2. Informe **e-mail** e **senha**.
3. Clique em **Entrar no sistema**.

Na tela de login há dois acessos de demonstração com o botão **Usar**, que preenche as credenciais automaticamente:

| Perfil | E-mail | Senha |
| --- | --- | --- |
| Administrador | `admin@horizonteviagens.com.br` | `admin123` |
| Agente | `rafael@horizonteviagens.com.br` | `agente123` |

### Dicas de segurança

- Não compartilhe sua senha.
- Após entrar, altere a senha de demonstração em **Meu perfil**.
- Use **Sair do sistema** ao terminar em computadores compartilhados.

---

## 3. Painel gerencial

O **Painel** é a primeira tela após o login e resume a situação da agência.

### Indicadores

- **Receita recebida no mês:** soma dos pagamentos com status **Pago** recebidos no mês atual, com variação em relação ao mês anterior.
- **Reservas criadas no mês:** quantidade de reservas registradas e quantas aguardam confirmação.
- **Clientes ativos:** total de clientes com cadastro ativo e viagens em operação.
- **Valores a receber:** saldo pendente e parte já vencida.

### Blocos de acompanhamento

- **Receita dos últimos 6 meses:** gráfico de barras com os valores efetivamente recebidos.
- **Últimas reservas:** movimentações recentes com acesso direto à reserva.
- **Reservas por status:** distribuição entre pendentes, confirmadas, concluídas e canceladas.
- **Ocupação das viagens ativas:** viajantes confirmados em relação às vagas ofertadas.
- **Próximas viagens:** partidas planejadas ou confirmadas, com data e ocupação.
- **Precisa de atenção:** cobranças vencidas e ainda em aberto.
- **Acesso rápido:** atalhos para novo cliente, nova viagem, novo pacote e novo pagamento.

### Ações rápidas

No topo do painel:

- **Ver relatórios:** abre o relatório gerencial.
- **Nova reserva:** inicia uma reserva.
- **Sino de alertas:** leva às cobranças em atraso.
- **Meu perfil:** abre os dados da conta.

---

## 4. Cadastro de clientes

Menu: **Clientes**

### Listar e localizar

- A listagem mostra nome, contato, cidade e status.
- Use o campo de busca para localizar por **nome, CPF, e-mail, telefone ou cidade**.
- Use o filtro de status para ver **ativos**, **inativos** ou **todos**.
- Clique no nome do cliente para abrir a ficha completa.

### Cadastrar um cliente

1. Clique em **Novo cliente**.
2. Preencha os dados pessoais:
   - Nome completo;
   - CPF;
   - Data de nascimento (opcional).
3. Preencha o contato:
   - E-mail;
   - Telefone / WhatsApp.
4. Informe o endereço (opcional):
   - CEP, logradouro, número, complemento, bairro, cidade e estado.
5. Use **Anotações internas** para preferências, restrições ou observações.
6. Clique em **Cadastrar cliente**.

O sistema valida CPF, e-mail e telefone e impede o cadastro de um CPF já existente.

### Editar um cliente

1. Abra a ficha do cliente.
2. Clique em **Editar**.
3. Altere os dados e clique em **Salvar alterações**.

### Ativar ou desativar

Na ficha ou na listagem, use a ação de **ativar/desativar**. A desativação preserva todo o histórico de reservas e pagamentos, mas impede novos vínculos.

> O sistema não exclui clientes com movimentações. Isso protege o histórico financeiro e operacional.

---

## 5. Cadastro de destinos

Menu: **Destinos**

### Cadastrar um destino

1. Clique em **Novo destino**.
2. Informe:
   - Nome do destino;
   - Cidade;
   - Estado ou província (opcional);
   - País;
   - Continente.
3. Escreva uma descrição com atrativos, época ideal e observações.
4. Clique em **Cadastrar destino**.

A combinação de **nome + cidade + país** deve ser única.

### Listar e filtrar

- Busque por nome, cidade, país ou continente.
- Filtre por status ativo/inativo.
- Clique no destino para ver os pacotes vinculados e os indicadores.

### Editar ou desativar

- Abra o destino e clique em **Editar** para atualizar os dados.
- Use a ação de **ativar/desativar** para tirar o destino de novas vendas sem apagar o histórico.

> Um destino com pacotes vinculados não pode ser excluído fisicamente.

---

## 6. Cadastro de pacotes

Menu: **Pacotes**

### Cadastrar um pacote

1. Clique em **Novo pacote**.
2. Informe:
   - Nome do pacote;
   - Destino;
   - Categoria;
   - Preço base por pessoa;
   - Descrição do que está incluído.
3. Clique em **Cadastrar pacote**.

### Categorias disponíveis

- Praia
- Aventura
- Cultural
- Gastronomia
- Ecoturismo
- Luxo
- Religioso
- Negócios

### Boas práticas

- Use nomes curtos e comerciais, como “Rio Essencial” ou “Gramado dos Sonhos”.
- Informe no preço base o valor por pessoa.
- Detalhe no texto o que está incluído: transporte, hospedagem, passeios, refeições e seguro.
- Mantenha o pacote **ativo** apenas enquanto estiver à venda.

### Editar ou desativar

- Abra o pacote e clique em **Editar**.
- Use **ativar/desativar** para interromper novas vendas sem afetar viagens e reservas já existentes.

> O preço base serve de referência. Cada viagem pode ter seu próprio preço por pessoa.

---

## 7. Programação de viagens

Menu: **Viagens**

A viagem é a **partida concreta** de um pacote, com data, vagas e preço próprios.

### Programar uma viagem

1. Clique em **Nova viagem**.
2. Selecione o **pacote**.
3. Informe:
   - Data de partida;
   - Data de retorno;
   - Vagas totais;
   - Preço por pessoa;
   - Status inicial;
   - Observações internas.
4. Clique em **Programar viagem**.

O campo de preço é preenchido automaticamente com o preço base do pacote, mas pode ser ajustado.

### Status da viagem

| Status | Quando usar |
| --- | --- |
| **Planejada** | Partida em negociação ou ainda sem confirmação. |
| **Confirmada** | Partida garantida e divulgada aos clientes. |
| **Em andamento** | Grupo já embarcou. |
| **Concluída** | Viagem realizada. |
| **Cancelada** | Partida cancelada. |

### Acompanhar ocupação

- A listagem mostra vagas ocupadas, vagas restantes e percentual de ocupação.
- A ocupação considera as reservas **não canceladas**.
- A ficha da viagem mostra as reservas vinculadas e o resumo financeiro.

### Editar, alterar status ou excluir

- **Editar:** atualiza datas, vagas, preço e observações.
- **Alterar status:** use o seletor na ficha da viagem.
- **Excluir:** disponível apenas quando não há reservas vinculadas.

> Evite reduzir as vagas abaixo do número de viajantes já reservados.

---

## 8. Agendamentos de viagens

Menu: **Agendamentos**

O agendamento é a **solicitação de viagem feita por um cliente**. Ele existe para registrar o pedido antes da reserva: o atendente anota o que o cliente quer, o gestor avalia e, se aprovar, a solicitação vira reserva sem precisar digitar tudo de novo.

Use o agendamento quando o cliente ainda não fechou a compra, quando depende de confirmação de acompanhantes ou quando a decisão precisa passar por outra pessoa.

### Como o fluxo funciona

```text
Solicitação registrada → Análise do gestor → Aprovado → Reserva confirmada
                                          ↘ Rejeitado
                            ↘ Cancelado (desistência)
```

### Criar um agendamento

1. Clique em **Novo agendamento**.
2. Selecione o **cliente**.
3. Selecione a **viagem**.
   - A lista mostra apenas viagens com status **planejada** ou **confirmada**.
   - Cada opção exibe destino, pacote, data de partida, preço e vagas disponíveis.
4. Informe a **quantidade de viajantes**.
5. Confirme o **valor unitário** — ele já vem sugerido pelo preço da viagem e pode ser ajustado.
6. Escolha o **status inicial** — o padrão é **Solicitado**.
7. Use **Observações** para registrar preferências, restrições alimentares, datas flexíveis ou combinados com o cliente.
8. Clique em **Solicitar agendamento**.

O total é calculado automaticamente:

```text
Total = valor unitário × quantidade de viajantes
```

O sistema gera um código no padrão `AG-AAAA-XXXXXX` e valida se a viagem ainda tem vagas suficientes para a quantidade informada.

### Status do agendamento

| Status | Significado |
| --- | --- |
| **Solicitado** | Aguardando análise do gestor. |
| **Aprovado** | Liberado para virar reserva. |
| **Rejeitado** | Pedido recusado (sem vagas, condições não atendidas, etc.). |
| **Cancelado** | Cliente desistiu da solicitação. |

### Acompanhar as solicitações

A tela de agendamentos mostra:

- Quantidade total de agendamentos;
- Quantos estão **solicitados**, **aprovados** e **rejeitados**;
- Busca por cliente, destino ou código;
- Filtro por status.

Quando existem solicitações aguardando decisão, o menu **Agendamentos** exibe um contador e a listagem oferece o atalho **pendentes**, que já abre o filtro correto.

### Aprovar ou rejeitar

Na listagem, use os ícones de ação:

- **Aprovar** (✓): muda o status para **Aprovado**.
- **Rejeitar** (✕): muda o status para **Rejeitado**.

As mesmas ações aparecem na ficha do agendamento, e qualquer mudança pede confirmação antes de ser aplicada.

Também é possível alterar o status pelo formulário de edição, campo **Status**. Use essa opção quando precisar corrigir um status manualmente ou registrar um cancelamento.

### Converter em reserva

1. Abra um agendamento com status **Aprovado**.
2. Clique em **Converter em reserva**.
3. O sistema cria uma reserva **confirmada** e abre a ficha dela.

A conversão reaproveita cliente, viagem, quantidade de viajantes e valor unitário do agendamento. Nas observações da reserva fica registrado de qual agendamento ela veio.

### Regras importantes

- Só um agendamento **aprovado** pode ser convertido em reserva.
- A conversão é bloqueada se o cliente já tiver uma reserva ativa para a mesma viagem, evitando cobrança em duplicidade.
- O agendamento não reserva vaga sozinho: quem consome vaga é a reserva.
- A quantidade de viajantes precisa caber nas vagas disponíveis no momento da solicitação.
- A desistência do cliente deve ser registrada como **Cancelado**, mantendo o histórico da conversa.

> Enquanto o agendamento estiver apenas **Solicitado**, as vagas continuam disponíveis para outros clientes. Por isso, confirme o pagamento assim que a reserva for criada.

### Depois da conversão

A reserva gerada segue o fluxo normal descrito na seção seguinte: lançamento de pagamentos, baixa, acompanhamento do saldo e conclusão da viagem.

---

## 9. Criação de reservas

Menu: **Reservas**

### Criar uma reserva

1. Clique em **Nova reserva**.
2. Selecione o **cliente**.
3. Selecione a **viagem**.
4. Informe:
   - Quantidade de viajantes;
   - Desconto em reais (opcional);
   - Status inicial;
   - Observações.
5. Confira o **Resumo da reserva**, que calcula automaticamente:
   - Valor por pessoa;
   - Subtotal;
   - Total da reserva.
6. Clique em **Criar reserva**.

### Como o total é calculado

```text
Total = (valor por pessoa × quantidade de viajantes) − desconto
```

O sistema valida a disponibilidade de vagas e impede reservas acima da capacidade da viagem.

### Código da reserva

Cada reserva recebe um código automático no padrão:

```text
HV-2026-A1B2C3
```

Use esse código para localizar a reserva, tratar pagamentos e emitir comprovantes.

### Status da reserva

| Status | Significado |
| --- | --- |
| **Pendente** | Reserva criada, aguardando confirmação ou pagamento. |
| **Confirmada** | Reserva validada e com pagamento inicial ou quitação. |
| **Concluída** | Viagem realizada e reserva encerrada. |
| **Cancelada** | Reserva cancelada pelo cliente ou pela agência. |

### Regras importantes

- A reserva é marcada como **Confirmada** automaticamente quando o total pago atinge o valor total.
- Para **concluir** uma reserva, é necessário quitar ou cancelar todos os pagamentos em aberto.
- Ao **cancelar** uma reserva, as cobranças pendentes ou atrasadas são canceladas automaticamente.
- Reservas canceladas não ocupam vagas.

### Ações na ficha da reserva

- **Editar:** altera cliente, viagem, viajantes, desconto, status e observações.
- **Registrar pagamento:** abre o formulário já vinculado à reserva.
- **Novo pagamento:** atalho na lista de pagamentos da reserva.
- **Atualizar status:** altera a etapa comercial.
- **Ver no financeiro:** abre a listagem de pagamentos filtrada.
- **Ver cadastro do cliente:** abre a ficha do cliente.

### Acompanhamento financeiro

A ficha da reserva exibe:

- Valor total;
- Total recebido;
- Saldo em aberto;
- Percentual de quitação;
- Valor por pessoa;
- Quantidade de viajantes;
- Subtotal, desconto e total;
- Valores em atraso.

---

## 10. Controle de pagamentos

Menu: **Pagamentos**

### Registrar um pagamento

1. Clique em **Novo pagamento**.
2. Selecione a **reserva**.
3. Confira o saldo devedor exibido pelo sistema.
4. Informe:
   - Valor;
   - Forma de pagamento;
   - Status da cobrança;
   - Vencimento;
   - Data de recebimento (quando pago);
   - Referência ou comprovante;
   - Observações.
5. Clique em **Registrar pagamento**.

### Formas de pagamento

- PIX
- Cartão de crédito
- Cartão de débito
- Boleto
- Transferência bancária
- Dinheiro

### Status do pagamento

| Status | Significado |
| --- | --- |
| **Pendente** | Cobrança emitida e ainda não recebida. |
| **Pago** | Valor recebido e confirmado. |
| **Atrasado** | Cobrança vencida e não paga. |
| **Estornado** | Valor devolvido ao cliente; permanece no histórico. |
| **Cancelado** | Cobrança cancelada sem efeito financeiro. |

### Baixar um pagamento

1. Localize o lançamento na listagem ou na ficha da reserva.
2. Use a ação **Baixar** (marcar como pago).
3. O sistema registra a data de recebimento e atualiza o status da reserva, se necessário.

### Estornar um pagamento

Use **Estornar** quando o valor precisar ser devolvido. O lançamento não é apagado: ele muda para **Estornado** e continua visível para auditoria.

### Excluir um pagamento

A exclusão é indicada para lançamentos criados por engano. Sempre que possível, prefira **Cancelar** ou **Estornar** para preservar o histórico.

### Bloqueio de valores acima do saldo

O sistema não permite registrar pagamentos que ultrapassem o saldo devedor da reserva. Se precisar de ajuste, edite ou exclua o lançamento incorreto antes de criar outro.

### Atualização automática de atrasos

Ao acessar o sistema, pagamentos **pendentes** com vencimento anterior à data atual são atualizados para **atrasado**. O painel e o menu exibem a quantidade de cobranças vencidas.

### Filtros da listagem

- Busca por cliente, código da reserva ou referência;
- Status;
- Data de vencimento inicial e final.

Os cartões do topo mostram **recebido no mês**, **a receber**, **em atraso** e **total de lançamentos** conforme os filtros aplicados.

---

## 11. Relatórios

Menu: **Relatórios**

### Aplicar filtros

1. Informe a **data inicial** e a **data final**.
2. Selecione um **destino** ou mantenha **Todos os destinos**.
3. Clique em **Aplicar filtros**.

O sistema compara automaticamente o período atual com o período anterior de mesma duração.

### Indicadores do relatório

- Receita recebida;
- Reservas no período e viajantes;
- Ticket médio;
- Valores em aberto;
- Clientes novos;
- Receita mensal;
- Top destinos por valor contratado;
- Desempenho das viagens;
- Reservas por status;
- Ocupação das viagens;
- Formas de pagamento;
- Comparativo com o período anterior.

### Exportar CSV

1. Aplique os filtros desejados.
2. Clique em **Exportar CSV**.
3. Abra o arquivo no Excel, LibreOffice ou Google Sheets.

O arquivo usa:

- Codificação UTF-8 com BOM;
- Separador `;`;
- Datas no formato `dd/mm/aaaa`;
- Valores no formato brasileiro.

### Imprimir

1. Clique em **Imprimir**.
2. A versão de impressão abre em uma tela limpa.
3. Use o botão **Imprimir** do navegador ou `Ctrl+P`.

A versão impressa inclui cabeçalho da agência, período, data de emissão e tabelas de apoio.

### Dicas de análise

- Compare a **receita recebida** com o **valor contratado** para identificar vendas ainda não quitadas.
- Use **Top destinos** para direcionar campanhas e parcerias.
- Acompanhe **formas de pagamento** para negociar taxas com operadoras.
- Verifique **ocupação** antes de confirmar novas partidas.

---

## 12. Usuários e permissões

Menu: **Usuários** (somente administrador)

### Criar um usuário

1. Clique em **Novo usuário**.
2. Informe:
   - Nome completo;
   - E-mail;
   - Perfil;
   - Telefone (opcional);
   - Senha e confirmação.
3. Clique em **Cadastrar usuário**.

### Perfis

- **Administrador:** acesso total, incluindo usuários.
- **Agente:** acesso operacional e relatórios, sem gestão de usuários.

### Editar um usuário

- Abra o usuário e clique em **Editar**.
- Atualize nome, e-mail, perfil, telefone ou senha.
- Para trocar a senha, informe a nova senha e a confirmação.

### Ativar ou desativar

Use a ação de **ativar/desativar** para bloquear o acesso sem excluir o histórico de ações do usuário.

> O sistema impede que o administrador desative a própria conta ou remova seu próprio acesso de administrador.

---

## 13. Meu perfil

Menu: **Meu perfil** (ou clique no seu nome no topo)

### Atualizar dados pessoais

1. Altere nome, e-mail ou telefone.
2. Clique em **Salvar dados**.

### Alterar a senha

1. Informe a **senha atual**.
2. Informe a **nova senha** e a confirmação.
3. Clique em **Alterar senha**.

A nova senha deve ter pelo menos 6 caracteres.

### Indicadores pessoais

A tela mostra:

- Reservas registradas por você;
- Pagamentos lançados por você;
- Total recebido;
- Data de criação da conta;
- Último acesso;
- Perfil de acesso.

---

## 14. Rotinas recomendadas

### Diariamente

- [ ] Conferir o **Painel**, especialmente “Precisa de atenção”.
- [ ] Analisar os **agendamentos solicitados** e aprovar, rejeitar ou converter em reserva.
- [ ] Dar baixa nos pagamentos recebidos.
- [ ] Verificar cobranças vencidas e entrar em contato com os clientes.
- [ ] Confirmar reservas pendentes com pagamento inicial.
- [ ] Atualizar o status das viagens que embarcaram ou retornaram.

### Semanalmente

- [ ] Revisar a ocupação das próximas viagens.
- [ ] Conferir reservas pendentes sem pagamento.
- [ ] Atualizar dados de contato dos clientes.
- [ ] Revisar pacotes ativos e preços.

### Mensalmente

- [ ] Emitir o relatório gerencial do mês.
- [ ] Comparar receita recebida com o período anterior.
- [ ] Analisar top destinos e formas de pagamento.
- [ ] Exportar o CSV para a contabilidade.
- [ ] Fazer backup do banco de dados.
- [ ] Revisar usuários ativos e permissões.

---

## 15. Perguntas frequentes

### Por que não consigo excluir um cliente, destino ou pacote?

Esses registros possuem vínculos com reservas, viagens ou pagamentos. Para preservar o histórico, o sistema bloqueia a exclusão. Use a opção **desativar**.

### Por que não consigo concluir uma reserva?

Existem pagamentos pendentes ou atrasados. Dê baixa, cancele ou estorne esses lançamentos antes de concluir.

### Por que o sistema não aceita um pagamento maior que o saldo?

O valor ultrapassa o saldo devedor da reserva. Confira o total já pago e ajuste o valor do lançamento.

### O status da reserva mudou sozinho. Isso é normal?

Sim. Quando a soma dos pagamentos atinge o valor total, a reserva passa automaticamente de **Pendente** para **Confirmada**.

### Por que um pagamento ficou “Atrasado”?

A data de vencimento já passou e o pagamento ainda estava **Pendente**. O sistema atualiza esse status automaticamente ao abrir o sistema.

### Posso alterar o valor de uma viagem já vendida?

Sim, mas isso não recalcula reservas existentes. As reservas mantêm o valor unitário registrado no momento da venda. Para novas reservas, o novo preço passa a valer.

### Como encontrar rapidamente uma reserva?

Use a busca em **Reservas** pelo código, nome do cliente ou destino.

### O que acontece ao cancelar uma reserva?

As cobranças pendentes ou atrasadas são canceladas e as vagas voltam a ficar disponíveis. O histórico da reserva permanece.

### Como abrir o CSV corretamente no Excel?

Basta dar duplo clique. O arquivo já inclui BOM UTF-8 e separador `;`. Se o Excel abrir tudo em uma coluna, use **Dados → Texto para Colunas** e escolha o separador `;`.

### Por que não consigo converter um agendamento em reserva?

A conversão exige status **Aprovado** e que o cliente ainda não tenha uma reserva ativa para aquela viagem. Aprove o agendamento primeiro; se já houver reserva, use a reserva existente.

### Esqueci minha senha. O que fazer?

Solicite a um administrador que redefina a senha em **Usuários → Editar**. Por segurança, não há recuperação automática por e-mail nesta versão.

---

## 16. Glossário

| Termo | Definição |
| --- | --- |
| **Destino** | Local geográfico vendido pela agência. |
| **Pacote** | Produto comercial vinculado a um destino, com categoria e preço base. |
| **Viagem** | Partida concreta de um pacote, com datas, vagas e preço. |
| **Agendamento** | Solicitação de viagem feita por um cliente, antes da reserva. |
| **Reserva** | Vínculo entre um cliente e uma viagem. |
| **Pagamento** | Lançamento financeiro de uma reserva. |
| **Baixa** | Ato de marcar um pagamento como recebido. |
| **Estorno** | Devolução de um valor já recebido. |
| **Ticket médio** | Valor médio por reserva no período. |
| **Ocupação** | Relação entre viajantes confirmados e vagas ofertadas. |
| **Receita recebida** | Soma dos pagamentos com status **Pago**. |
| **Valor contratado** | Soma dos valores totais das reservas no período. |
| **Saldo devedor** | Valor total da reserva menos o total já pago. |

---

## Suporte

Em caso de dúvidas técnicas sobre instalação, configuração ou backup, consulte o [README](README.md).

Para problemas de acesso, procure o administrador do sistema.
