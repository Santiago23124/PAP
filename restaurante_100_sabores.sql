-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 28-Ago-2026 às 22:39
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `restaurante_100_sabores`
--
CREATE DATABASE IF NOT EXISTS `restaurante_100_sabores` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `restaurante_100_sabores`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `configuracoes`
--

CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL,
  `chave` varchar(50) NOT NULL,
  `valor` text NOT NULL,
  `descricao` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `configuracoes`
--

INSERT INTO `configuracoes` (`id`, `chave`, `valor`, `descricao`) VALUES
(1, 'email_restaurante', 'restaurante@100sabores.pt', 'Email principal do restaurante'),
(2, 'telefone_restaurante', '+351 912 345 678', 'Telefone do restaurante'),
(3, 'horario_funcionamento', '12h00-15h00 / 19h00-23h00', 'Horário de funcionamento'),
(4, 'capacidade_maxima', '80', 'Capacidade máxima do restaurante'),
(5, 'reservas_antecipacao_minima', '2', 'Horas mínimas para reserva antecipada'),
(6, 'reservas_antecipacao_maxima', '30', 'Dias máximos para reserva antecipada');

-- --------------------------------------------------------

--
-- Estrutura da tabela `contactos`
--

CREATE TABLE `contactos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `assunto` varchar(50) DEFAULT 'general',
  `mensagem` text NOT NULL,
  `data_envio` datetime NOT NULL,
  `ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `contactos`
--

INSERT INTO `contactos` (`id`, `nome`, `email`, `telefone`, `assunto`, `mensagem`, `data_envio`, `ip`) VALUES
(1, 'Mariana Pereira', 'sandomingos45@gmail.com', '968387773', 'feedback', 'Muito aconchegante', '2026-06-25 14:23:19', '::1'),
(2, 'Joana Fonseca', 'sandomingos45@gmail.com', '968387773', 'reservation', 'cxzcxczxc', '2026-07-05 22:36:56', '::1'),
(3, 'Luís Cabral', 'luismccabral12@gmail.com', '', 'complaint', 'Não gostei', '2026-07-27 15:40:33', '::1'),
(4, 'Monitor lg', 'sandomingos45@gmail.com', '968387773', 'reservation', 'saf', '2026-08-02 23:42:52', '::1');

-- --------------------------------------------------------

--
-- Estrutura da tabela `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `mensagem` text NOT NULL,
  `data_hora` datetime NOT NULL,
  `telefone` varchar(30) NOT NULL,
  `email` varchar(150) NOT NULL,
  `num_pessoas` int(11) NOT NULL,
  `tipo_de_evento` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `funcionarios`
--

CREATE TABLE `funcionarios` (
  `id` int(11) NOT NULL,
  `nome_utilizador` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nome_completo` varchar(100) NOT NULL,
  `cargo` enum('admin','chef','garcom','gerente') NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `ultimo_acesso` timestamp NULL DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `funcionarios`
--

INSERT INTO `funcionarios` (`id`, `nome_utilizador`, `senha`, `nome_completo`, `cargo`, `email`, `telefone`, `ativo`, `ultimo_acesso`, `data_criacao`, `data_atualizacao`) VALUES
(1, 'admin', '$2y$10$TdzUNDUNJZuliS0kKzziAuIAUewNypLP2G59tsuzPLqa9RXUE2Vp.', 'Administrador do Sistema', 'admin', 'admin@100sabores.pt', '+351 912 345 678', 1, '2026-08-19 18:35:21', '2026-06-10 16:58:44', '2026-08-19 18:35:21'),
(2, 'chef', '$2y$10$c99mfxYr3pd6lwo4AnXq9.mUq1490yub4Mxxh7czlEXsqNBzoy9du', 'Chef Executivo', 'chef', 'chef@100sabores.pt', '+351 913 456 789', 1, '2026-07-27 14:49:35', '2026-06-10 16:58:44', '2026-07-27 14:49:35'),
(3, 'garcom', '$2y$10$Q.5/xQnsJS5CeBmAGu4TSOS9paGsaIZmc1V/w8Mnn7jbfkl3dAhYy', 'Funcionário de Mesa', 'garcom', 'garcom@100sabores.pt', '+351 914 567 890', 1, '2026-06-25 13:17:48', '2026-06-10 16:58:44', '2026-06-25 13:17:48');

-- --------------------------------------------------------

--
-- Estrutura da tabela `logs_acesso`
--

CREATE TABLE `logs_acesso` (
  `id` int(11) NOT NULL,
  `funcionario_id` int(11) DEFAULT NULL,
  `acao` varchar(100) NOT NULL,
  `detalhes` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `data_acesso` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `logs_acesso`
--

INSERT INTO `logs_acesso` (`id`, `funcionario_id`, `acao`, `detalhes`, `ip_address`, `user_agent`, `data_acesso`) VALUES
(1, NULL, 'login_falha', 'Tentativa de login falhada para: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-10 17:02:29'),
(2, NULL, 'login_falha', 'Tentativa de login falhada para: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-10 17:05:54'),
(3, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-10 17:21:05'),
(4, NULL, 'login_falha', 'Tentativa de login falhada para: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 OPR/131.0.0.0', '2026-06-10 17:30:04'),
(5, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 OPR/131.0.0.0', '2026-06-10 17:30:13'),
(6, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 OPR/131.0.0.0', '2026-06-10 17:51:28'),
(7, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 OPR/131.0.0.0', '2026-06-10 17:54:21'),
(8, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-10 18:00:23'),
(9, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-10 18:05:52'),
(10, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-10 18:26:16'),
(11, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-10 18:48:21'),
(12, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-10 18:51:34'),
(13, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-11 21:19:20'),
(14, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-12 23:41:58'),
(15, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 21:01:06'),
(16, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 21:20:43'),
(17, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-15 19:22:09'),
(18, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-15 22:41:35'),
(19, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-15 23:01:12'),
(20, NULL, 'login_falha', 'Tentativa de login falhada para: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-15 23:01:32'),
(21, 2, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-15 23:01:54'),
(22, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 11:39:13'),
(23, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 12:14:51'),
(24, 2, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 12:48:59'),
(25, 3, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 12:49:42'),
(26, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 13:06:42'),
(27, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 13:13:15'),
(28, 3, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 13:13:34'),
(29, 2, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 13:13:56'),
(30, 3, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 22:14:39'),
(31, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 22:20:37'),
(32, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 21:11:49'),
(33, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-19 11:49:24'),
(34, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-19 13:31:29'),
(35, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-19 19:16:00'),
(36, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 10:09:56'),
(37, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 11:51:39'),
(38, 3, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 11:59:40'),
(39, 3, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 14:21:29'),
(40, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 14:22:14'),
(41, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 21:13:04'),
(42, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 00:29:27'),
(43, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 10:49:27'),
(44, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:39:40'),
(45, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 01:22:09'),
(46, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 01:59:03'),
(47, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 15:28:16'),
(48, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 15:31:58'),
(49, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 18:16:44'),
(50, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 21:58:58'),
(51, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 22:47:04'),
(52, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 22:47:23'),
(53, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 23:35:56'),
(54, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 13:11:35'),
(55, 3, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 13:17:48'),
(56, 2, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 13:26:04'),
(57, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 19:08:09'),
(58, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 19:15:10'),
(59, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-26 01:06:41'),
(60, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-26 01:19:09'),
(61, 1, 'login', 'Login bem-sucedido', '192.168.1.221', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', '2026-06-28 21:39:45'),
(62, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-28 22:07:01'),
(63, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-29 16:56:24'),
(64, 2, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-29 16:58:47'),
(65, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-29 21:19:51'),
(66, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-29 21:36:35'),
(67, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-29 21:51:45'),
(68, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-29 22:03:37'),
(69, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-30 17:07:08'),
(70, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-30 18:29:27'),
(71, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-30 22:33:48'),
(72, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-30 23:41:38'),
(73, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-01 21:16:20'),
(74, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 16:46:44'),
(75, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-03 02:36:27'),
(76, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-04 10:38:38'),
(77, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-04 11:52:29'),
(78, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-04 12:01:40'),
(79, NULL, 'login_falha', 'Tentativa de login falhada para: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-04 13:20:04'),
(80, NULL, 'login_falha', 'Tentativa de login falhada para: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-04 13:20:06'),
(81, NULL, 'login_falha', 'Tentativa de login falhada para: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-04 13:20:06'),
(82, NULL, 'login_falha', 'Tentativa de login falhada para: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-04 13:20:06'),
(83, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-04 13:20:13'),
(84, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-04 21:09:09'),
(85, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-04 22:28:26'),
(86, NULL, 'login_falha', 'Tentativa de login falhada para: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-04 22:56:08'),
(87, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-04 22:56:20'),
(88, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 00:08:07'),
(89, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 19:02:38'),
(90, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 21:22:03'),
(91, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 21:28:21'),
(92, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 21:46:15'),
(93, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 22:18:54'),
(94, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 22:52:10'),
(95, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-06 00:04:03'),
(96, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-06 00:09:37'),
(97, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-27 14:43:10'),
(98, 2, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-27 14:49:35'),
(99, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-27 14:51:20'),
(100, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0', '2026-08-02 22:43:20'),
(101, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0', '2026-08-03 20:55:56'),
(102, 1, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 OPR/134.0.0.0', '2026-08-19 18:35:21');

-- --------------------------------------------------------

--
-- Estrutura da tabela `menu_categorias`
--

CREATE TABLE `menu_categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ordem_exibicao` int(11) DEFAULT 0,
  `ativa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `menu_categorias`
--

INSERT INTO `menu_categorias` (`id`, `nome`, `descricao`, `ordem_exibicao`, `ativa`) VALUES
(1, 'Entradas', 'Para abrir o apetite e preparar os sentidos', 1, 1),
(2, 'Pratos Principais', 'Os nossos chefes criam pratos inspiradores', 2, 1),
(3, 'Sobremesas', 'O inevitável final feliz', 3, 1),
(4, 'Bebidas', 'Acompanhamentos perfeitos', 4, 1),
(5, 'Entradas', 'Para abrir o apetite e preparar os sentidos', 1, 1),
(6, 'Pratos Principais', 'Os nossos chefes criam pratos inspiradores', 2, 1),
(7, 'Sobremesas', 'O inevitável final feliz', 3, 1),
(8, 'Bebidas', 'Acompanhamentos perfeitos', 4, 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `menu_itens`
--

CREATE TABLE `menu_itens` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(8,2) NOT NULL,
  `categoria` enum('entradas','principais','sobremesas','bebidas') NOT NULL,
  `tipo` enum('normal','especial','vegetariano','vegan') DEFAULT 'normal',
  `disponivel` tinyint(1) DEFAULT 1,
  `ordem_exibicao` int(11) DEFAULT 0,
  `criado_por` int(11) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `imagem` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `menu_itens`
--

INSERT INTO `menu_itens` (`id`, `nome`, `descricao`, `preco`, `categoria`, `tipo`, `disponivel`, `ordem_exibicao`, `criado_por`, `data_criacao`, `data_atualizacao`, `imagem`) VALUES
(4, 'File Mignon com Risotto', 'File mignon grelhado ao ponto perfeito, servido com risotto cremoso de cogumelos selvagens e queijo parmesão. Acompanhado de legumes salteados e molho de vinho tinto.', 18.50, 'principais', 'especial', 1, 0, 2, '2026-06-10 16:58:44', '2026-06-29 21:59:16', '../imagens/menu/1782770356_PFGUXLY6OVIH3DN7ZSKX37ZZCI.jpg'),
(6, 'Tarte de Lima e Gengibre', 'Base crocante de amêndoa com creme de lima fresca e gengibre cristalizado. Acompanhada de chantilly caseiro.', 5.50, 'sobremesas', 'vegetariano', 1, 0, 2, '2026-06-10 16:58:44', '2026-06-29 22:12:38', '../imagens/menu/1782771158_688925311_925646650465980_7737773668096411544_n.jpg'),
(15, 'Cogumelos Recheados', 'Cogumelos salteados com alho, ervas frescas e gratinados com queijo parmesão.', 6.00, 'principais', 'normal', 1, 0, 1, '2026-06-10 18:01:27', '2026-07-05 21:40:53', '../imagens/menu/1782771236_474649183_560112790352703_1414298278102554252_n.jpg'),
(16, 'Risotto de Aspargos', 'Arroz arbóreo cozinhado lentamente com aspargos frescos, vinho branco e lascas de queijo.', 12.00, 'principais', 'normal', 1, 0, 1, '2026-06-10 18:01:43', '2026-06-30 18:29:56', '../imagens/menu/1782844196_481333379_584203884610260_942746284068288699_n.jpg'),
(17, 'Hambúrguer Gourmet', 'Carne de novilho (200g), queijo cheddar maturado, bacon crocante e molho secreto no pão brioche.', 18.00, 'principais', 'especial', 1, 0, 1, '2026-06-10 18:01:57', '2026-06-29 22:18:42', '../imagens/menu/1782771522_384771961_250290411334944_1895574956038820247_n.jpg'),
(19, 'Cheesecake de Frutos Vermelhos', 'Base crocante de bolacha com creme de queijo suave e cobertura de frutos silvestres.', 4.00, 'sobremesas', 'especial', 1, 0, 1, '2026-06-10 18:02:43', '2026-06-29 22:19:12', '../imagens/menu/1782771552_480261285_577926898571292_1477930436272135903_n.jpg'),
(26, 'Cocktail de frutos vermelhos', 'Um cocktail vibrante e refrescante que celebra a doçura natural do verão. Esta criação combina o carácter intenso de morangos maduros, framboesas e amoras frescas, equilibrado com um toque cítrico refrescante e uma base aveludada', 10.00, 'bebidas', 'normal', 1, 0, 1, '2026-07-05 19:05:06', '2026-07-05 19:05:06', '../imagens/menu/1783278306_480684221_584204044610244_6719021584371952150_n.jpg'),
(27, 'Sangria de frutos de vermelhos', 'Uma interpretação sofisticada e vibrante da clássica sangria, onde a frescura encontra a intensidade. Esta mistura é uma explosão de cor e sabor, combinando um vinho (tinto, branco ou espumante) de excelente qualidade com uma seleção generosa de frutos silvestres  morangos suculentos, framboesas, amoras e mirtilos.', 12.00, 'bebidas', 'normal', 1, 0, 1, '2026-07-05 19:08:10', '2026-07-05 19:08:10', '../imagens/menu/1783278489_557337477_751799457850701_4943235369354937777_n.jpg'),
(28, 'Banoffee', 'Uma celebração de texturas e sabores em cada camada. O nosso Banoffee equilibra perfeitamente uma base crocante de bolacha amanteigada com um recheio generoso de doce de leite cremoso e rodelas de banana fresca.', 9.50, 'sobremesas', 'especial', 1, 0, 1, '2026-07-05 19:11:57', '2026-07-05 19:11:57', '../imagens/menu/1783278717_487990283_609186988778616_243818628928539820_n.jpg'),
(29, 'Cocktail de Maracuja', 'Uma verdadeira explosão de energia tropical que desperta os sentidos. Este cocktail destaca o sabor vibrante e intensamente aromático do maracujá fresco, equilibrado na perfeição por um toque cítrico revigorante.', 9.50, 'bebidas', 'normal', 1, 0, 1, '2026-07-05 19:15:53', '2026-07-05 19:15:53', '../imagens/menu/1783278953_500653525_2072390446580911_5756126478478582894_n.jpg'),
(30, 'Peixinhos da horta', 'Feijão-verde fresco em polme artesanal, frito até atingir uma textura perfeitamente estaladiça e leve. Uma interpretação refinada do clássico tradicional', 12.25, 'entradas', 'normal', 1, 0, 1, '2026-07-05 19:21:49', '2026-07-05 19:22:23', '../imagens/menu/1783279343_504625729_659403830423598_3110275443302896683_n.jpg'),
(31, 'MAD-TEA-PARTY', 'Um cocktail elegante, servido \"up\" numa taça coupe, destacando-se pela sua clareza e tonalidade dourada-pálida, evocando um ambiente sofisticado e misterioso.', 15.00, 'bebidas', 'normal', 1, 0, 1, '2026-07-05 19:31:29', '2026-07-05 19:31:29', '../imagens/menu/1783279889_MAD-TEA-PARTY-CHA-DE-DOIDOS-2.jpg'),
(32, 'Cascas de batata', 'em disse que o que não se vê não tem valor? Transformamos as cascas das nossas batatas em chips irresistíveis. Mantemos o sabor autêntico e a crocância ideal, garantindo que aproveitamos o produto na sua totalidade. Delicioso para ti, gentil para o planeta.', 7.50, 'entradas', 'normal', 0, 0, 1, '2026-07-05 21:23:23', '2026-07-27 14:50:37', '../imagens/menu/1783286603_cascas-de-batatas-14.jpg'),
(33, 'Camarão empanado', 'Camarão suculento por dentro com uma crosta dourada irresistivelmente estaladiça.', 5.40, 'entradas', 'especial', 1, 0, 1, '2026-07-05 21:25:11', '2026-08-19 18:35:43', '../imagens/menu/1783286711_721601964_955916137439031_1878328483497038458_n.jpg'),
(34, 'Trouxinhas com queijo chevre', 'Delicadas trouxinhas de queijo chèvre envoltas em massa filo estaladiça, douradas ao forno para um contraste de texturas perfeito. Finalizadas com um fio de mel que acentua a cremosidade e o sabor característico do queijo, criando uma entrada refinada e irresistível.', 7.50, 'entradas', 'normal', 1, 0, 1, '2026-07-05 21:31:51', '2026-07-05 21:39:17', '../imagens/menu/1783287111_710555452_948915724805739_5388363142251724664_n.jpg'),
(35, 'Tábua de entradas', 'rrrrew', 13.50, 'entradas', 'normal', 1, 0, 1, '2026-07-05 22:23:28', '2026-07-06 00:10:09', '../imagens/menu/1783290208_611673372_828838550146791_8838110289176191576_n.jpg'),
(36, 'Cogumelos Recheados', 'rwer', 13.00, 'entradas', 'normal', 1, 0, 1, '2026-07-05 22:55:04', '2026-07-05 22:55:04', '../imagens/menu/1783292104_688925311_925646650465980_7737773668096411544_n.jpg'),
(37, 'Tagliatelli de Choco', 'Tagliatelli com tinta de choco, acompanhado com choco frito e camarão salteado.', 12.50, 'principais', 'especial', 1, 0, 1, '2026-07-27 14:48:35', '2026-07-27 14:48:35', '../imagens/menu/1785163715_IMG_20200928_140600.jpg');

-- --------------------------------------------------------

--
-- Estrutura da tabela `mesas`
--

CREATE TABLE `mesas` (
  `id` int(11) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `capacidade` int(11) NOT NULL,
  `localizacao` varchar(50) DEFAULT 'Sala Principal',
  `ativa` tinyint(1) DEFAULT 1,
  `observacoes` text DEFAULT NULL,
  `criada_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizada_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `mesas`
--

INSERT INTO `mesas` (`id`, `numero`, `capacidade`, `localizacao`, `ativa`, `observacoes`, `criada_em`, `atualizada_em`) VALUES
(1, '1', 2, 'Sala Principal', 1, NULL, '2026-06-10 16:58:44', '2026-06-10 16:58:44'),
(2, '2', 2, 'Sala Principal', 1, NULL, '2026-06-10 16:58:44', '2026-06-10 16:58:44'),
(3, '3', 4, 'Sala Principal', 1, NULL, '2026-06-10 16:58:44', '2026-06-10 16:58:44'),
(4, '4', 4, 'Sala Principal', 1, NULL, '2026-06-10 16:58:44', '2026-06-10 16:58:44'),
(5, '5', 6, 'Sala Principal', 1, NULL, '2026-06-10 16:58:44', '2026-06-10 16:58:44'),
(6, '6', 2, 'Terraço', 1, NULL, '2026-06-10 16:58:44', '2026-06-10 16:58:44'),
(7, '7', 4, 'Terraço', 1, NULL, '2026-06-10 16:58:44', '2026-06-10 16:58:44'),
(8, '8', 8, 'Sala Privada', 1, NULL, '2026-06-10 16:58:44', '2026-06-10 16:58:44'),
(10, '9', 2, 'Terraço', 1, NULL, '2026-06-30 17:56:30', '2026-06-30 17:56:30'),
(11, '10', 2, 'Sala Principal', 1, NULL, '2026-06-30 17:56:30', '2026-06-30 17:56:30'),
(12, '11', 4, 'Sala Principal', 1, NULL, '2026-06-30 17:56:30', '2026-06-30 17:56:30'),
(13, '12', 4, 'Sala Principal', 1, NULL, '2026-06-30 17:56:30', '2026-06-30 17:56:30'),
(14, '13', 4, 'Terraço', 1, NULL, '2026-06-30 17:56:30', '2026-06-30 17:56:30'),
(15, '14', 4, 'Terraço', 1, NULL, '2026-06-30 17:56:30', '2026-06-30 17:56:30'),
(16, '15', 6, 'Sala Principal', 1, NULL, '2026-06-30 17:56:30', '2026-06-30 17:56:30'),
(17, '16', 6, 'Sala Privada', 1, NULL, '2026-06-30 17:56:30', '2026-06-30 17:56:30'),
(18, '17', 8, 'Sala Privada', 1, NULL, '2026-06-30 17:56:30', '2026-06-30 17:56:30'),
(19, '18', 8, 'Sala Privada', 1, NULL, '2026-06-30 17:56:30', '2026-06-30 17:56:30');

-- --------------------------------------------------------

--
-- Estrutura da tabela `mesas_historico`
--

CREATE TABLE `mesas_historico` (
  `id` int(11) NOT NULL,
  `reserva_id` int(11) NOT NULL,
  `mesa_id` int(11) NOT NULL,
  `data_atribuicao` timestamp NOT NULL DEFAULT current_timestamp(),
  `motivo` enum('automatica','manual','remanejamento') DEFAULT 'automatica',
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `mesas_historico`
--

INSERT INTO `mesas_historico` (`id`, `reserva_id`, `mesa_id`, `data_atribuicao`, `motivo`, `observacoes`) VALUES
(15, 15, 5, '2026-06-23 01:59:54', 'automatica', NULL),
(16, 17, 8, '2026-06-23 02:00:44', 'automatica', NULL),
(17, 18, 5, '2026-06-23 02:01:18', 'automatica', NULL),
(19, 20, 8, '2026-06-23 02:02:10', 'automatica', NULL),
(20, 21, 7, '2026-06-25 00:30:48', 'automatica', NULL),
(23, 15, 5, '2026-06-25 13:15:45', 'remanejamento', 'Mesa liberada por cancelamento'),
(25, 18, 5, '2026-06-26 01:16:50', 'remanejamento', 'Mesa liberada por cancelamento'),
(27, 26, 6, '2026-06-27 15:20:32', 'automatica', NULL),
(29, 27, 19, '2026-07-03 03:25:31', 'automatica', NULL),
(30, 28, 3, '2026-07-04 10:36:36', 'automatica', NULL),
(32, 29, 2, '2026-07-04 22:27:10', 'automatica', NULL),
(34, 30, 14, '2026-07-04 22:55:07', 'automatica', NULL),
(35, 32, 11, '2026-07-05 00:09:15', 'automatica', NULL),
(36, 33, 4, '2026-07-05 00:10:49', 'automatica', NULL),
(38, 34, 5, '2026-07-05 22:48:58', 'automatica', NULL),
(39, 35, 10, '2026-07-27 14:41:55', 'automatica', NULL),
(40, 36, 16, '2026-08-02 22:50:08', 'automatica', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `usado` tinyint(1) DEFAULT 0,
  `data_expiracao` datetime NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(30) NOT NULL,
  `data_reserva` date NOT NULL,
  `hora_reserva` time NOT NULL,
  `num_pessoas` int(11) NOT NULL,
  `observacoes` text DEFAULT NULL,
  `status` enum('pendente','confirmada','cancelada') DEFAULT 'pendente',
  `mesa_id` int(11) DEFAULT NULL,
  `duracao_minutos` int(11) DEFAULT 120,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizada_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `reservas`
--

INSERT INTO `reservas` (`id`, `nome`, `email`, `telefone`, `data_reserva`, `hora_reserva`, `num_pessoas`, `observacoes`, `status`, `mesa_id`, `duracao_minutos`, `data_criacao`, `atualizada_em`) VALUES
(15, 'Tiago Miguel', 'joao@gmail.com', '968387773', '2026-06-26', '19:30:00', 6, '', 'cancelada', NULL, 120, '2026-06-23 01:59:54', '2026-07-05 22:53:36'),
(17, 'Ines', 'joao@gmail.com', '968387773', '2026-06-30', '19:30:00', 8, '', 'confirmada', 18, 120, '2026-06-23 02:00:44', '2026-07-05 22:53:34'),
(18, 'Miguel Andrade', 'manel@gmail.com', '968387773', '2026-07-02', '21:30:00', 5, '', 'cancelada', 5, 120, '2026-06-23 02:01:18', '2026-08-02 22:43:37'),
(20, 'Martim', 'joao@gmail.com', '968387773', '2026-06-27', '20:00:00', 8, '', 'pendente', 18, 120, '2026-06-23 02:02:10', '2026-07-05 22:29:37'),
(21, 'Salvador', 'sandomingos45@gmail.com', '968387773', '2026-06-26', '21:00:00', 4, '', 'confirmada', 13, 120, '2026-06-25 00:30:48', '2026-07-05 22:32:01'),
(23, 'Pedro Martins', 'sandomingos45@gmail.com', '968387773', '2026-07-04', '21:30:00', 12, '', 'confirmada', NULL, 120, '2026-06-25 00:34:46', '2026-06-26 01:16:41'),
(25, 'Aurora Pereira Silva', 'sandomingos45@gmail.com', '968385505', '2026-06-26', '21:00:00', 12, '', 'confirmada', NULL, 120, '2026-06-25 13:21:35', '2026-07-05 22:53:48'),
(26, 'João Fonseca Pando', 'Spmariana359@gmail.com', '912855544', '2026-06-28', '20:00:00', 2, 'Intolerante a lactose', 'pendente', 11, 120, '2026-06-27 15:20:32', '2026-07-05 22:31:13'),
(27, 'Joao', 'joao@gmail.com', '968387773', '2026-07-04', '22:00:00', 8, '', 'confirmada', 19, 210, '2026-07-03 03:25:31', '2026-07-03 03:25:55'),
(28, 'Joao Silva', 'sandomingos45@gmail.com', '968387773', '2026-07-05', '21:00:00', 4, '', 'pendente', 3, 210, '2026-07-04 10:36:36', '2026-07-04 10:36:36'),
(29, 'Mariana Pereira', 'sandomingos45@gmail.com', '968387773', '2026-07-06', '19:30:00', 2, '', 'pendente', 2, 210, '2026-07-04 22:27:10', '2026-07-04 22:27:10'),
(30, 'Francisco Branco', 'sandomingos45@gmail.com', '968387773', '2026-07-11', '18:30:00', 4, '', 'pendente', 13, 210, '2026-07-04 22:55:07', '2026-07-05 22:32:26'),
(31, 'Micael Girão', 'sandomingos45@gmail.com', '968387773', '2026-07-18', '20:30:00', 12, '', 'pendente', NULL, 210, '2026-07-05 00:07:54', '2026-07-05 22:30:45'),
(32, 'Carlos manuel', 'sandomingos45@gmail.com', '968387773', '2026-07-11', '19:30:00', 2, '', 'confirmada', 11, 210, '2026-07-05 00:09:15', '2026-07-27 14:49:06'),
(33, 'Enzo Silva', 'joao@gmail.com', '968387773', '2026-07-30', '21:30:00', 3, '', 'pendente', 14, 210, '2026-07-05 00:10:49', '2026-07-05 22:30:16'),
(34, 'Joao Fonseca', 'sandomingos45@gmail.com', '968387773', '2026-07-07', '13:00:00', 6, '', 'pendente', 5, 210, '2026-07-05 22:48:58', '2026-07-05 22:48:58'),
(35, 'Luís Cabral', 'luismccabral12@gmail.com', '912526137', '2026-07-29', '20:00:00', 2, '', 'pendente', 10, 210, '2026-07-27 14:41:55', '2026-07-27 14:41:55'),
(36, 'joao', 'sandomingos45@gmail.com', '968387773', '2026-08-11', '20:00:00', 6, '', 'pendente', 16, 210, '2026-08-02 22:50:08', '2026-08-02 22:50:08');

-- --------------------------------------------------------

--
-- Estrutura da tabela `traducoes`
--

CREATE TABLE `traducoes` (
  `hash` char(32) NOT NULL,
  `idioma` varchar(5) NOT NULL,
  `origem` text NOT NULL,
  `traducao` text NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `traducoes`
--

INSERT INTO `traducoes` (`hash`, `idioma`, `origem`, `traducao`, `criado_em`) VALUES
('00537519d062a0cf95254900c5d9bcc3', 'en', 'O Espaço Ideal para o Seu Grupo', 'The Ideal Space for Your Group', '2026-07-04 16:50:35'),
('007c4f8798ef5660b36cda79ff7dcd01', 'en', '\"Celebrámos o aniversário de casamento dos meus pais no 100 Sabores e a experiência foi memorável. A equipa foi fantástica na organização da ementa personalizada, a comida estava requintada e o serviço impecável. Todos os convidados adoraram a elegância do espaço.\"', '\" We celebrated my parents\' wedding anniversary at 100 Tastes and the experience was memorable. The team was fantastic in organizing the personalized menu, the food was exquisite and the service was impeccable. All the guests loved the elegance of space.\"', '2026-07-04 16:50:35'),
('00e1d71f4d91269d95428711c55dc38e', 'en', 'Jantar de Grupo', 'Group Dinner', '2026-07-04 16:50:35'),
('02cea8cdf66947c12e2d704f2321dc0b', 'en', '43243', '43243', '2026-07-04 16:49:52'),
('04151bb361a58c01d1b727156cffdf0d', 'en', 'RECONHECIMENTO', 'RECOGNITION', '2026-07-04 16:46:48'),
('047bbe5b31dbdedb4deff920f0064709', 'en', 'O 100 Sabores nasce de um sonho: criar um espaço onde a alta classe é prioridade, onde comer é mais\n                    que uma experiência é algo exclusivo...', 'The 100 Tastes are born of a dream: creating a space where high class is priority, where eating is more\nthat an experience is something unique...', '2026-07-04 16:46:48'),
('049e60b09d7a33d899b5a462fbefa875', 'en', 'Soufflé quente de chocolate belga de alta qualidade, servido com sorvete de baunilha e frutos vermelhos.', 'High quality Belgian chocolate hot soufflé served with vanilla ice cream and red fruit.', '2026-07-04 16:49:52'),
('05af213bfc187676f7e37d530e71a75f', 'en', 'Preencha o formulário abaixo com os detalhes do seu evento. A nossa equipa entrará em contacto num prazo de 24 horas.', 'Fill out the form below with the details of your event. Our team will be in touch within 24 hours.', '2026-07-04 16:50:35'),
('078b32092c80956a2feccfd97d801a46', 'en', 'Opinião do Cliente', 'Customer Opinion', '2026-07-04 16:50:35'),
('079d1f714d75beca7869855aa05a537c', 'en', 'Da entrada à sobremesa, a nossa ementa é uma viagem pela gastronomia mediterrânica e europeia,\n                    criada pelo nosso chef para surpreender a cada garfada.', 'From the entrance to dessert, our menu is a journey through Mediterranean and European cuisine,\ncreated by our chef to surprise every fork.', '2026-07-04 16:46:48'),
('0804d9c47d6758da948dc00849448970', 'en', 'Porquê Escolher-nos', 'Why Choose Us', '2026-07-04 16:50:35'),
('0a54c91900d73a5c9bbb3659a3017a95', 'en', 'Cada prato do 100 Sabores é pensado ao detalhe, unindo produtos frescos da época a técnicas\n                    contemporâneas que realçam o melhor de cada ingrediente.', 'Each dish of the 100 Tastes is thought to detail, uniting fresh products from the time to techniques\ncontemporarys that highlight the best of each ingredient.', '2026-07-04 16:46:48'),
('0e4095e7dfecbcd3d65f02d376ab9d1b', 'en', 'Uma Celebração de Sabores e Momentos', 'A Celebration of Tastes and Moments', '2026-07-04 16:50:35'),
('11a5af8a24cd3d4337d6ea74f645fc6c', 'en', 'Espaço & Capacidade', 'Space & Capacity', '2026-07-04 16:50:35'),
('13250e135f35778c613ccef225674a30', 'en', 'Dom: 18h-21h', 'Sun: 18h-21h', '2026-07-04 16:46:48'),
('14885b7493e7d41eb38ced16bcda08de', 'en', 'Andares', 'Floors', '2026-07-04 16:46:48'),
('15364ae6626aae23b3847ce8d58e6827', 'en', 'Serviço de Excelência', 'Service of Excellence', '2026-07-04 16:50:35'),
('15dab7e840a446eec8209b84b7e599c0', 'en', 'Tarte de Lima e Gengibre', 'Lima and Ginger pie', '2026-07-04 16:49:52'),
('18ebbc9a773bd6adcd7d4ef4e52c05a1', 'en', 'Soufflé de Chocolate Belga', 'Belgian Chocolate Soufflé', '2026-07-04 16:49:52'),
('22eeca05b44c3e5dcb475b9bceeef984', 'en', 'Vinhos, batatas, legumes e molhos para partilhar.', 'Wines, potatoes, vegetables and sauces to share.', '2026-07-04 16:46:48'),
('2531b3e459da691e9f27297741cbaf8c', 'en', 'A NOSSA COZINHA', 'OUR COOK', '2026-07-04 16:46:48'),
('257e2c76cea0d811bbe8a6095b50beb1', 'en', 'File Mignon com batata frita', 'File Mignon with French fries', '2026-07-04 16:49:52'),
('273886c5388a0c7e438d200edd3c5371', 'en', 'Selecione o tipo de evento', 'Select event type', '2026-07-04 16:50:35'),
('28dcf3bb26aba9d7d844a033a0a228f2', 'en', 'Uma equipa dedicada e experiente para planear e executar o seu evento com atenção a cada detalhe, garantindo que o seu dia seja perfeito.', 'A dedicated and experienced team to plan and run your event with attention to every detail, ensuring your day is perfect.', '2026-07-04 16:50:35'),
('293520c27c5a56bf957ba06512d4a8f9', 'en', 'Telefone / Telemóvel *', 'Telephone / Mobile *', '2026-07-04 16:50:35'),
('295e44e21e5484ec06622b48f189e7fb', 'en', 'File Mignon com Risotto', 'File Mignon with Risotto', '2026-07-04 16:49:52'),
('297b07513ebf6c842bd2ae58129dbaec', 'en', 'Carta de Vinhos', 'Wine Letter', '2026-07-04 16:46:48'),
('29ccc9f5977b90f75e38a6ff78c1c5e0', 'en', 'Jantares de grupo', 'Group dinners', '2026-07-04 16:46:48'),
('2b0c71f7d58875315e80dae5021b13a1', 'en', 'Evento Corporativo', 'Corporate Event', '2026-07-04 16:50:35'),
('2bd5aafef850b37b82474d92bca78557', 'en', 'Alta Gastronomia', 'High Gastronomy', '2026-07-04 16:50:35'),
('31541c3f30665b31fc4d39de839c3e69', 'en', '\"100 Sabores não é apenas um restaurante. É família que come e sente, que bebe e deseja, que\n                olha o mundo com ganas de o sorver.\"', '\"100 Tastes isn\'t just a restaurant. It\'s family that eats and feels, that drinks and desires, that\nLook at the world with the gust of it.\"', '2026-07-04 16:46:48'),
('31f07342ade3d77f26d9b8c44bbf69a2', 'en', 'Mousse intensa de chocolate belga com uma pitada de flor de sal.', 'Intense Belgian chocolate mousse with a pinch of salt flower.', '2026-07-04 16:49:52'),
('32ab6956683dc4abf743b14d7d82896c', 'en', 'Recomendado', 'Recommended', '2026-07-04 16:46:48'),
('337634cb398db98587d8b7d4dc4356e0', 'en', 'Arroz arbóreo cozinhado lentamente com aspargos frescos, vinho branco e lascas de queijo.', 'Arboreal rice cooked slowly with fresh asparagus, white wine and cheese chips.', '2026-07-04 16:49:52'),
('34a2e1e2326adbde3ec5647603918c58', 'en', 'Contacto para Eventos', 'Events Contact', '2026-07-04 16:50:35'),
('3701da057b056e2d47db3d8d274ba08b', 'en', 'Referências de Vinho', 'Wine References', '2026-07-04 16:46:48'),
('3794a514da28086745367787379d58e6', 'en', 'Base crocante de amêndoa com creme de lima fresca e gengibre cristalizado. Acompanhada de chantilly caseiro.', 'Crunchy almond base with fresh lime cream and crystallized ginger. Accompanied with homemade whipped cream.', '2026-07-04 16:49:52'),
('3ae74f36ab8ef9724e94002c3b97e468', 'en', 'Metro: Rato', 'Metro: Mouse', '2026-07-04 16:46:48'),
('3b3e8a642fb2fd39d14c23a303f8493a', 'en', 'Garanta o seu lugar no 100 Sabores', 'Ensure your place in the 100 Tastes', '2026-07-04 16:46:48'),
('3e38543d081efbfd9cd9f6994e9ba428', 'en', 'Mensagem / Pedidos Especiais', 'Message / Special Requests', '2026-07-04 16:50:35'),
('4100c44543e2e03d48c2c90503f375cd', 'en', 'wdqddd', 'wdqdd', '2026-07-04 16:49:52'),
('49f5b598c28acab54d876b41cabda6ad', 'en', 'CONTACTO', 'CONTACT', '2026-07-04 16:46:48'),
('4a9702f7ffe84ee7fc40d587e191c45c', 'en', 'EVENTOS', 'EVENTS', '2026-07-04 16:46:48'),
('4d368ea7cc82dfa302e07d8501b6e81e', 'en', 'Lugares', 'Places', '2026-07-04 16:46:48'),
('505dffb37d1c9bf6fe5cf9d65817d65d', 'en', 'Seg-Sáb: 12h-00h', 'Seg-Sab: 12h-00 h', '2026-07-04 16:46:48'),
('50e88c23a119047ce328d490f2dde0a5', 'en', 'Uma Casa Sem Tempo', 'A House Without Time', '2026-07-04 16:46:48'),
('511e6121477f2db6d81b3d276349c25f', 'en', 'File mignon grelhado ao ponto perfeito, servido com risotto cremoso de cogumelos selvagens e queijo parmesão. Acompanhado de legumes salteados e molho de vinho tinto.', 'File mignon grilled to the perfect point, served with creamy risotto of wild mushrooms and parmesan cheese. Accompanied with sauteed vegetables and red wine sauce.', '2026-07-04 16:49:52'),
('5528d74f6237aaec9bee1a8a43a71f83', 'en', 'Hambúrguer Gourmet', 'Gourmet burger', '2026-07-04 16:49:52'),
('55adb8b16fe3390195d0d8c29d378c0a', 'en', 'Tipo de Evento *', 'Event Type *', '2026-07-04 16:50:35'),
('560b16dc03ab60a7b0ff9aad9189ffd0', 'en', 'Produtos da época, proteínas do dia sempre frescos.', 'Seasonal products, always fresh day proteins.', '2026-07-04 16:46:48'),
('5b17ac3d812fecfec4b161eb5b4dbd78', 'en', 'Cocktails e Lançamentos', 'Cocktails and Launches', '2026-07-04 16:50:35'),
('5cd1cb307b9280849bc1125e596453ad', 'en', 'Carne de novilho (200g), queijo cheddar maturado, bacon crocante e molho secreto no pão brioche.', 'Sheepmeat (200g), mature cheddar cheese, crispy bacon and secret sauce on brioche bread.', '2026-07-04 16:49:52'),
('60324c31ec67eb09728fc5c83e19901e', 'en', 'Política de Privacidade', 'Privacy Policy', '2026-07-04 16:50:35'),
('62abf917376a58b5e2162fea875538a5', 'en', 'Menus de Degustação', 'Taste Menus', '2026-07-04 16:46:48'),
('64184bc0462719eab691a30d5dbae96b', 'en', 'Pratos de Autor', 'Author Dishes', '2026-07-04 16:46:48'),
('66ae2f22aac378ac5f98587642922fe6', 'en', 'Pratos Diários', 'Daily Dishes', '2026-07-04 16:46:48'),
('694a94103759829aa71ccc72009959a7', 'en', 'Risotto de Aspargos', 'Asparagus Risotto', '2026-07-04 16:49:52'),
('6b58ece8ff377483aac5a587998cff3d', 'en', 'Arroz de pato', 'Duck rice', '2026-07-04 16:49:52'),
('6cabf970ee1d1d73f31bbc8a564b4490', 'en', 'VINHOS', 'WINE', '2026-07-04 16:46:48'),
('6f16ccd5d815c724b172e72833dee561', 'en', 'Nome Completo *', 'Full Name *', '2026-07-04 16:50:35'),
('703f9c08bf57b9e6710cd9505260731c', 'en', 'GALERIA', 'GALLERY', '2026-07-04 16:46:48'),
('710533dd879dd1202e5c73b27705bf02', 'en', 'HOME', 'HOME', '2026-07-04 16:46:48'),
('7380a545c9133341d80d721a8858fe90', 'en', 'O Que Não Pode Perder', 'What You Can\'t Lose', '2026-07-04 16:46:48'),
('73fb1fbc3f7049662187d80df30ce754', 'en', 'SOBRE NÓS', 'ABOUT US', '2026-07-04 16:46:48'),
('74bb157458565bde4e561188f3f586cd', 'en', 'Certificado de Excelência', 'Certificate of Excellence', '2026-07-04 16:46:48'),
('767269d912a5ed4adcd4374dc34dc2b3', 'en', 'Sobremesas', 'Dessert', '2026-07-04 16:46:48'),
('76da12685cc1345cad882ebf244fa7e1', 'en', 'Boa Cama Boa Mesa', 'Good Bed Good Table', '2026-07-04 16:46:48'),
('77b490f8715b22b2f910750d96ade140', 'en', 'Bacalhau à Brás', 'Cod Bras', '2026-07-04 16:44:57'),
('78b25020095737c70c72d744877d8b80', 'en', 'Ideal para:', 'Ideal for:', '2026-07-04 16:50:35'),
('7b8f7911b2760d45acb66c882f7c315c', 'en', 'Eventos Corporativos', 'Corporate Events', '2026-07-04 16:50:35'),
('7c1f08329ab2a73fef2ee2908fab6af6', 'en', 'Bruschetta Mediterrânea', 'Mediterranean Bruschetta', '2026-07-04 16:49:52'),
('7d507e7fff8ad4e865fc7bdceb277182', 'en', 'Nº de Pessoas *', 'Number of People *', '2026-07-04 16:50:35'),
('7e678de5ad0e76d0468e6f44436dbbbe', 'en', 'Cheesecake de Frutos Vermelhos', 'Red Fruit Cheesecake', '2026-07-04 16:49:52'),
('8097d4e9e42b603ac57218667a08479e', 'en', 'O nosso restaurante conta com dois andares elegantemente decorados, oferecendo flexibilidade para acomodar até 80 convidados com total privacidade e conforto climatizado.', 'Our restaurant has two elegantly decorated floors, offering flexibility to accommodate up to 80 guests with total privacy and air-conditioned comfort.', '2026-07-04 16:50:35'),
('825e178a79c63514c3e07fc16129f44b', 'en', 'RESERVE A SUA EXPERIÊNCIA', 'RESERVE YOUR EXPERIENCE', '2026-07-04 16:46:48'),
('82b9ee5a7287b9b9088fbeede01f1143', 'en', 'Guia Michelin', 'Michelin Guide', '2026-07-04 16:46:48'),
('843a1705f2f6b3227997e8aabb29304a', 'en', 'Reservas', 'Reserves', '2026-07-04 16:46:48'),
('85addbf8f3aacade12f58118d82accfe', 'en', 'Solicitar Orçamento', 'Request Budget', '2026-07-04 16:50:35'),
('88941488f82ce78c17070c46bd275137', 'en', 'Eventos privados', 'Private events', '2026-07-04 16:46:48'),
('890dbe27439493bd3fe762022dc7a203', 'en', 'Exclusividade e Requinte', 'Exclusiveness and Fineness', '2026-07-04 16:50:35'),
('89e531b900ab237d1f308852295ea32c', 'en', 'Ver Ementa Completa', 'View Full Menu', '2026-07-04 16:46:48'),
('8a4feec92576aecf54c9b1717ecaf522', 'en', 'Festa de Aniversário', 'Birthday party', '2026-07-04 16:50:35'),
('8d1a7fdd8a0a2e70f7fbf7e3a6937ab1', 'en', 'DESTAQUES', 'HIGHLIGHTS', '2026-07-04 16:46:48'),
('92a80c742c327d7abeb9a00e5bb8f8c3', 'en', '100 Sabores', '100 Flavors', '2026-07-04 16:46:48'),
('9410de143226d80eff46965a13d88327', 'en', 'Jantares de Grupo', 'Group Dinners', '2026-07-04 16:50:35'),
('95fe2e401827d17c312f424156f256e6', 'en', 'Domingo: 12h - 16h', 'Sunday: 12h - 16h', '2026-07-04 16:46:48'),
('969945f35a9410dd8f5874a5c173bca8', 'en', 'Revista de Vinhos', 'Wine Magazine', '2026-07-04 16:46:48'),
('9931794b05bed2b5f23a10f2e2331750', 'en', 'Uma carta de cocktails que reflete a essência do 100 Sabores, com ingredientes naturais e criações\n                    exclusivas.', 'A cocktail menu that reflects the essence of the 100 flavors, with natural ingredients and creations\nexclusive.', '2026-07-04 16:46:48'),
('9b573891c91bfe5465af087572919a23', 'en', 'Pratos na Ementa', 'Dishes on the menu', '2026-07-04 16:46:48'),
('9bd12751e13787435f0f4ae8d51e9103', 'en', 'Reservar mesa', 'Book table', '2026-07-04 16:44:57'),
('9c3449992b8fc0467d939db5d2743f17', 'en', 'Solicitar Proposta', 'Request Proposal', '2026-07-04 16:50:35'),
('9ff95a7d254c10112f123db9d3c7d487', 'en', 'Melhor Carta de Vinhos', 'Best Wine Letter', '2026-07-04 16:46:48'),
('ad91c5ecf2a064c7cfa181c1837f0b64', 'en', 'Deseja um espaço amplo, sofisticado e elegante para um evento especial? O 100 Sabores oferece a atmosfera perfeita. A nossa sala principal e áreas reservadas foram concebidas com um design contemporâneo de luxo, ideal para acolher os seus convidados com total conforto e exclusividade.', 'Would you like a large, sophisticated and elegant space for a special event? The 100 Tastes offers the perfect atmosphere. Our main room and reserved areas are designed with a contemporary luxury design, ideal for welcoming your guests with total comfort and exclusivity.', '2026-07-04 16:50:35'),
('b418efd0cc5e89db8de1b4f78db2a203', 'en', 'Príncipe Real, Lisboa', 'Principe Real, Lisbon', '2026-07-04 16:46:48'),
('b48eee749bcd99b3ed16794ea42d1b12', 'en', 'Eventos no 100 Sabores', 'Events at 100 Tastes', '2026-07-04 16:50:35'),
('b8c8a49f79f2cbcefdd4b08237808c52', 'en', 'Sabores de Autor', 'Author Tastes', '2026-07-04 16:46:48'),
('bb811c6fecb948831bab2fc2661eacc2', 'en', 'Torradas artesanais com tomate confitado, manjericão fresco, mozzarella de búfala e azeite virgem extra. Uma explosão de sabores do Mediterrâneo.', 'Handmade toasts with confited tomato, fresh basil, buffalo mozzarella and extra virgin olive oil. An explosion of Mediterranean flavors.', '2026-07-04 16:49:52'),
('bba2e95b9732abb7ef2aa0273d040bf1', 'en', 'Fazer Reserva', 'Make Reservation', '2026-07-04 16:46:48'),
('bd8305435d735f8a0ff097c51aa7e525', 'en', 'Largo da Trindade', 'Trinity Largo', '2026-07-04 16:46:48'),
('bd97e9dc920becd703c7c79a1bedceb8', 'en', 'Mousse de Chocolate Negro', 'Black Chocolate Mousse', '2026-07-04 16:49:52'),
('c01783ad81ddd3f7bff4a997f339a6e5', 'en', 'O inevitável final feliz — bem doce!', 'The inevitable happy ending — very sweet!', '2026-07-04 16:46:48'),
('c2644a9c9ff5ba4d0d73c338622d6de9', 'en', 'Especial da Casa', 'Special House', '2026-07-04 16:49:52'),
('c2b41b401c2e089b5bb2149d8e6691ce', 'en', 'Segunda a Quinta: 12h - 22h', 'Monday to Thursday: 12h - 22 h', '2026-07-04 16:46:48'),
('c4b99c2e9486226ba01b8ea231d1c4a9', 'en', 'Reservas online', 'Online booking', '2026-07-04 16:46:48'),
('c5209634672ca9eac4ec86ce566757e9', 'en', 'Mais de 200 referências nacionais e internacionais, cuidadosamente selecionadas pelo nosso sommelier\n                    para harmonizar com a nossa cozinha.', 'More than 200 national and international references, carefully selected by our sommelier\nTo harmonize with our kitchen.', '2026-07-04 16:46:48'),
('c8c5efa5d5516988c7a3ad32ebf4f040', 'en', 'Especialidades da Casa', 'Specialties of the House', '2026-07-04 16:46:48'),
('c8c7630b6dfc32b07d1693157b39e473', 'en', 'RESERVAS', 'RESERVES', '2026-07-04 16:46:48'),
('c9b24cca6df2b987b6f6f59887c11dd8', 'en', 'Celebre momentos únicos no coração de Lisboa', 'Celebrate unique moments in the heart of Lisbon', '2026-07-04 16:50:35'),
('cff71fef97ac33cb17aa8e86e1d064a9', 'en', 'Explorar a Ementa', 'Explore the Menu', '2026-07-04 16:46:48'),
('d00887917cf4ecf7a02ef188112ff63f', 'en', 'TripAdvisor', 'TripAdvisor', '2026-07-04 16:46:48'),
('d33bde553e0b498bb294c531e493f7be', 'en', 'Base crocante de bolacha com creme de queijo suave e cobertura de frutos silvestres.', 'Crispy cookie base with soft cheese cream and wild fruit cover.', '2026-07-04 16:49:52'),
('d782fc6e56dfa0d5e5aaa5fa6e08d7d2', 'en', 'Sexta a Sábado: 12h - 00h', 'Friday to Saturday: 12h - 00h', '2026-07-04 16:46:48'),
('d886f789d4070ec37f1abf8106004efa', 'en', 'Cada distinção é o reflexo de uma busca constante pela excelência — à mesa, na adega\n                e em cada detalhe da experiência 100 Sabores.', 'Each distinction is the reflection of a constant search for excellence — at the table, at the winery\nand in every detail of experience 100 Tastes.', '2026-07-04 16:46:48'),
('da13367e953d87eab6fdd2b0573f1450', 'en', 'Criações únicas inspiradas na gastrónomia de alto padrão com influências mediterrânicas e europeias.\n                    O nosso chef traz viagens, vivências e experiências a cada prato.', 'Unique creations inspired by high standard gastronomy with Mediterranean and European influences.\nOur chef brings trips, experiences and experiences to each dish.', '2026-07-04 16:46:48'),
('dc3176ca2f47f410c17988822b328b00', 'en', 'Mesa de Ouro', 'Gold Table', '2026-07-04 16:46:48'),
('dd3d7b69b7a514036163131542768cb3', 'en', 'Data do Evento *', 'Event Date *', '2026-07-04 16:50:35'),
('ddc35f88fa71b6ef142ae61f35364653', 'en', 'Bar', 'Bar', '2026-07-04 16:50:27'),
('ddfcda6314dd4bf1e7da03d9d50df22d', 'en', 'Localização', 'Location', '2026-07-04 16:46:48'),
('de6138e0ceffc4d8fb161827062a13e1', 'en', 'Horário', 'Schedule', '2026-07-04 16:46:48'),
('e263ff4ce0f14ef3ef8a62888ee58714', 'en', 'Cocktails & Bar', 'Cocktails & Bar', '2026-07-04 16:46:48'),
('e384bd849f52f9f3266e4d17848ffca3', 'en', 'Largo da Trindade, Príncipe Real', 'Trinity Largo, Royal Prince', '2026-07-04 16:46:48'),
('e56fa2facc347ac75604464c7d807d4f', 'en', 'Com dois andares e capacidade para 80 pessoas...', 'With two floors and capacity for 80 people...', '2026-07-04 16:46:48'),
('e88e9e522a595fea7aad626d97f20185', 'en', 'Enviar Mensagem', 'Send Message', '2026-07-04 16:50:35'),
('e95e7df44fc2cb94c00798c9602ee859', 'en', 'EMENTA', 'FOOD', '2026-07-04 16:46:48'),
('ea8b7081fb64bd6e47227935cfd7a0e3', 'en', 'Celebrações de Casamentos', 'Wedding Celebrations', '2026-07-04 16:50:35'),
('eab0c255721887e8481423534dd3476a', 'en', 'Outro Evento Privado', 'Another Private Event', '2026-07-04 16:50:35'),
('ef5c06f45a96262b3ef3324a5fbbc1a3', 'en', 'Declaro que li e concordo com a', 'I declare that I have read and agree with the', '2026-07-04 16:50:35'),
('efd62207dafd8dc72a0db2eb8cbc8b4a', 'en', 'Criações únicas do nosso chef, feitas com paixão e inovação.', 'Unique creations of our chef, made with passion and innovation.', '2026-07-04 16:46:48'),
('f49824b06e3f05c12771daf62b7b02ad', 'en', 'Sobremesa da casa', 'House dessert', '2026-07-04 16:44:57'),
('f5a239531004819a8998cf4dc8444879', 'en', 'Ementas personalizadas criadas pelo nosso Chef, combinando a tradição gastronómica com técnicas inovadoras e ingredientes premium selecionados.', 'Custom menus created by our Chef, combining gastronomic tradition with innovative techniques and premium ingredients selected.', '2026-07-04 16:50:35'),
('f6e8820c85ce71429de28ba07c0e1baf', 'en', 'Distinções & Prémios', 'Highlights & Awards', '2026-07-04 16:46:48'),
('faabb9750b19a0a5e2c2af9fd1a9b0b9', 'en', 'Ver Ementa', 'View Menu', '2026-07-04 16:46:48'),
('fcf9dd6c48b4132a161a0f3a811b8a4f', 'en', 'Casamento / Mini Wedding', 'Wedding / Mini Wedding', '2026-07-04 16:50:35'),
('fd0bdf8d9f7c688d7449663224792d73', 'en', 'Festas de Aniversário', 'Birthday parties', '2026-07-04 16:50:35'),
('fd90e8dd48a4f75b240cc0f87b1da451', 'en', 'E-mail *', 'E-mail *', '2026-07-04 16:50:35'),
('ff1ac788da928ea4f0ff7f0c574cc18d', 'en', 'Bebidas & Acompanhamentos', 'Drinks & Follow-ups', '2026-07-04 16:46:48'),
('fff30d787ef68dd50e7dd95e1858cc4f', 'en', 'Uma Experiência Completa', 'A Complete Experience', '2026-07-04 16:50:35');

-- --------------------------------------------------------

--
-- Estrutura da tabela `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nome_oferece` varchar(100) NOT NULL,
  `nome_recebe` varchar(100) NOT NULL,
  `email_comprador` varchar(150) NOT NULL,
  `valor` decimal(6,2) NOT NULL,
  `mensagem` text DEFAULT NULL,
  `data_prevista` date DEFAULT NULL,
  `data_validade` date DEFAULT NULL,
  `status` enum('pendente_pagamento','pago','resgatado','expirado','cancelado') DEFAULT 'pendente_pagamento',
  `reserva_id` int(11) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_resgate` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `vouchers`
--

INSERT INTO `vouchers` (`id`, `codigo`, `nome_oferece`, `nome_recebe`, `email_comprador`, `valor`, `mensagem`, `data_prevista`, `data_validade`, `status`, `reserva_id`, `data_criacao`, `data_resgate`) VALUES
(1, 'VC-C6594172', 'Santiago Silva', 'Mariana Santos Pereira', 'sandomingos45@gmail.com', 100.00, '', NULL, '2027-01-05', 'pendente_pagamento', NULL, '2026-07-04 22:24:52', NULL),
(2, 'VC-37C2BB44', 'Santiago Silva', 'Mariana Santos Pereira', 'sandomingos45@gmail.com', 100.00, '', NULL, '2027-01-06', 'pendente_pagamento', NULL, '2026-07-05 22:17:56', NULL),
(3, 'VC-5A8EC740', 'Santiago Silva', 'Mariana Santos Pereira', 'sandomingos45@gmail.com', 100.00, '', NULL, '2027-01-06', 'pendente_pagamento', NULL, '2026-07-05 22:47:22', NULL),
(4, 'VC-B237CDCD', 'Santuago', 'Luis', 'sandomingos45@gmail.com', 100.00, '', NULL, '2027-01-27', 'pendente_pagamento', NULL, '2026-07-27 14:56:36', NULL),
(5, 'VC-3312EABA', 'Henrique', 'santiago', 'sandomingos45@gmail.com', 50.00, '', NULL, '2027-02-03', 'pendente_pagamento', NULL, '2026-08-02 22:50:57', NULL);

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `vw_reservas_completas`
-- (Veja abaixo para a view atual)
--
CREATE TABLE `vw_reservas_completas` (
`id` int(11)
,`nome` varchar(100)
,`email` varchar(150)
,`telefone` varchar(30)
,`data_reserva` date
,`hora_reserva` time
,`num_pessoas` int(11)
,`observacoes` text
,`status` enum('pendente','confirmada','cancelada')
,`mesa_id` int(11)
,`duracao_minutos` int(11)
,`data_criacao` timestamp
,`atualizada_em` timestamp
,`mesa_numero` varchar(10)
,`mesa_capacidade` int(11)
,`mesa_localizacao` varchar(50)
);

-- --------------------------------------------------------

--
-- Estrutura para vista `vw_reservas_completas`
--
DROP TABLE IF EXISTS `vw_reservas_completas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_reservas_completas`  AS SELECT `r`.`id` AS `id`, `r`.`nome` AS `nome`, `r`.`email` AS `email`, `r`.`telefone` AS `telefone`, `r`.`data_reserva` AS `data_reserva`, `r`.`hora_reserva` AS `hora_reserva`, `r`.`num_pessoas` AS `num_pessoas`, `r`.`observacoes` AS `observacoes`, `r`.`status` AS `status`, `r`.`mesa_id` AS `mesa_id`, `r`.`duracao_minutos` AS `duracao_minutos`, `r`.`data_criacao` AS `data_criacao`, `r`.`atualizada_em` AS `atualizada_em`, `m`.`numero` AS `mesa_numero`, `m`.`capacidade` AS `mesa_capacidade`, `m`.`localizacao` AS `mesa_localizacao` FROM (`reservas` `r` left join `mesas` `m` on(`r`.`mesa_id` = `m`.`id`)) ;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave` (`chave`);

--
-- Índices para tabela `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_data_envio` (`data_envio`),
  ADD KEY `idx_email` (`email`);

--
-- Índices para tabela `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome_utilizador` (`nome_utilizador`),
  ADD KEY `idx_funcionarios_nome_utilizador` (`nome_utilizador`),
  ADD KEY `idx_funcionarios_cargo` (`cargo`);

--
-- Índices para tabela `logs_acesso`
--
ALTER TABLE `logs_acesso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_logs_acesso_funcionario` (`funcionario_id`),
  ADD KEY `idx_logs_acesso_data` (`data_acesso`);

--
-- Índices para tabela `menu_categorias`
--
ALTER TABLE `menu_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `menu_itens`
--
ALTER TABLE `menu_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `criado_por` (`criado_por`),
  ADD KEY `idx_menu_itens_categoria` (`categoria`),
  ADD KEY `idx_menu_itens_tipo` (`tipo`),
  ADD KEY `idx_menu_itens_imagem` (`imagem`);

--
-- Índices para tabela `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero` (`numero`);

--
-- Índices para tabela `mesas_historico`
--
ALTER TABLE `mesas_historico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reserva_id` (`reserva_id`),
  ADD KEY `mesa_id` (`mesa_id`);

--
-- Índices para tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `funcionario_id` (`funcionario_id`);

--
-- Índices para tabela `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_data_reserva` (`data_reserva`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_mesa` (`mesa_id`),
  ADD KEY `idx_reservas_data` (`data_reserva`),
  ADD KEY `idx_reservas_status` (`status`),
  ADD KEY `idx_reservas_email` (`email`);

--
-- Índices para tabela `traducoes`
--
ALTER TABLE `traducoes`
  ADD PRIMARY KEY (`hash`,`idioma`);

--
-- Índices para tabela `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_codigo` (`codigo`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_reserva` (`reserva_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `logs_acesso`
--
ALTER TABLE `logs_acesso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT de tabela `menu_categorias`
--
ALTER TABLE `menu_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `menu_itens`
--
ALTER TABLE `menu_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de tabela `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `mesas_historico`
--
ALTER TABLE `mesas_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de tabela `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `logs_acesso`
--
ALTER TABLE `logs_acesso`
  ADD CONSTRAINT `logs_acesso_ibfk_1` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`);

--
-- Limitadores para a tabela `menu_itens`
--
ALTER TABLE `menu_itens`
  ADD CONSTRAINT `menu_itens_ibfk_1` FOREIGN KEY (`criado_por`) REFERENCES `funcionarios` (`id`);

--
-- Limitadores para a tabela `mesas_historico`
--
ALTER TABLE `mesas_historico`
  ADD CONSTRAINT `mesas_historico_ibfk_1` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mesas_historico_ibfk_2` FOREIGN KEY (`mesa_id`) REFERENCES `mesas` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
