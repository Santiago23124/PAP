-- Migration: Sistema de Gestão de Mesas e Turnos
-- Data: 2025-04-23
-- Descrição: Implementa mesas reais e turnos no restaurante

-- ============================================
-- TABELA MESAS
-- ============================================
CREATE TABLE IF NOT EXISTS mesas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero VARCHAR(10) NOT NULL UNIQUE,
    capacidade INT NOT NULL CHECK (capacidade > 0),
    localizacao ENUM('interior', 'terraco', 'privado', 'bar') DEFAULT 'interior',
    ativa BOOLEAN DEFAULT TRUE,
    observacoes TEXT,
    criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Mesas físicas do restaurante';

-- Inserir mesas de exemplo (2, 4, 6 lugares)
INSERT INTO mesas (numero, capacidade, localizacao) VALUES
-- Mesas de 2 lugares (12 unidades)
('M1', 2, 'interior'),
('M2', 2, 'interior'),
('M3', 2, 'interior'),
('M4', 2, 'terraco'),
('M5', 2, 'terraco'),
('M6', 2, 'interior'),
('M7', 2, 'interior'),
('M8', 2, 'privado'),
('M9', 2, 'privado'),
('M10', 2, 'terraco'),
('M11', 2, 'interior'),
('M12', 2, 'interior'),
-- Mesas de 4 lugares (8 unidades)
('M13', 4, 'interior'),
('M14', 4, 'interior'),
('M15', 4, 'terraco'),
('M16', 4, 'terraco'),
('M17', 4, 'interior'),
('M18', 4, 'interior'),
('M19', 4, 'privado'),
('M20', 4, 'privado'),
-- Mesas de 6 lugares (6 unidades)
('M21', 6, 'interior'),
('M22', 6, 'interior'),
('M23', 6, 'terraco'),
('M24', 6, 'terraco'),
('M25', 6, 'privado'),
('M26', 6, 'privado'),
-- Mesas grandes (8-10 lugares)
('M27', 8, 'privado'),
('M28', 10, 'privado');

-- ============================================
-- TABELA TURNOS (Horários de funcionamento)
-- ============================================
CREATE TABLE IF NOT EXISTS turnos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    dia_semana_set ENUM('todos','segunda_sexta','fim_semana','personalizado') DEFAULT 'todos',
    capacidade_maxima INT DEFAULT 80,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Turnos de horário do restaurante';

-- Inserir turnos padrão
INSERT INTO turnos (nome, hora_inicio, hora_fim, dia_semana_set) VALUES
('Almoço - Dias úteis', '12:00:00', '14:30:00', 'segunda_sexta'),
('Almoço - Fim de semana', '12:00:00', '16:00:00', 'fim_semana'),
('Jantar - Segunda a Quinta', '19:00:00', '22:30:00', 'segunda_sexta'),
('Jantar - Sexta e Sábado', '19:00:00', '23:30:00', 'fim_semana'),
('Jantar - Domingo', '19:00:00', '22:00:00', 'personalizado');

-- ============================================
-- ATUALIZAR TABELA RESERVAS
-- ============================================
-- Adicionar colunas para mesa e turno
ALTER TABLE reservas
ADD COLUMN IF NOT EXISTS mesa_id INT NULL DEFAULT NULL AFTER observacoes,
ADD COLUMN IF NOT EXISTS turno_id INT NULL DEFAULT NULL AFTER mesa_id,
ADD COLUMN IF NOT EXISTS duracao_minutos INT DEFAULT 120 AFTER turno_id,
ADD COLUMN IF NOT EXISTS data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER duracao_minutos,
ADD COLUMN IF NOT EXISTS atualizada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER data_criacao,
ADD INDEX idx_mesa (mesa_id),
ADD INDEX idx_turno (turno_id),
ADD INDEX idx_data_hora (data_reserva, hora_reserva),
ADD INDEX idx_status (status);

-- Chave estrangeira (criada após populações iniciais para não quebrar)
-- ALTER TABLE reservas ADD CONSTRAINT fk_reserva_mesa FOREIGN KEY (mesa_id) REFERENCES mesas(id) ON DELETE SET NULL;
-- ALTER TABLE reservas ADD CONSTRAINT fk_reserva_turno FOREIGN KEY (turno_id) REFERENCES turnos(id) ON DELETE SET NULL;

-- ============================================
-- TABELA HISTÓRICO DE MESAS (Opcional - rastreabilidade)
-- ============================================
CREATE TABLE IF NOT EXISTS mesas_historico (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reserva_id INT NOT NULL,
    mesa_id INT NOT NULL,
    data_atribuicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    motivo ENUM('automatica', 'manual', 'remanejamento') DEFAULT 'automatica',
    observacoes TEXT,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (mesa_id) REFERENCES mesas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Histórico de atribuição de mesas às reservas';

-- ============================================
-- VIEW PARA DASHBOARD (opcional)
-- ============================================
CREATE OR REPLACE VIEW vw_reservas_completas AS
SELECT
    r.*,
    m.numero as mesa_numero,
    m.capacidade as mesa_capacidade,
    m.localizacao as mesa_localizacao,
    t.nome as turno_nome,
    t.hora_inicio as turno_inicio,
    t.hora_fim as turno_fim
FROM reservas r
LEFT JOIN mesas m ON r.mesa_id = m.id
LEFT JOIN turnos t ON r.turno_id = t.id;

-- Trigger: Impedir reservas duplicadas na mesma mesa/horário
-- Criar manualmente no MySQL se necessário, pois requer mudança de delimiter
-- DELIMITER $$
-- CREATE TRIGGER before_reserva_insert BEFORE INSERT ON reservas
-- FOR EACH ROW
-- BEGIN
--     DECLARE mesa_count INT;
--     IF NEW.mesa_id IS NOT NULL THEN
--         SELECT COUNT(*) INTO mesa_count
--         FROM reservas
--         WHERE mesa_id = NEW.mesa_id
--           AND data_reserva = NEW.data_reserva
--           AND hora_reserva = NEW.hora_reserva
--           AND status IN ('pendente', 'confirmada');
--         IF mesa_count > 0 THEN
--             SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Esta mesa já está reservada para este horário';
--         END IF;
--     END IF;
-- END$$
-- DELIMITER ;

-- O trigger pode ser criado manualmente via phpMyAdmin com a consulta acima.
