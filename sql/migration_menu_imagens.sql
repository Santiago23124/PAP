-- Adicionar suporte a imagens nos pratos do menu
ALTER TABLE `menu_itens` ADD COLUMN `imagem` VARCHAR(255) DEFAULT NULL;

-- Adicionar índice
CREATE INDEX idx_menu_itens_imagem ON `menu_itens`(`imagem`);