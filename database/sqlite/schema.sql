-- ============================================================
-- Horizonte Viagens — Estrutura do banco (SQLite 3.31+)
--
-- Executado automaticamente na primeira abertura do sistema quando
-- o driver ativo é "sqlite". Não requer servidor de banco de dados.
-- ============================================================

CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    senha TEXT NOT NULL,
    perfil TEXT NOT NULL DEFAULT 'agente' CHECK (perfil IN ('administrador', 'agente')),
    telefone TEXT NULL,
    ativo INTEGER NOT NULL DEFAULT 1,
    ultimo_acesso TEXT NULL,
    criado_em TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    atualizado_em TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
);

CREATE TABLE IF NOT EXISTS clientes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    cpf TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL,
    telefone TEXT NOT NULL,
    data_nascimento TEXT NULL,
    cep TEXT NULL,
    endereco TEXT NULL,
    numero TEXT NULL,
    complemento TEXT NULL,
    bairro TEXT NULL,
    cidade TEXT NULL,
    estado TEXT NULL,
    observacoes TEXT NULL,
    ativo INTEGER NOT NULL DEFAULT 1,
    criado_em TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    atualizado_em TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
);

CREATE TABLE IF NOT EXISTS destinos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    cidade TEXT NOT NULL,
    estado TEXT NULL,
    pais TEXT NOT NULL,
    continente TEXT NOT NULL,
    descricao TEXT NULL,
    ativo INTEGER NOT NULL DEFAULT 1,
    criado_em TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    atualizado_em TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    UNIQUE (nome, cidade, pais)
);

CREATE TABLE IF NOT EXISTS pacotes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    destino_id INTEGER NOT NULL,
    nome TEXT NOT NULL,
    categoria TEXT NOT NULL DEFAULT 'cultural'
        CHECK (categoria IN ('praia', 'aventura', 'cultural', 'gastronomia', 'ecoturismo', 'luxo', 'religioso', 'negocios')),
    descricao TEXT NULL,
    preco_base DECIMAL(10, 2) NOT NULL,
    ativo INTEGER NOT NULL DEFAULT 1,
    criado_em TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    atualizado_em TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    UNIQUE (nome, destino_id),
    FOREIGN KEY (destino_id) REFERENCES destinos (id) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS viagens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pacote_id INTEGER NOT NULL,
    data_partida TEXT NOT NULL,
    data_retorno TEXT NOT NULL,
    vagas_totais INTEGER NOT NULL,
    preco DECIMAL(10, 2) NOT NULL,
    status TEXT NOT NULL DEFAULT 'planejada'
        CHECK (status IN ('planejada', 'confirmada', 'em_andamento', 'concluida', 'cancelada')),
    observacoes TEXT NULL,
    criado_em TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    atualizado_em TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    UNIQUE (pacote_id, data_partida),
    FOREIGN KEY (pacote_id) REFERENCES pacotes (id) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS reservas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    codigo TEXT NOT NULL UNIQUE,
    cliente_id INTEGER NOT NULL,
    viagem_id INTEGER NOT NULL,
    usuario_id INTEGER NULL,
    qtd_viajantes INTEGER NOT NULL DEFAULT 1,
    valor_unitario DECIMAL(10, 2) NOT NULL,
    desconto DECIMAL(10, 2) NOT NULL DEFAULT 0,
    valor_total DECIMAL(10, 2) NOT NULL,
    status TEXT NOT NULL DEFAULT 'pendente'
        CHECK (status IN ('pendente', 'confirmada', 'concluida', 'cancelada')),
    observacoes TEXT NULL,
    criado_em TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    atualizado_em TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (viagem_id) REFERENCES viagens (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS pagamentos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reserva_id INTEGER NOT NULL,
    usuario_id INTEGER NULL,
    valor DECIMAL(10, 2) NOT NULL,
    forma_pagamento TEXT NOT NULL DEFAULT 'pix'
        CHECK (forma_pagamento IN ('pix', 'cartao_credito', 'cartao_debito', 'boleto', 'transferencia', 'dinheiro')),
    status TEXT NOT NULL DEFAULT 'pendente'
        CHECK (status IN ('pendente', 'pago', 'atrasado', 'estornado', 'cancelado')),
    data_vencimento TEXT NOT NULL,
    data_pagamento TEXT NULL,
    referencia TEXT NULL,
    observacoes TEXT NULL,
    criado_em TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    atualizado_em TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    FOREIGN KEY (reserva_id) REFERENCES reservas (id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS agendamentos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    codigo TEXT NOT NULL UNIQUE,
    cliente_id INTEGER NOT NULL,
    viagem_id INTEGER NOT NULL,
    qtd_viajantes INTEGER NOT NULL DEFAULT 1,
    valor_unitario DECIMAL(10, 2) NOT NULL,
    valor_total DECIMAL(10, 2) NOT NULL,
    status TEXT NOT NULL DEFAULT 'solicitado'
        CHECK (status IN ('solicitado', 'aprovado', 'rejeitado', 'cancelado')),
    observacoes TEXT NULL,
    usuario_id INTEGER NULL,
    criado_em TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    atualizado_em TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (viagem_id) REFERENCES viagens (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL
);

-- Índices -------------------------------------------------------

CREATE INDEX IF NOT EXISTS idx_usuarios_ativo ON usuarios (ativo);
CREATE INDEX IF NOT EXISTS idx_clientes_nome ON clientes (nome);
CREATE INDEX IF NOT EXISTS idx_clientes_email ON clientes (email);
CREATE INDEX IF NOT EXISTS idx_clientes_ativo ON clientes (ativo);
CREATE INDEX IF NOT EXISTS idx_destinos_ativo ON destinos (ativo);
CREATE INDEX IF NOT EXISTS idx_pacotes_destino ON pacotes (destino_id);
CREATE INDEX IF NOT EXISTS idx_pacotes_ativo ON pacotes (ativo);
CREATE INDEX IF NOT EXISTS idx_viagens_data ON viagens (data_partida);
CREATE INDEX IF NOT EXISTS idx_viagens_status ON viagens (status);
CREATE INDEX IF NOT EXISTS idx_reservas_cliente ON reservas (cliente_id);
CREATE INDEX IF NOT EXISTS idx_reservas_viagem ON reservas (viagem_id);
CREATE INDEX IF NOT EXISTS idx_reservas_status ON reservas (status);
CREATE INDEX IF NOT EXISTS idx_reservas_criado ON reservas (criado_em);
CREATE INDEX IF NOT EXISTS idx_pagamentos_reserva ON pagamentos (reserva_id);
CREATE INDEX IF NOT EXISTS idx_pagamentos_status ON pagamentos (status);
CREATE INDEX IF NOT EXISTS idx_pagamentos_vencimento ON pagamentos (data_vencimento);
CREATE INDEX IF NOT EXISTS idx_pagamentos_pagamento ON pagamentos (data_pagamento);
CREATE INDEX IF NOT EXISTS idx_agendamentos_cliente ON agendamentos (cliente_id);
CREATE INDEX IF NOT EXISTS idx_agendamentos_viagem ON agendamentos (viagem_id);
CREATE INDEX IF NOT EXISTS idx_agendamentos_status ON agendamentos (status);

-- "ON UPDATE CURRENT_TIMESTAMP" não existe no SQLite: os gatilhos abaixo
-- mantêm atualizado_em coerente com o comportamento do MySQL.

CREATE TRIGGER IF NOT EXISTS trg_usuarios_atualizado AFTER UPDATE ON usuarios
FOR EACH ROW WHEN NEW.atualizado_em = OLD.atualizado_em
BEGIN
    UPDATE usuarios SET atualizado_em = datetime('now', 'localtime') WHERE id = NEW.id;
END;

CREATE TRIGGER IF NOT EXISTS trg_clientes_atualizado AFTER UPDATE ON clientes
FOR EACH ROW WHEN NEW.atualizado_em = OLD.atualizado_em
BEGIN
    UPDATE clientes SET atualizado_em = datetime('now', 'localtime') WHERE id = NEW.id;
END;

CREATE TRIGGER IF NOT EXISTS trg_destinos_atualizado AFTER UPDATE ON destinos
FOR EACH ROW WHEN NEW.atualizado_em = OLD.atualizado_em
BEGIN
    UPDATE destinos SET atualizado_em = datetime('now', 'localtime') WHERE id = NEW.id;
END;

CREATE TRIGGER IF NOT EXISTS trg_pacotes_atualizado AFTER UPDATE ON pacotes
FOR EACH ROW WHEN NEW.atualizado_em = OLD.atualizado_em
BEGIN
    UPDATE pacotes SET atualizado_em = datetime('now', 'localtime') WHERE id = NEW.id;
END;

CREATE TRIGGER IF NOT EXISTS trg_viagens_atualizado AFTER UPDATE ON viagens
FOR EACH ROW WHEN NEW.atualizado_em = OLD.atualizado_em
BEGIN
    UPDATE viagens SET atualizado_em = datetime('now', 'localtime') WHERE id = NEW.id;
END;

CREATE TRIGGER IF NOT EXISTS trg_reservas_atualizado AFTER UPDATE ON reservas
FOR EACH ROW WHEN NEW.atualizado_em = OLD.atualizado_em
BEGIN
    UPDATE reservas SET atualizado_em = datetime('now', 'localtime') WHERE id = NEW.id;
END;

CREATE TRIGGER IF NOT EXISTS trg_pagamentos_atualizado AFTER UPDATE ON pagamentos
FOR EACH ROW WHEN NEW.atualizado_em = OLD.atualizado_em
BEGIN
    UPDATE pagamentos SET atualizado_em = datetime('now', 'localtime') WHERE id = NEW.id;
END;

CREATE TRIGGER IF NOT EXISTS trg_agendamentos_atualizado AFTER UPDATE ON agendamentos
FOR EACH ROW WHEN NEW.atualizado_em = OLD.atualizado_em
BEGIN
    UPDATE agendamentos SET atualizado_em = datetime('now', 'localtime') WHERE id = NEW.id;
END;
