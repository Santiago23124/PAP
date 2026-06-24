CREATE TABLE IF NOT EXISTS eventos(
    id INT(11) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    mensagem TEXT NOT NULL,
    data_hora DATETIME NOT NULL,
    telefone VARCHAR(30) NOT NULL,
    email VARCHAR(150) NOT NULL,
    num_pessoas INT(11) NOT NULL,
    tipo_de_evento VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
);
    
