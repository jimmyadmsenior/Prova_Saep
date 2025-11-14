DROP DATABASE IF EXISTS saep_db;
CREATE DATABASE saep_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE saep_db;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT
);

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    categoria_id INT,
    unidade_medida VARCHAR(20) NOT NULL,
    quantidade_disponivel INT DEFAULT 0,
    estoque_minimo INT DEFAULT 5,
    preco_unitario DECIMAL(10,2) DEFAULT 0.00,
    data_validade DATE,
    descricao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

CREATE TABLE movimentacoes_estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo_movimentacao ENUM('entrada', 'saida') NOT NULL,
    quantidade INT NOT NULL,
    motivo VARCHAR(200),
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

INSERT INTO usuarios (username, password, nome) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador'),
('peluxo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Peluxo'),
('zakafofo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Zakafofo'),
('maria_silva', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maria Silva'),
('carlos_santos', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos Santos');

INSERT INTO categorias (nome, descricao) VALUES 
('Cimento e Argamassa', 'Cimentos, argamassas e materiais de ligação'),
('Tijolos e Blocos', 'Tijolos cerâmicos, blocos de concreto e vedação'),
('Ferro e Aço', 'Vergalhões, vigas, perfis e estruturas metálicas'),
('Madeira', 'Madeiras, compensados e estruturas de madeira'),
('Telhas e Coberturas', 'Telhas cerâmicas, metálicas e materiais de cobertura'),
('Tubos e Conexões', 'Tubulações hidráulicas, elétricas e conexões'),
('Outros', 'Materiais diversos para construção');

INSERT INTO produtos (nome, categoria_id, unidade_medida, quantidade_disponivel, estoque_minimo, preco_unitario, data_validade, descricao) VALUES 
('Telhado Cerâmico Português', 5, 'Unidade', 150, 20, 2.80, NULL, 'Telha cerâmica modelo português'),
('Viga de Aço H 200mm', 3, 'Metro', 8, 5, 245.50, NULL, 'Viga de aço estrutural H 200mm'),
('Arame Galvanizado 14 BWG', 3, 'Quilograma', 25, 15, 18.90, NULL, 'Rolo de arame galvanizado calibre 14'),
('Tijolo Comum 6 Furos', 2, 'Unidade', 3, 5, 0.85, NULL, 'Tijolo cerâmico comum 9x14x19cm');

INSERT INTO movimentacoes_estoque (produto_id, usuario_id, tipo_movimentacao, quantidade, motivo) VALUES 
(1, 1, 'entrada', 150, 'Estoque inicial - Admin'),
(2, 2, 'entrada', 8, 'Compra de vigas - Peluxo'),
(3, 3, 'entrada', 25, 'Reposição arame - Zakafofo'),
(4, 1, 'entrada', 3, 'Estoque inicial tijolos - Admin'),
(1, 4, 'saida', 20, 'Venda cliente - Maria Silva'),
(2, 5, 'entrada', 5, 'Compra adicional - Carlos Santos'),
(3, 2, 'saida', 10, 'Obra residencial - Peluxo');
