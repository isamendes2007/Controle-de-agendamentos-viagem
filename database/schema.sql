-- ============================================================
-- Horizonte Viagens — Estrutura do banco (MySQL 8 / MariaDB 10.4+)
--
-- Mesmas tabelas, colunas e regras do database/sqlite/schema.sql.
-- Use este arquivo na hospedagem (DB_DRIVER=mysql) ou em um MySQL local.
-- ============================================================

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('administrador', 'agente') NOT NULL DEFAULT 'agente',
    telefone VARCHAR(20) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    ultimo_acesso DATETIME NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email),
    KEY idx_usuarios_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clientes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(120) NOT NULL,
    cpf VARCHAR(14) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    data_nascimento DATE NULL,
    cep VARCHAR(9) NULL,
    endereco VARCHAR(180) NULL,
    numero VARCHAR(20) NULL,
    complemento VARCHAR(80) NULL,
    bairro VARCHAR(100) NULL,
    cidade VARCHAR(100) NULL,
    estado VARCHAR(40) NULL,
    observacoes TEXT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_clientes_cpf (cpf),
    KEY idx_clientes_nome (nome),
    KEY idx_clientes_email (email),
    KEY idx_clientes_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS destinos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(120) NOT NULL,
    cidade VARCHAR(120) NOT NULL,
    estado VARCHAR(80) NULL,
    pais VARCHAR(80) NOT NULL,
    continente VARCHAR(60) NOT NULL,
    descricao TEXT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_destinos_nome_cidade_pais (nome, cidade, pais),
    KEY idx_destinos_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pacotes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    destino_id INT UNSIGNED NOT NULL,
    nome VARCHAR(120) NOT NULL,
    categoria ENUM('praia', 'aventura', 'cultural', 'gastronomia', 'ecoturismo', 'luxo', 'religioso', 'negocios') NOT NULL DEFAULT 'cultural',
    descricao TEXT NULL,
    preco_base DECIMAL(10, 2) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pacotes_nome_destino (nome, destino_id),
    KEY idx_pacotes_destino (destino_id),
    KEY idx_pacotes_ativo (ativo),
    CONSTRAINT fk_pacotes_destino FOREIGN KEY (destino_id) REFERENCES destinos (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS viagens (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    pacote_id INT UNSIGNED NOT NULL,
    data_partida DATE NOT NULL,
    data_retorno DATE NOT NULL,
    vagas_totais INT UNSIGNED NOT NULL,
    preco DECIMAL(10, 2) NOT NULL,
    status ENUM('planejada', 'confirmada', 'em_andamento', 'concluida', 'cancelada') NOT NULL DEFAULT 'planejada',
    observacoes TEXT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_viagens_pacote_data (pacote_id, data_partida),
    KEY idx_viagens_data (data_partida),
    KEY idx_viagens_status (status),
    CONSTRAINT fk_viagens_pacote FOREIGN KEY (pacote_id) REFERENCES pacotes (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reservas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(20) NOT NULL,
    cliente_id INT UNSIGNED NOT NULL,
    viagem_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    qtd_viajantes INT UNSIGNED NOT NULL DEFAULT 1,
    valor_unitario DECIMAL(10, 2) NOT NULL,
    desconto DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    valor_total DECIMAL(10, 2) NOT NULL,
    status ENUM('pendente', 'confirmada', 'concluida', 'cancelada') NOT NULL DEFAULT 'pendente',
    observacoes TEXT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_reservas_codigo (codigo),
    KEY idx_reservas_cliente (cliente_id),
    KEY idx_reservas_viagem (viagem_id),
    KEY idx_reservas_status (status),
    KEY idx_reservas_criado (criado_em),
    CONSTRAINT fk_reservas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_reservas_viagem FOREIGN KEY (viagem_id) REFERENCES viagens (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_reservas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pagamentos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    reserva_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    valor DECIMAL(10, 2) NOT NULL,
    forma_pagamento ENUM('pix', 'cartao_credito', 'cartao_debito', 'boleto', 'transferencia', 'dinheiro') NOT NULL DEFAULT 'pix',
    status ENUM('pendente', 'pago', 'atrasado', 'estornado', 'cancelado') NOT NULL DEFAULT 'pendente',
    data_vencimento DATE NOT NULL,
    data_pagamento DATE NULL,
    referencia VARCHAR(80) NULL,
    observacoes TEXT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pagamentos_reserva (reserva_id),
    KEY idx_pagamentos_status (status),
    KEY idx_pagamentos_vencimento (data_vencimento),
    KEY idx_pagamentos_pagamento (data_pagamento),
    CONSTRAINT fk_pagamentos_reserva FOREIGN KEY (reserva_id) REFERENCES reservas (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_pagamentos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agendamentos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(20) NOT NULL,
    cliente_id INT UNSIGNED NOT NULL,
    viagem_id INT UNSIGNED NOT NULL,
    qtd_viajantes INT UNSIGNED NOT NULL DEFAULT 1,
    valor_unitario DECIMAL(10, 2) NOT NULL,
    valor_total DECIMAL(10, 2) NOT NULL,
    status ENUM('solicitado', 'aprovado', 'rejeitado', 'cancelado') NOT NULL DEFAULT 'solicitado',
    observacoes TEXT NULL,
    usuario_id INT UNSIGNED NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_agendamentos_codigo (codigo),
    KEY idx_agendamentos_cliente (cliente_id),
    KEY idx_agendamentos_viagem (viagem_id),
    KEY idx_agendamentos_status (status),
    CONSTRAINT fk_agendamentos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_agendamentos_viagem FOREIGN KEY (viagem_id) REFERENCES viagens (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_agendamentos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
