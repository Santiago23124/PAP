-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 25-Jun-2026 às 20:04
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
(1, 'Mariana Pereira', 'sandomingos45@gmail.com', '968387773', 'feedback', 'Muito aconchegante', '2026-06-25 14:23:19', '::1');

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
(1, 'admin', '$2y$10$TdzUNDUNJZuliS0kKzziAuIAUewNypLP2G59tsuzPLqa9RXUE2Vp.', 'Administrador do Sistema', 'admin', 'admin@100sabores.pt', '+351 912 345 678', 1, '2026-06-25 13:11:35', '2026-06-10 16:58:44', '2026-06-25 13:11:35'),
(2, 'chef', '$2y$10$c99mfxYr3pd6lwo4AnXq9.mUq1490yub4Mxxh7czlEXsqNBzoy9du', 'Chef Executivo', 'chef', 'chef@100sabores.pt', '+351 913 456 789', 1, '2026-06-25 13:26:04', '2026-06-10 16:58:44', '2026-06-25 13:26:04'),
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
(56, 2, 'login', 'Login bem-sucedido', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 13:26:04');

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
(2, 'Bruschetta Mediterrânea', 'Torradas artesanais com tomate confitado, manjericão fresco, mozzarella de búfala e azeite virgem extra. Uma explosão de sabores do Mediterrâneo.', 6.50, 'entradas', 'vegetariano', 1, 0, 1, '2026-06-10 16:58:44', '2026-06-24 18:22:46', '../imagens/menu/1782325016_imagem_1.jpg'),
(4, 'File Mignon com Risotto', 'File mignon grelhado ao ponto perfeito, servido com risotto cremoso de cogumelos selvagens e queijo parmesão. Acompanhado de legumes salteados e molho de vinho tinto.', 18.50, 'principais', 'especial', 1, 0, 2, '2026-06-10 16:58:44', '2026-06-10 16:58:44', NULL),
(5, 'Salmão Grelhado com Legumes', 'Lombo de salmão atlântico grelhado com ervas aromáticas, servido com legumes da época salteados e molho de mostarda e mel.', 12.00, 'principais', 'normal', 0, 0, 2, '2026-06-10 16:58:44', '2026-06-25 13:17:11', '../imagens/menu/1782393398_1781610377_imagem_31.jpg'),
(6, 'Tarte de Lima e Gengibre', 'Base crocante de amêndoa com creme de lima fresca e gengibre cristalizado. Acompanhada de chantilly caseiro.', 5.50, 'sobremesas', 'vegetariano', 1, 0, 2, '2026-06-10 16:58:44', '2026-06-10 16:58:44', NULL),
(7, 'Soufflé de Chocolate Belga', 'Soufflé quente de chocolate belga de alta qualidade, servido com sorvete de baunilha e frutos vermelhos.', 7.30, 'sobremesas', 'normal', 1, 0, 2, '2026-06-10 16:58:44', '2026-06-10 16:58:44', NULL),
(8, 'Tabua de Entradas Tradicionais', 'Seleção cuidadosa de queijos artesanais, enchidos regionais, presunto de qualidade superior e azeitonas curadas. Acompanhada de compotas caseiras e pão artesanal.', 15.00, 'entradas', 'normal', 1, 0, 1, '2026-06-10 17:08:09', '2026-06-10 17:08:09', NULL),
(13, 'Tarte de Lima e Gengibre', 'Base crocante de amêndoa com creme de lima fresca e gengibre cristalizado. Acompanhada de chantilly caseiro.', 5.50, 'sobremesas', 'vegetariano', 1, 0, 2, '2026-06-10 17:08:09', '2026-06-10 17:08:09', NULL),
(14, 'Soufflé de Chocolate Belga', 'Soufflé quente de chocolate belga de alta qualidade, servido com sorvete de baunilha e frutos vermelhos.', 7.30, 'sobremesas', 'normal', 1, 0, 2, '2026-06-10 17:08:09', '2026-06-10 17:08:09', NULL),
(15, 'Cogumelos Recheados', 'Cogumelos salteados com alho, ervas frescas e gratinados com queijo parmesão.', 6.00, 'entradas', 'normal', 1, 0, 1, '2026-06-10 18:01:27', '2026-06-10 18:01:27', NULL),
(16, 'Risotto de Aspargos', 'Arroz arbóreo cozinhado lentamente com aspargos frescos, vinho branco e lascas de queijo.', 12.00, 'principais', 'normal', 1, 0, 1, '2026-06-10 18:01:43', '2026-06-24 22:48:01', '../imagens/menu/1782341281_imagem_26.jpeg'),
(17, 'Hambúrguer Gourmet', 'Carne de novilho (200g), queijo cheddar maturado, bacon crocante e molho secreto no pão brioche.', 18.00, 'principais', 'especial', 1, 0, 1, '2026-06-10 18:01:57', '2026-06-24 23:36:42', '../imagens/menu/1782344202_imagem_2.jpg'),
(18, 'Mousse de Chocolate Negro', 'Mousse intensa de chocolate belga com uma pitada de flor de sal.', 4.00, 'sobremesas', 'normal', 1, 0, 1, '2026-06-10 18:02:20', '2026-06-10 18:02:20', NULL),
(19, 'Cheesecake de Frutos Vermelhos', 'Base crocante de bolacha com creme de queijo suave e cobertura de frutos silvestres.', 4.00, 'sobremesas', 'especial', 1, 0, 1, '2026-06-10 18:02:43', '2026-06-10 18:02:43', NULL);

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
(8, '8', 8, 'Sala Privada', 1, NULL, '2026-06-10 16:58:44', '2026-06-10 16:58:44');

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
(2, 2, 8, '2026-06-10 17:14:42', 'automatica', NULL),
(9, 8, 5, '2026-06-16 12:25:21', 'automatica', NULL),
(10, 8, 5, '2026-06-16 13:06:59', 'remanejamento', 'Mesa liberada por cancelamento'),
(11, 9, 4, '2026-06-19 13:29:40', 'automatica', NULL),
(12, 2, 8, '2026-06-22 22:44:00', 'remanejamento', 'Mesa liberada por cancelamento'),
(13, 12, 3, '2026-06-23 01:58:23', 'automatica', NULL),
(14, 13, 4, '2026-06-23 01:58:45', 'automatica', NULL),
(15, 15, 5, '2026-06-23 01:59:54', 'automatica', NULL),
(16, 17, 8, '2026-06-23 02:00:44', 'automatica', NULL),
(17, 18, 5, '2026-06-23 02:01:18', 'automatica', NULL),
(18, 19, 4, '2026-06-23 02:01:57', 'automatica', NULL),
(19, 20, 8, '2026-06-23 02:02:10', 'automatica', NULL),
(20, 21, 7, '2026-06-25 00:30:48', 'automatica', NULL),
(21, 22, 5, '2026-06-25 00:32:18', 'automatica', NULL),
(22, 24, 5, '2026-06-25 00:36:43', 'automatica', NULL),
(23, 15, 5, '2026-06-25 13:15:45', 'remanejamento', 'Mesa liberada por cancelamento'),
(24, 12, 3, '2026-06-25 13:15:57', 'remanejamento', 'Mesa liberada por cancelamento');

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
(2, 'Leandro', 'sandomingos45@gmail.com', '968387773', '2026-06-11', '20:30:00', 8, '', 'confirmada', 8, 120, '2026-06-10 17:14:42', '2026-06-24 15:32:21'),
(8, 'Gabriel', 'sandomingos45@gmail.com', '968387773', '2026-06-18', '12:30:00', 6, '', 'cancelada', 5, 120, '2026-06-16 12:25:21', '2026-06-23 01:52:14'),
(9, 'Margarida Rodrigues', 'sandomingos45@gmail.com', '968387773', '2026-06-21', '13:00:00', 4, '', 'confirmada', 4, 120, '2026-06-19 13:29:40', '2026-06-25 13:15:51'),
(12, 'Joana Fonseca', 'joao@gmail.com', '968387773', '2026-06-24', '19:00:00', 4, '', 'cancelada', NULL, 120, '2026-06-23 01:58:23', '2026-06-25 13:15:57'),
(13, 'Margarida Rodrigues', 'joao@gmail.com', '968387773', '2026-06-24', '19:00:00', 4, '', 'confirmada', 4, 120, '2026-06-23 01:58:45', '2026-06-25 13:15:48'),
(14, 'Mariana Pereira', 'joao@gmail.com', '968387773', '2026-06-24', '13:30:00', 12, '', 'confirmada', NULL, 120, '2026-06-23 01:59:34', '2026-06-25 13:15:50'),
(15, 'Tiago Miguel', 'joao@gmail.com', '968387773', '2026-06-26', '19:30:00', 6, '', 'confirmada', NULL, 120, '2026-06-23 01:59:54', '2026-06-25 13:15:46'),
(16, 'Claudia', 'joao@gmail.com', '968387773', '2026-06-27', '21:30:00', 10, '', 'pendente', NULL, 120, '2026-06-23 02:00:25', '2026-06-23 02:00:25'),
(17, 'Ines', 'joao@gmail.com', '968387773', '2026-06-30', '19:30:00', 8, '', 'pendente', 8, 120, '2026-06-23 02:00:44', '2026-06-23 02:00:44'),
(18, 'Ana', 'joao@gmail.com', '968387773', '2026-07-02', '21:30:00', 5, '', 'pendente', 5, 120, '2026-06-23 02:01:18', '2026-06-23 02:01:18'),
(19, 'Mariana Pereira', 'joao@gmail.com', '968387773', '2026-06-30', '19:30:00', 4, '', 'pendente', 4, 120, '2026-06-23 02:01:57', '2026-06-23 02:01:57'),
(20, 'Joana Fonseca', 'joao@gmail.com', '968387773', '2026-06-27', '20:00:00', 8, '', 'pendente', 8, 120, '2026-06-23 02:02:10', '2026-06-23 02:02:10'),
(21, 'Filipa', 'sandomingos45@gmail.com', '968387773', '2026-06-26', '21:00:00', 4, '', 'pendente', 7, 120, '2026-06-25 00:30:48', '2026-06-25 00:30:48'),
(22, 'Margarida Rodrigues', 'sandomingos45@gmail.com', '968387773', '2026-06-26', '12:30:00', 5, '', 'confirmada', 5, 120, '2026-06-25 00:32:18', '2026-06-25 13:15:47'),
(23, 'Pedro Martins', 'sandomingos45@gmail.com', '968387773', '2026-07-04', '21:30:00', 12, '', 'pendente', NULL, 120, '2026-06-25 00:34:46', '2026-06-25 00:34:46'),
(24, 'Joana Pinto', 'sandomingos45@gmail.com', '968387773', '2026-06-30', '19:30:00', 5, '', 'pendente', 5, 120, '2026-06-25 00:36:43', '2026-06-25 00:36:43'),
(25, 'Mariana Pereira', 'sandomingos45@gmail.com', '968387773', '2026-06-26', '21:00:00', 12, '', 'pendente', NULL, 120, '2026-06-25 13:21:35', '2026-06-25 13:21:35');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `logs_acesso`
--
ALTER TABLE `logs_acesso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de tabela `menu_categorias`
--
ALTER TABLE `menu_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `menu_itens`
--
ALTER TABLE `menu_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `mesas_historico`
--
ALTER TABLE `mesas_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

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
