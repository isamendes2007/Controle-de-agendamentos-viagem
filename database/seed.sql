-- ============================================================
-- Horizonte Viagens — Dados de demonstração (MySQL 8 / MariaDB)
--
-- Mesmos registros do database/sqlite/seed.sql.
-- Senhas: admin123 (administrador) e agente123 (agente).
-- ============================================================

INSERT INTO usuarios (id, nome, email, senha, perfil, telefone, ativo) VALUES
(1, 'Marina Costa', 'admin@horizonteviagens.com.br', '$2y$10$DnMdfdt/6HI74lw7ItyqFelCWxK8ueyXQul7g1GoxiDJvM4klNbnO', 'administrador', '(11) 98765-4321', 1),
(2, 'Rafael Almeida', 'rafael@horizonteviagens.com.br', '$2y$10$XajbIr4gLHnzCyir/GQrvuKYBwc4M3X5nOOUmHxZJT4TUdJJ1WZou', 'agente', '(21) 99123-4567', 1);

INSERT INTO clientes (id, nome, cpf, email, telefone, data_nascimento, cep, endereco, numero, complemento, bairro, cidade, estado, observacoes, ativo, criado_em) VALUES
(1, 'Ana Beatriz Souza', '529.982.247-25', 'ana.souza@email.com', '(21) 99876-5432', '1988-04-12', '22071060', 'Rua Barata Ribeiro', '120', 'Apto 802', 'Copacabana', 'Rio de Janeiro', 'RJ', 'Prefere voos pela manhã.', 1, DATE_SUB(NOW(), INTERVAL 220 DAY)),
(2, 'Carlos Eduardo Lima', '111.444.777-35', 'carlos.lima@email.com', '(11) 98765-1122', '1979-09-23', '01310100', 'Avenida Paulista', '1578', 'Conjunto 54', 'Bela Vista', 'São Paulo', 'SP', 'Cliente corporativo. Emissão de nota fiscal.', 1, DATE_SUB(NOW(), INTERVAL 190 DAY)),
(3, 'Fernanda Oliveira', '123.456.789-09', 'fernanda.oliveira@email.com', '(31) 99123-7788', '1992-01-30', '30130010', 'Rua da Bahia', '1148', NULL, 'Centro', 'Belo Horizonte', 'MG', 'Viaja com frequência em casal.', 1, DATE_SUB(NOW(), INTERVAL 150 DAY)),
(4, 'João Pedro Martins', '987.654.321-00', 'joao.martins@email.com', '(41) 98888-4455', '1985-07-08', '80420120', 'Rua Comendador Araújo', '421', 'Sala 7', 'Batel', 'Curitiba', 'PR', NULL, 1, DATE_SUB(NOW(), INTERVAL 130 DAY)),
(5, 'Juliana Santos', '390.533.447-05', 'juliana.santos@email.com', '(51) 99777-2233', '1990-11-17', '90570020', 'Rua Padre Chagas', '300', 'Apto 1201', 'Moinhos de Vento', 'Porto Alegre', 'RS', 'Alergia a frutos do mar.', 1, DATE_SUB(NOW(), INTERVAL 110 DAY)),
(6, 'Marcos Vinícius Rocha', '862.883.967-45', 'marcos.rocha@email.com', '(71) 99222-8899', '1983-03-05', '40015010', 'Avenida Sete de Setembro', '77', NULL, 'Centro', 'Salvador', 'BA', NULL, 1, DATE_SUB(NOW(), INTERVAL 90 DAY)),
(7, 'Patrícia Almeida', '153.509.460-56', 'patricia.almeida@email.com', '(81) 99666-7788', '1995-06-21', '51020010', 'Rua dos Navegantes', '640', 'Apto 903', 'Boa Viagem', 'Recife', 'PE', 'Solicita quarto com cama king.', 1, DATE_SUB(NOW(), INTERVAL 75 DAY)),
(8, 'Ricardo Nunes', '435.178.236-60', 'ricardo.nunes@email.com', '(61) 98111-3344', '1976-12-02', '70200030', 'SQS 108', 'Bloco C', 'Apto 401', 'Asa Sul', 'Brasília', 'DF', NULL, 1, DATE_SUB(NOW(), INTERVAL 60 DAY)),
(9, 'Camila Ferreira', '246.813.579-28', 'camila.ferreira@email.com', '(48) 99555-6677', '1998-08-14', '88010000', 'Rua Felipe Schmidt', '215', 'Apto 502', 'Centro', 'Florianópolis', 'SC', 'Lua de mel em Fernando de Noronha.', 1, DATE_SUB(NOW(), INTERVAL 45 DAY)),
(10, 'Bruno Costa', '135.792.468-28', 'bruno.costa@email.com', '(19) 98333-9900', '1987-02-19', '13010111', 'Rua Coronel Quirino', '880', NULL, 'Cambuí', 'Campinas', 'SP', NULL, 1, DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT INTO destinos (id, nome, cidade, estado, pais, continente, descricao, ativo) VALUES
(1, 'Rio de Janeiro', 'Rio de Janeiro', 'RJ', 'Brasil', 'América do Sul', 'Cidade maravilhosa, com praias icônicas, paisagens de tirar o fôlego e uma cultura vibrante.', 1),
(2, 'Gramado', 'Gramado', 'RS', 'Brasil', 'América do Sul', 'Charme de serra, gastronomia premiada e cenários encantadores em qualquer estação.', 1),
(3, 'Salvador', 'Salvador', 'BA', 'Brasil', 'América do Sul', 'Berço da cultura afro-brasileira, com centro histórico, música e litoral exuberante.', 1),
(4, 'Buenos Aires', 'Buenos Aires', 'Cidade Autônoma de Buenos Aires', 'Argentina', 'América do Sul', 'Elegância, tango, cafés históricos e uma cena gastronômica reconhecida mundialmente.', 1),
(5, 'Lisboa', 'Lisboa', NULL, 'Portugal', 'Europa', 'Colinas, azulejos, história e uma luz única às margens do rio Tejo.', 1),
(6, 'Santiago', 'Santiago', 'Região Metropolitana', 'Chile', 'América do Sul', 'Porta de entrada para os Andes, com neve, vinícolas e uma cidade cosmopolita.', 1),
(7, 'Fernando de Noronha', 'Fernando de Noronha', 'PE', 'Brasil', 'América do Sul', 'Arquipélago de águas cristalinas, vida marinha e paisagens preservadas.', 1),
(8, 'Bonito', 'Bonito', 'MS', 'Brasil', 'América do Sul', 'Ecoturismo de água doce, rios transparentes, grutas e florestas.', 1);

INSERT INTO pacotes (id, destino_id, nome, categoria, descricao, preco_base, ativo) VALUES
(1, 1, 'Rio Essencial', 'praia', 'Quatro dias no Rio com hospedagem em Copacabana, passeio ao Cristo Redentor e Pão de Açúcar.', 2890.00, 1),
(2, 2, 'Gramado dos Sonhos', 'gastronomia', 'Cinco dias na Serra Gaúcha com rota gastronômica, Lago Negro e vinícola.', 3450.00, 1),
(3, 3, 'Bahia Histórica', 'cultural', 'Seis dias em Salvador com Pelourinho, mercado modelo e praias do litoral norte.', 3990.00, 1),
(4, 4, 'Buenos Aires Gourmet', 'gastronomia', 'Quatro dias na capital argentina com jantar de tango, feira de San Telmo e aulas de cozinha.', 5200.00, 1),
(5, 5, 'Lisboa Encantada', 'cultural', 'Oito dias em Portugal com Lisboa, Sintra, Cascais e degustação de vinhos.', 8990.00, 1),
(6, 6, 'Santiago na Neve', 'aventura', 'Seis dias no Chile com Valle Nevado, city tour e vinícola Concha y Toro.', 6750.00, 1),
(7, 7, 'Noronha Premium', 'luxo', 'Cinco dias em Fernando de Noronha com pousada boutique, passeio de barco e mergulho.', 11200.00, 1),
(8, 8, 'Bonito Ecoturismo', 'ecoturismo', 'Quatro dias em Bonito com flutuação no Rio Sucuri, grutas e trilhas guiadas.', 4850.00, 1);

INSERT INTO viagens (id, pacote_id, data_partida, data_retorno, vagas_totais, preco, status, observacoes) VALUES
(1, 1, DATE_SUB(CURDATE(), INTERVAL 45 DAY), DATE_SUB(CURDATE(), INTERVAL 41 DAY), 20, 2890.00, 'concluida', 'Grupo com guia local.'),
(2, 1, DATE_ADD(CURDATE(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 24 DAY), 20, 2990.00, 'confirmada', 'Saída confirmada com guia.'),
(3, 2, DATE_ADD(CURDATE(), INTERVAL 35 DAY), DATE_ADD(CURDATE(), INTERVAL 40 DAY), 18, 3590.00, 'planejada', 'Aguardando fechamento do grupo.'),
(4, 3, DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 16 DAY), 22, 3990.00, 'confirmada', 'Roteiro com passeios inclusos.'),
(5, 4, DATE_ADD(CURDATE(), INTERVAL 50 DAY), DATE_ADD(CURDATE(), INTERVAL 54 DAY), 16, 5450.00, 'planejada', NULL),
(6, 5, DATE_ADD(CURDATE(), INTERVAL 80 DAY), DATE_ADD(CURDATE(), INTERVAL 88 DAY), 14, 9290.00, 'confirmada', 'Passagens aéreas emitidas.'),
(7, 6, DATE_ADD(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 36 DAY), 12, 6890.00, 'confirmada', 'Inclui aluguel de equipamento de neve.'),
(8, 7, DATE_ADD(CURDATE(), INTERVAL 65 DAY), DATE_ADD(CURDATE(), INTERVAL 70 DAY), 10, 11800.00, 'planejada', 'Pousada boutique com pensão completa.'),
(9, 2, DATE_SUB(CURDATE(), INTERVAL 100 DAY), DATE_SUB(CURDATE(), INTERVAL 95 DAY), 18, 3300.00, 'concluida', NULL),
(10, 3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 4 DAY), 22, 3890.00, 'em_andamento', 'Grupo em Salvador. Contato: guia André.'),
(11, 5, DATE_SUB(CURDATE(), INTERVAL 150 DAY), DATE_SUB(CURDATE(), INTERVAL 142 DAY), 14, 8700.00, 'concluida', NULL),
(12, 8, DATE_ADD(CURDATE(), INTERVAL 90 DAY), DATE_ADD(CURDATE(), INTERVAL 94 DAY), 16, 4890.00, 'planejada', 'Turma de ecoturismo.');

INSERT INTO reservas (id, codigo, cliente_id, viagem_id, usuario_id, qtd_viajantes, valor_unitario, desconto, valor_total, status, observacoes, criado_em) VALUES
(1, 'HV-2026-A1B2C3', 1, 2, 2, 2, 2990.00, 0.00, 5980.00, 'confirmada', 'Casal. Quarto com vista para o mar.', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(2, 'HV-2026-D4E5F6', 2, 4, 1, 2, 3990.00, 200.00, 7780.00, 'confirmada', 'Desconto de 200 reais aplicado na negociação.', DATE_SUB(NOW(), INTERVAL 35 DAY)),
(3, 'HV-2026-G7H8I9', 3, 6, 2, 1, 9290.00, 0.00, 9290.00, 'pendente', 'Aguardando confirmação do pagamento do saldo.', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(4, 'HV-2026-J1K2L3', 4, 10, 1, 2, 3890.00, 0.00, 7780.00, 'confirmada', NULL, DATE_SUB(NOW(), INTERVAL 40 DAY)),
(5, 'HV-2026-M4N5O6', 5, 7, 2, 3, 6890.00, 0.00, 20670.00, 'confirmada', 'Família com um adolescente.', DATE_SUB(NOW(), INTERVAL 25 DAY)),
(6, 'HV-2026-P7Q8R9', 6, 3, 2, 2, 3590.00, 0.00, 7180.00, 'pendente', 'Cliente pediu proposta de parcelamento.', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(7, 'HV-2026-S1T2U3', 7, 1, 1, 1, 2890.00, 0.00, 2890.00, 'concluida', NULL, DATE_SUB(NOW(), INTERVAL 70 DAY)),
(8, 'HV-2026-V4W5X6', 8, 9, 1, 2, 3300.00, 0.00, 6600.00, 'concluida', NULL, DATE_SUB(NOW(), INTERVAL 120 DAY)),
(9, 'HV-2026-Y7Z8A9', 9, 8, 2, 2, 11800.00, 0.00, 23600.00, 'confirmada', 'Lua de mel. Preparar surpresa no quarto.', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(10, 'HV-2026-B1C2D3', 10, 5, 1, 2, 5450.00, 0.00, 10900.00, 'cancelada', 'Cancelada pelo cliente por motivos pessoais.', DATE_SUB(NOW(), INTERVAL 30 DAY)),
(11, 'HV-2026-E4F5G6', 1, 11, 2, 2, 8700.00, 0.00, 17400.00, 'concluida', NULL, DATE_SUB(NOW(), INTERVAL 170 DAY)),
(12, 'HV-2026-H7I8J9', 4, 4, 2, 1, 3990.00, 0.00, 3990.00, 'pendente', 'Viajante adicional do mesmo grupo.', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(13, 'HV-2026-K1L2M3', 3, 2, 1, 1, 2990.00, 0.00, 2990.00, 'confirmada', NULL, DATE_SUB(NOW(), INTERVAL 2 DAY));

INSERT INTO pagamentos (id, reserva_id, usuario_id, valor, forma_pagamento, status, data_vencimento, data_pagamento, referencia, observacoes) VALUES
(1, 1, 2, 2990.00, 'pix', 'pago', DATE_SUB(CURDATE(), INTERVAL 12 DAY), DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'PIX-88291', 'Sinal de 50%.'),
(2, 1, 2, 2990.00, 'pix', 'pendente', DATE_ADD(CURDATE(), INTERVAL 15 DAY), NULL, 'PIX-88292', 'Saldo da reserva.'),
(3, 2, 1, 3890.00, 'transferencia', 'pago', DATE_SUB(CURDATE(), INTERVAL 125 DAY), DATE_SUB(CURDATE(), INTERVAL 120 DAY), 'TED-1092', 'Primeira parcela.'),
(4, 2, 1, 3890.00, 'cartao_credito', 'pago', DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 28 DAY), 'CC-55018', 'Segunda parcela.'),
(5, 3, 2, 1858.00, 'pix', 'pago', DATE_SUB(CURDATE(), INTERVAL 7 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'PIX-77120', 'Sinal de 20%.'),
(6, 3, 2, 7432.00, 'cartao_credito', 'pendente', DATE_ADD(CURDATE(), INTERVAL 20 DAY), NULL, 'CC-77121', 'Saldo em uma parcela.'),
(7, 4, 1, 3890.00, 'boleto', 'pago', DATE_SUB(CURDATE(), INTERVAL 100 DAY), DATE_SUB(CURDATE(), INTERVAL 95 DAY), 'BOL-3301', NULL),
(8, 4, 1, 3890.00, 'pix', 'pago', DATE_SUB(CURDATE(), INTERVAL 8 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'PIX-3302', 'Quitação antes do embarque.'),
(9, 5, 2, 6890.00, 'cartao_credito', 'pago', DATE_SUB(CURDATE(), INTERVAL 115 DAY), DATE_SUB(CURDATE(), INTERVAL 110 DAY), 'CC-9901', 'Primeira parcela.'),
(10, 5, 2, 6890.00, 'cartao_credito', 'pago', DATE_SUB(CURDATE(), INTERVAL 55 DAY), DATE_SUB(CURDATE(), INTERVAL 50 DAY), 'CC-9902', 'Segunda parcela.'),
(11, 5, 2, 6890.00, 'boleto', 'pendente', DATE_ADD(CURDATE(), INTERVAL 25 DAY), NULL, 'BOL-9903', 'Última parcela.'),
(12, 6, 2, 1500.00, 'pix', 'pendente', DATE_SUB(CURDATE(), INTERVAL 2 DAY), NULL, 'PIX-4451', 'Sinal combinado com o cliente.'),
(13, 7, 1, 2890.00, 'pix', 'pago', DATE_SUB(CURDATE(), INTERVAL 68 DAY), DATE_SUB(CURDATE(), INTERVAL 65 DAY), 'PIX-6612', NULL),
(14, 8, 1, 6600.00, 'transferencia', 'pago', DATE_SUB(CURDATE(), INTERVAL 118 DAY), DATE_SUB(CURDATE(), INTERVAL 115 DAY), 'TED-7712', NULL),
(15, 9, 2, 11800.00, 'pix', 'pago', DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'PIX-9981', 'Sinal de 50%.'),
(16, 9, 2, 11800.00, 'cartao_credito', 'pendente', DATE_ADD(CURDATE(), INTERVAL 40 DAY), NULL, 'CC-9982', 'Saldo da lua de mel.'),
(17, 10, 1, 3270.00, 'pix', 'estornado', DATE_SUB(CURDATE(), INTERVAL 28 DAY), NULL, 'PIX-1002', 'Sinal devolvido após cancelamento.'),
(18, 10, 1, 7630.00, 'boleto', 'cancelado', DATE_SUB(CURDATE(), INTERVAL 20 DAY), NULL, 'BOL-1003', 'Cobrança cancelada.'),
(19, 11, 2, 17400.00, 'transferencia', 'pago', DATE_SUB(CURDATE(), INTERVAL 168 DAY), DATE_SUB(CURDATE(), INTERVAL 165 DAY), 'TED-3001', NULL),
(20, 12, 2, 1000.00, 'pix', 'pago', DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'PIX-2020', 'Sinal.'),
(21, 12, 2, 2990.00, 'boleto', 'pendente', DATE_SUB(CURDATE(), INTERVAL 5 DAY), NULL, 'BOL-2021', 'Saldo vencido.'),
(22, 13, 1, 2990.00, 'pix', 'pago', DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'PIX-3030', NULL);

INSERT INTO agendamentos (id, codigo, cliente_id, viagem_id, qtd_viajantes, valor_unitario, valor_total, status, observacoes, usuario_id, criado_em) VALUES
(1, 'AG-2026-A1B2C3', 2, 6, 2, 9290.00, 18580.00, 'solicitado', 'Cliente pediu cotação para acompanhante.', 2, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 'AG-2026-D4E5F6', 9, 5, 1, 5450.00, 5450.00, 'solicitado', 'Lua de mel: prefere quarto com varanda.', 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 'AG-2026-G7H8I9', 6, 8, 2, 11800.00, 23600.00, 'aprovado', 'Aprovado por telefone; aguardando conversão em reserva.', 1, DATE_SUB(NOW(), INTERVAL 6 DAY));
