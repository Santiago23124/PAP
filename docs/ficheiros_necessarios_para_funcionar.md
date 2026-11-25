# Ficheiros necessários para cada função do projeto funcionar

Este ficheiro serve para explicar, de forma simples, quais são os ficheiros principais que o projeto precisa para funcionar corretamente.

## 1. Para abrir o website

Ficheiros principais:
- index.php
- pages/index.html
- css/estilos.css
- js/script.js

Explicação simples:
- O index.php é a entrada principal do projeto.
- A página inicial está em pages/index.html.
- O CSS define a aparência.
- O JavaScript faz interações na página.

## 2. Para fazer login

Ficheiros principais:
- api/login.php
- api/logout.php
- classes/Funcionario.php
- config/database.php
- database/schema.sql

Explicação simples:
- O login é tratado pelo ficheiro api/login.php.
- O logout está em api/logout.php.
- A classe Funcionario guarda as regras do utilizador.
- A base de dados é ligada pelo ficheiro config/database.php.

## 3. Para mostrar o menu

Ficheiros principais:
- api/menu.php
- classes/MenuItem.php
- pages/menu.html
- js/menu-public.js
- imagens/menu/

Explicação simples:
- O menu é carregado pelo ficheiro api/menu.php.
- Cada prato é representado pela classe MenuItem.
- A página do menu está em pages/menu.html.
- As imagens dos pratos ficam na pasta imagens/menu.

## 4. Para fazer reservas

Ficheiros principais:
- api/reservas.php
- classes/Reserva.php
- classes/Mesa.php
- pages/Reservas.html
- js/reservas-form.js

Explicação simples:
- As reservas são tratadas pelo ficheiro api/reservas.php.
- A classe Reserva guarda os dados da reserva.
- A classe Mesa ajuda a controlar as mesas.
- O formulário da reserva está em pages/Reservas.html.

## 5. Para mostrar eventos

Ficheiros principais:
- api/eventos.php
- pages/Eventos.html
- css/pages/eventos.css

Explicação simples:
- Os eventos são recebidos e tratados em api/eventos.php.
- A página visual fica em pages/Eventos.html.
- O estilo do evento está em css/pages/eventos.css.

## 6. Para o formulário de contacto

Ficheiros principais:
- api/contacto.php
- pages/contacto.html
- classes/EmailSender.php

Explicação simples:
- O contacto é tratado pelo ficheiro api/contacto.php.
- A página do formulário está em pages/contacto.html.
- O envio de emails é feito pela classe EmailSender.

## 7. Para usar vouchers

Ficheiros principais:
- api/voucher.php
- classes/Voucher.php
- pages/voucher.html
- js/voucher-form.js

Explicação simples:
- O voucher é gerido em api/voucher.php.
- A classe Voucher define as regras do voucher.
- O formulário está em pages/voucher.html.

## 8. Para o painel de administração

Ficheiros principais:
- pages/dashboard.html
- pages/funcionarios.php
- js/dashboard-tabs.js
- js/menu-dashboard.js
- js/reservas-dashboard.js

Explicação simples:
- O painel administrativo é aberto em pages/dashboard.html.
- A gestão de funcionários está em pages/funcionarios.php.
- Os ficheiros JavaScript fazem as abas e a interação do painel.

## 9. Para a base de dados

Ficheiros principais:
- config/database.php
- database/schema.sql
- restaurante_100_sabores (2).sql

Explicação simples:
- A ligação à base de dados é feita em config/database.php.
- O esquema da base de dados está em database/schema.sql.
- O ficheiro SQL com os dados do projeto fica em restaurante_100_sabores (2).sql.

## 10. Para o envio de emails

Ficheiros principais:
- classes/EmailSender.php
- composer.json
- vendor/autoload.php
- .env

Explicação simples:
- A classe EmailSender é responsável por enviar emails.
- O composer.json diz quais as bibliotecas do projeto.
- O autoload ajuda o PHP a encontrar as classes automaticamente.
- O ficheiro .env guarda dados como senhas e configurações importantes.

## 11. Para a parte visual do projeto

Ficheiros principais:
- css/estilos.css
- css/components/
- css/pages/
- imagens/

Explicação simples:
- O CSS organiza o aspeto das páginas.
- As pastas components e pages guardam estilos específicos.
- A pasta imagens guarda as fotos do projeto.

## Como é que a comunicação acontece no projeto

O projeto funciona com uma comunicação simples entre três partes:
1. A página do website envia dados.
2. A API recebe esses dados e processa.
3. A base de dados guarda ou lê a informação.
4. A API devolve uma resposta em formato JSON.
5. A página mostra essa resposta ao utilizador.

### 1. Comunicação no login

O ficheiro responsável é api/login.php.

Exemplo de código:
```php
$data = json_decode(file_get_contents("php://input"));
$resultado = $funcionario->autenticar($data->nome_utilizador, $data->senha);
echo json_encode($resultado);
```

Explicação simples:
- O formulário manda os dados para a API.
- A API lê esses dados.
- A classe Funcionario verifica se o utilizador existe.
- A API devolve uma resposta de sucesso ou erro.

### 2. Comunicação nas reservas

O ficheiro responsável é api/reservas.php.

Exemplo de código:
```php
$input = json_decode(file_get_contents('php://input'), true);
$reserva = new Reserva($db);
$reserva->nome = $input['nome'] ?? '';
$reserva->email = $input['email'] ?? '';
```

Explicação simples:
- A página de reservas envia os dados do formulário.
- A API recebe esses dados.
- A classe Reserva organiza a informação.
- Depois a reserva é guardada na base de dados.

### 3. Comunicação no formulário de contacto

O ficheiro responsável é api/contacto.php.

Exemplo de código:
```php
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$stmt = $pdo->prepare("INSERT INTO contactos (nome, email, mensagem) VALUES (:nome, :email, :mensagem)");
$stmt->execute([
    ':nome' => $input['nome'],
    ':email' => $input['email'],
    ':mensagem' => $input['mensagem']
]);
```

Explicação simples:
- O utilizador escreve a mensagem.
- A API recebe essa mensagem.
- O PHP guarda a informação na tabela contactos.
- Depois pode enviar um email de confirmação.

### 4. Comunicação com a base de dados

O ficheiro responsável é config/database.php.

Exemplo de código:
```php
$this->conn = new PDO(
    "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
    $this->username,
    $this->password
);
```

Explicação simples:
- Este ficheiro cria a ligação entre o PHP e a base de dados.
- Sem este ficheiro, a API não consegue guardar nem ler informação.

### 5. Resumo da comunicação

Em resumo, o fluxo é este:
- A página envia dados para a API.
- A API lê esses dados.
- A API usa classes como Reserva, Funcionario ou EmailSender.
- A API liga-se à base de dados.
- Devolve uma resposta para a página.

## Resumo rápido

Se quiseres lembrar de forma simples:
- As páginas ficam em pages/.
- As funções do sistema ficam em api/.
- As regras e classes ficam em classes/.
- A ligação à base de dados fica em config/.
- Os estilos ficam em css/.
- As imagens ficam em imagens/.

Se quiseres, posso também fazer uma versão deste ficheiro em formato mais curto, tipo "guia rápido para apresentar ao professor".