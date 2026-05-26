-- Base de dados para o Restaurante 100 Sabores
-- Executar no phpMyAdmin ou MySQL CLI

CREATE DATABASE IF NOT EXISTS `100sabores` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `100sabores`;

-- Tabela de contactos
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

-- Dados de exemplo
INSERT INTO `contactos` (`nome`, `email`, `telefone`, `assunto`, `mensagem`, `data_envio`, `ip`) VALUES
('João Silva', 'joao@email.com', '+351 912345678', 'reservation', 'Gostaria de marcar uma mesa para 4 pessoas no próximo fim de semana.', NOW(), '127.0.0.1'),
('Maria Santos', 'maria@email.com', '+351 912345679', 'feedback', 'Excelente serviço e comida maravilhosa!', NOW(), '127.0.0.1');

-- Tabela de reservas (para futuro uso)
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
  `data_criacao` datetime NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_data_reserva` (`data_reserva`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;