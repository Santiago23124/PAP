-- ============================================
-- Base de dados completa - Restaurante 100 Sabores
-- Ficheiro unificado: base + schema completo
-- Sem dependência de turnos; inclui todas as tabelas ativas do projecto
-- ============================================

-- Criar base de dados se não existir
CREATE DATABASE IF NOT EXISTS `restaurante_100_sabores` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `restaurante_100_sabores`;

-- ============================================
-- Contactos
-- ============================================
CREATE TABLE IF NOT EXISTS `contactos` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nome` varchar(100) NOT NULL,
    `email` varchar(150) NOT NULL,
    `telefone` varchar(30) DEFAULT NULL,
    `assunto` varchar(50) DEFAULT 'general',
    `mensagem` text NOT NULL,
    `data_envio` datetime NOT NULL,
    `ip` varchar(45) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_data_envio` (`data_envio`),
    KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Reservas (schema principal)
-- ============================================
CREATE TABLE IF NOT EXISTS `reservas` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nome` varchar(100) NOT NULL,
    `email` varchar(150) NOT NULL,
    `telefone` varchar(30) NOT NULL,
    `data_reserva` date NOT NULL,
    `hora_reserva` time NOT NULL,
    `num_pessoas` int(11) NOT NULL,
    `observacoes` text,
    `status` enum('pendente','confirmada','cancelada') DEFAULT 'pendente',
    `mesa_id` int(11) NULL DEFAULT NULL,
    `duracao_minutos` int(11) DEFAULT 120,
    `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
    `atualizada_em` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_data_reserva` (`data_reserva`),
    KEY `idx_status` (`status`),
    KEY `idx_mesa` (`mesa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Vouchers (presente) - pré-pagos, resgatáveis numa reserva
-- ============================================
CREATE TABLE IF NOT EXISTS `vouchers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `codigo` varchar(20) NOT NULL,
    `nome_oferece` varchar(100) NOT NULL,
    `nome_recebe` varchar(100) NOT NULL,
    `email_comprador` varchar(150) NOT NULL,
    `valor` decimal(6,2) NOT NULL,
    `mensagem` text,
    `data_prevista` date NULL DEFAULT NULL,
    `data_validade` date NULL DEFAULT NULL,
    `status` enum('pendente_pagamento','pago','resgatado','expirado','cancelado') DEFAULT 'pendente_pagamento',
    `reserva_id` int(11) NULL DEFAULT NULL,
    `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
    `data_resgate` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_codigo` (`codigo`),
    KEY `idx_status` (`status`),
    KEY `idx_reserva` (`reserva_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Configurações do restaurante
-- ============================================
CREATE TABLE IF NOT EXISTS `configuracoes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `chave` varchar(50) UNIQUE NOT NULL,
    `valor` text NOT NULL,
    `descricao` varchar(200) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `configuracoes` (`chave`, `valor`, `descricao`) VALUES
('email_restaurante', 'restaurante@100sabores.pt', 'Email principal do restaurante'),
('telefone_restaurante', '+351 912 345 678', 'Telefone do restaurante'),
('horario_funcionamento', '12h00-15h00 / 19h00-23h00', 'Horário de funcionamento'),
('capacidade_maxima', '80', 'Capacidade máxima do restaurante'),
('reservas_antecipacao_minima', '2', 'Horas mínimas para reserva antecipada'),
('reservas_antecipacao_maxima', '30', 'Dias máximos para reserva antecipada');

-- ============================================
-- Mesas (sem turnos; capacidade total do restaurante)
-- ============================================
CREATE TABLE IF NOT EXISTS `mesas` (
    `id` int(11) PRIMARY KEY AUTO_INCREMENT,
    `numero` varchar(10) NOT NULL UNIQUE,
    `capacidade` int(11) NOT NULL,
    `localizacao` varchar(50) DEFAULT 'Sala Principal',
    `ativa` boolean DEFAULT TRUE,
    `observacoes` text,
    `criada_em` timestamp DEFAULT CURRENT_TIMESTAMP,
    `atualizada_em` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `mesas` (`numero`, `capacidade`, `localizacao`) VALUES
('1', 2, 'Sala Principal'),
('2', 2, 'Sala Principal'),
('3', 4, 'Sala Principal'),
('4', 4, 'Sala Principal'),
('5', 6, 'Sala Principal'),
('6', 2, 'Terraço'),
('7', 4, 'Terraço'),
('8', 8, 'Sala Privada');

-- ============================================
-- Histórico de mesas (rastreabilidade)
-- ============================================
CREATE TABLE IF NOT EXISTS `mesas_historico` (
    `id` int(11) PRIMARY KEY AUTO_INCREMENT,
    `reserva_id` int(11) NOT NULL,
    `mesa_id` int(11) NOT NULL,
    `data_atribuicao` timestamp DEFAULT CURRENT_TIMESTAMP,
    `motivo` enum('automatica','manual','remanejamento') DEFAULT 'automatica',
    `observacoes` text,
    FOREIGN KEY (reserva_id) REFERENCES `reservas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (mesa_id) REFERENCES `mesas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Funcionários (login / dashboard)
-- ============================================
CREATE TABLE IF NOT EXISTS `funcionarios` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nome_utilizador` varchar(50) UNIQUE NOT NULL,
    `senha` varchar(255) NOT NULL,
    `nome_completo` varchar(100) NOT NULL,
    `cargo` enum('admin','chef','garcom','gerente') NOT NULL,
    `email` varchar(150) DEFAULT NULL,
    `telefone` varchar(20) DEFAULT NULL,
    `ativo` boolean DEFAULT TRUE,
    `ultimo_acesso` timestamp NULL,
    `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `funcionarios` (`nome_utilizador`, `senha`, `nome_completo`, `cargo`, `email`, `telefone`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador do Sistema', 'admin', 'admin@100sabores.pt', '+351 912 345 678'),
('chef', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Chef Executivo', 'chef', 'chef@100sabores.pt', '+351 913 456 789'),
('garcom', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Funcionário de Mesa', 'garcom', 'garcom@100sabores.pt', '+351 914 567 890');

-- ============================================
-- Itens do menu
-- ============================================
CREATE TABLE IF NOT EXISTS `menu_itens` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nome` varchar(100) NOT NULL,
    `descricao` text,
    `preco` decimal(8,2) NOT NULL,
    `categoria` enum('entradas','principais','sobremesas','bebidas') NOT NULL,
    `tipo` enum('normal','especial','vegetariano','vegan') DEFAULT 'normal',
    `disponivel` boolean DEFAULT TRUE,
    `ordem_exibicao` int(11) DEFAULT 0,
    `criado_por` int(11) DEFAULT NULL,
    `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`criado_por`) REFERENCES `funcionarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `menu_itens` (`nome`, `descricao`, `preco`, `categoria`, `tipo`, `criado_por`) VALUES
('Tabua de Entradas Tradicionais', 'Seleção cuidadosa de queijos artesanais, enchidos regionais, presunto de qualidade superior e azeitonas curadas. Acompanhada de compotas caseiras e pão artesanal.', 15.00, 'entradas', 'normal', 1),
('Bruschetta Mediterrânea', 'Torradas artesanais com tomate confitado, manjericão fresco, mozzarella de búfala e azeite virgem extra. Uma explosão de sabores do Mediterrâneo.', 6.50, 'entradas', 'vegetariano', 1),
('Carpaccio de Salmão', 'Finas fatias de salmão fresco marinado em sumo de limão, acompanhado de rúcula, alcaparras e parmesão em lascas. Uma entrada elegante e refrescante.', 12.00, 'entradas', 'normal', 1),
('File Mignon com Risotto', 'File mignon grelhado ao ponto perfeito, servido com risotto cremoso de cogumelos selvagens e queijo parmesão. Acompanhado de legumes salteados e molho de vinho tinto.', 18.50, 'principais', 'especial', 2),
('Salmão Grelhado com Legumes', 'Lombo de salmão atlântico grelhado com ervas aromáticas, servido com legumes da época salteados e molho de mostarda e mel.', 17.00, 'principais', 'normal', 2),
('Tarte de Lima e Gengibre', 'Base crocante de amêndoa com creme de lima fresca e gengibre cristalizado. Acompanhada de chantilly caseiro.', 5.50, 'sobremesas', 'vegetariano', 2),
('Soufflé de Chocolate Belga', 'Soufflé quente de chocolate belga de alta qualidade, servido com sorvete de baunilha e frutos vermelhos.', 7.30, 'sobremesas', 'normal', 2);

-- ============================================
-- Logs de acesso dos funcionários
-- ============================================
CREATE TABLE IF NOT EXISTS `logs_acesso` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `funcionario_id` int(11) DEFAULT NULL,
    `acao` varchar(100) NOT NULL,
    `detalhes` text,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text,
    `data_acesso` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Tokens de recuperação de senha
-- ============================================
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `funcionario_id` int(11) NOT NULL,
    `token` varchar(255) NOT NULL,
    `usado` boolean DEFAULT FALSE,
    `data_expiracao` datetime NOT NULL,
    `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Categorias do menu
-- ============================================
CREATE TABLE IF NOT EXISTS `menu_categorias` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nome` varchar(50) NOT NULL,
    `descricao` text,
    `ordem_exibicao` int(11) DEFAULT 0,
    `ativa` boolean DEFAULT TRUE,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `menu_categorias` (`nome`, `descricao`, `ordem_exibicao`) VALUES
('Entradas', 'Para abrir o apetite e preparar os sentidos', 1),
('Pratos Principais', 'Os nossos chefes criam pratos inspiradores', 2),
('Sobremesas', 'O inevitável final feliz', 3),
('Bebidas', 'Acompanhamentos perfeitos', 4);

-- ============================================
-- Índices para melhor performance
-- ============================================
CREATE INDEX idx_reservas_data ON `reservas`(`data_reserva`);
CREATE INDEX idx_reservas_status ON `reservas`(`status`);
CREATE INDEX idx_reservas_email ON `reservas`(`email`);
CREATE INDEX idx_funcionarios_nome_utilizador ON `funcionarios`(`nome_utilizador`);
CREATE INDEX idx_funcionarios_cargo ON `funcionarios`(`cargo`);
CREATE INDEX idx_menu_itens_categoria ON `menu_itens`(`categoria`);
CREATE INDEX idx_menu_itens_tipo ON `menu_itens`(`tipo`);
CREATE INDEX idx_logs_acesso_funcionario ON `logs_acesso`(`funcionario_id`);
CREATE INDEX idx_logs_acesso_data ON `logs_acesso`(`data_acesso`);
