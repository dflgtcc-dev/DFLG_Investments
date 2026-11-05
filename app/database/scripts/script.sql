-- O banco (DB_NAME, em Config.php) é criado e selecionado pelo DatabaseInitializer; em hospedeiro,
-- crie o banco no painel e importe este arquivo no phpMyAdmin com ele selecionado.

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    nickname VARCHAR(25) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefone VARCHAR(11) NULL,
    senha VARCHAR(255) NOT NULL,
    data_nascimento DATE NOT NULL,
    foto VARCHAR(255) NULL,
    status ENUM('ativo', 'bloqueado') NOT NULL DEFAULT 'ativo',
    perfil ENUM('comum', 'administrador') NOT NULL DEFAULT 'comum',
    tema ENUM('claro', 'escuro') NOT NULL DEFAULT 'claro',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS usuario_comum (
    usuario_id INT PRIMARY KEY,
    xp_total INT NOT NULL DEFAULT 0,
    nivel INT NOT NULL DEFAULT 1,
    streak_atual INT NOT NULL DEFAULT 0,
    maior_streak INT NOT NULL DEFAULT 0,
    ultimo_acesso DATE NULL,
    visibilidade_ranking BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS administradores (
    usuario_id INT PRIMARY KEY,
    nivel_permissao INT NOT NULL DEFAULT 2,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    icone VARCHAR(50) NULL,
    tipo ENUM('receita', 'despesa') NOT NULL DEFAULT 'despesa',
    limite_orcamento DECIMAL(10,2) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS metas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome_meta VARCHAR(100) NOT NULL,
    tipo_meta ENUM('economizar', 'comprar', 'investir') NOT NULL DEFAULT 'economizar',
    valor_alvo DECIMAL(10,2) NOT NULL,
    valor_atual DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    data_limite DATE NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS historico_xp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    acao VARCHAR(50) NOT NULL,
    xp INT NOT NULL,
    data_concessao DATE NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS registro_financeiro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    categoria_id INT NULL,
    tipo ENUM('receita', 'despesa') NOT NULL DEFAULT 'despesa',
    valor DECIMAL(10,2) NOT NULL,
    data_registro DATE NOT NULL,
    descricao VARCHAR(255) NULL,
    parcelamento_id INT NULL,
    numero_parcela INT NULL,
    meta_id INT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_registro_parcela (parcelamento_id, numero_parcela),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    FOREIGN KEY (meta_id) REFERENCES metas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS parcelamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    categoria_id INT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    quantidade_parcelas INT NOT NULL,
    parcelas_pagas INT NOT NULL DEFAULT 0,
    data_primeira_parcela DATE NOT NULL,
    tag VARCHAR(50) NULL,
    valor_parcela DECIMAL(10,2) NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS limites_categoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    categoria_id INT NOT NULL,
    valor_limite DECIMAL(10,2) NOT NULL,
    UNIQUE KEY uk_limite_usuario_categoria (usuario_id, categoria_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO usuarios (nome, nickname, email, senha, data_nascimento, perfil)
VALUES ('Administrador', 'admin', 'admin@dflg.com', '$2y$12$Zon6XY2fKd2Ua4YIId4MC.dVB3XxTHDlK96rgDJYEnJRoXxoZ84XS', '2000-01-01', 'administrador');

INSERT IGNORE INTO administradores (usuario_id, nivel_permissao) VALUES (LAST_INSERT_ID(), 1);

-- admin@dflg.com
-- admin123

INSERT IGNORE INTO categorias (nome, icone, tipo) VALUES
('Salário', 'bi-cash-coin', 'receita'),
('Freelance', 'bi-laptop', 'receita'),
('Rendimentos', 'bi-graph-up-arrow', 'receita'),
('Outras receitas', 'bi-plus-circle', 'receita'),
('Alimentação', 'bi-basket', 'despesa'),
('Moradia', 'bi-house-door', 'despesa'),
('Transporte', 'bi-bus-front', 'despesa'),
('Saúde', 'bi-heart-pulse', 'despesa'),
('Educação', 'bi-book', 'despesa'),
('Lazer', 'bi-controller', 'despesa'),
('Compras', 'bi-bag', 'despesa'),
('Contas e assinaturas', 'bi-receipt', 'despesa'),
('Outras despesas', 'bi-dash-circle', 'despesa'),
('Metas', 'bi-bullseye', 'despesa'),
('Resgate de metas', 'bi-box-arrow-in-down', 'receita');


