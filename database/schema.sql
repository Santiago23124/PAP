 -- Base de dados para o Restaurante 100 Sabores
-- Criar esta base de dados no phpMyAdmin do XAMPP
-- Cria a tabela se a mesma não existir
CREATE DATABASE IF NOT EXISTS restaurante_100_sabores;
USE restaurante_100_sabores;

-- Tabela de reservas
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,-- O id sobe sozinho nao e um numero aleatorio
    nome VARCHAR(100) NOT NULL,-- nao pode ser vazio
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    data_reserva DATE NOT NULL,
    hora_reserva TIME NOT NULL,
    num_pessoas INT NOT NULL,
    observacoes TEXT,
    -- Unicos status permitidos
    status ENUM('pendente', 'confirmada', 'cancelada') DEFAULT 'pendente',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de configurações do restaurante
CREATE TABLE IF NOT EXISTS configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(50) UNIQUE NOT NULL,
    valor TEXT NOT NULL,
    descricao VARCHAR(200)
);

-- Inserir configurações padrão
INSERT INTO configuracoes (chave, valor, descricao) VALUES
('email_restaurante', 'restaurante@100sabores.pt', 'Email principal do restaurante'),
('telefone_restaurante', '+351 912 345 678', 'Telefone do restaurante'),
('horario_funcionamento', '12h00-15h00 / 19h00-23h00', 'Horário de funcionamento'),
('capacidade_maxima', '80', 'Capacidade máxima do restaurante'),
('reservas_antecipacao_minima', '2', 'Horas mínimas para reserva antecipada'),
('reservas_antecipacao_maxima', '30', 'Dias máximos para reserva antecipada');

# 1. Limpar tabelas que podem estar com nomes de colunas errados
DROP TABLE IF EXISTS reservas_mesas;
DROP TABLE IF EXISTS mesas;

# 2. Criar a tabela mesas com a coluna 'numero' (como o teu PHP novo pede)
CREATE TABLE mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(10) NOT NULL UNIQUE,
    capacidade INT NOT NULL,
    localizacao VARCHAR(50),
    ativa BOOLEAN DEFAULT TRUE
);

# 3. Inserir os dados (usando o formato que o teu sistema reconhece)
INSERT INTO mesas (numero, capacidade, localizacao) VALUES
('1', 2, 'Sala Principal'),
('2', 2, 'Sala Principal'),
('3', 4, 'Sala Principal'),
('4', 4, 'Sala Principal'),
('5', 6, 'Sala Principal'),
('6', 2, 'Terraço'),
('7', 4, 'Terraço'),
('8', 8, 'Sala Privada');

# 4. Criar a tabela de ligação para o sistema automático
CREATE TABLE reservas_mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT,
    mesa_id INT,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (mesa_id) REFERENCES mesas(id)
);
-- Tabela de funcionários
CREATE TABLE IF NOT EXISTS funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_utilizador VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL, -- senha longa por ser ecriptografada
    nome_completo VARCHAR(100) NOT NULL,
    cargo ENUM('admin', 'chef', 'garcom', 'gerente') NOT NULL,-- ENUM para limitar os valores possíveis
    email VARCHAR(150),
    telefone VARCHAR(20),
    ativo BOOLEAN DEFAULT TRUE,
    ultimo_acesso TIMESTAMP NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir funcionários padrão
INSERT INTO funcionarios (nome_utilizador, senha, nome_completo, cargo, email, telefone) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador do Sistema', 'admin', 'admin@100sabores.pt', '+351 912 345 678'),
('chef', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Chef Executivo', 'chef', 'chef@100sabores.pt', '+351 913 456 789'),
('garcom', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Funcionário de Mesa', 'garcom', 'garcom@100sabores.pt', '+351 914 567 890');

-- Tabela de itens do menu
CREATE TABLE IF NOT EXISTS menu_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(8,2) NOT NULL,
    categoria ENUM('entradas', 'principais', 'sobremesas', 'bebidas') NOT NULL,
    tipo ENUM('normal', 'especial', 'vegetariano', 'vegan') DEFAULT 'normal',
    disponivel BOOLEAN DEFAULT TRUE,
    ordem_exibicao INT DEFAULT 0,
    criado_por INT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (criado_por) REFERENCES funcionarios(id)
);

-- Inserir alguns itens do menu de exemplo
INSERT INTO menu_itens (nome, descricao, preco, categoria, tipo, criado_por) VALUES
('Tabua de Entradas Tradicionais', 'Seleção cuidadosa de queijos artesanais, enchidos regionais, presunto de qualidade superior e azeitonas curadas. Acompanhada de compotas caseiras e pão artesanal.', 15.00, 'entradas', 'normal', 1),
('Bruschetta Mediterrânea', 'Torradas artesanais com tomate confitado, manjericão fresco, mozzarella de búfala e azeite virgem extra. Uma explosão de sabores do Mediterrâneo.', 6.50, 'entradas', 'vegetariano', 1),
('Carpaccio de Salmão', 'Finas fatias de salmão fresco marinado em sumo de limão, acompanhado de rúcula, alcaparras e parmesão em lascas. Uma entrada elegante e refrescante.', 12.00, 'entradas', 'normal', 1),
('File Mignon com Risotto', 'File mignon grelhado ao ponto perfeito, servido com risotto cremoso de cogumelos selvagens e queijo parmesão. Acompanhado de legumes salteados e molho de vinho tinto.', 18.50, 'principais', 'especial', 2),
('Salmão Grelhado com Legumes', 'Lombo de salmão atlântico grelhado com ervas aromáticas, servido com legumes da época salteados e molho de mostarda e mel. Uma opção saudável e saborosa.', 17.00, 'principais', 'normal', 2),
('Tarte de Lima e Gengibre', 'Base crocante de amêndoa com creme de lima fresca e gengibre cristalizado. Acompanhada de chantilly caseiro e raspas de lima. Uma sobremesa refrescante e elegante.', 5.50, 'sobremesas', 'vegetariano', 2),
('Soufflé de Chocolate Belga', 'Soufflé quente de chocolate belga de alta qualidade, servido com sorvete de baunilha e frutos vermelhos. Uma experiência decadente e memorável.', 7.30, 'sobremesas', 'normal', 2);

-- Tabela de logs de acesso dos funcionários
CREATE TABLE IF NOT EXISTS logs_acesso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    funcionario_id INT,
    acao VARCHAR(100) NOT NULL,
    detalhes TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    data_acesso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id)
);

-- Tabela de tokens de recuperação de senha
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    funcionario_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    data_expiracao DATETIME NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id)
);

-- Tabela de categorias do menu (para organização)
CREATE TABLE IF NOT EXISTS menu_categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    descricao TEXT,
    ordem_exibicao INT DEFAULT 0,
    ativa BOOLEAN DEFAULT TRUE
);

-- Inserir categorias padrão
INSERT INTO menu_categorias (nome, descricao, ordem_exibicao) VALUES
('Entradas', 'Para abrir o apetite e preparar os sentidos', 1),
('Pratos Principais', 'Os nossos chefes criam pratos inspiradores', 2),
('Sobremesas', 'O inevitável final feliz', 3),
('Bebidas', 'Acompanhamentos perfeitos', 4);

-- Facilita a leitura de dados por parte da base de dados
-- Índices para melhor performance
CREATE INDEX idx_reservas_data ON reservas(data_reserva);
CREATE INDEX idx_reservas_status ON reservas(status);
CREATE INDEX idx_reservas_email ON reservas(email);
CREATE INDEX idx_funcionarios_nome_utilizador ON funcionarios(nome_utilizador);
CREATE INDEX idx_funcionarios_cargo ON funcionarios(cargo);
CREATE INDEX idx_menu_itens_categoria ON menu_itens(categoria);
CREATE INDEX idx_menu_itens_tipo ON menu_itens(tipo);
CREATE INDEX idx_logs_acesso_funcionario ON logs_acesso(funcionario_id);
CREATE INDEX idx_logs_acesso_data ON logs_acesso(data_acesso);
