# Excertos de Código Principais — Restaurante 100 Sabores

> Documento de apoio à PAP. Reúne os excertos de código mais importantes de cada
> funcionalidade do projeto, cada um acompanhado de uma breve explicação do que faz
> e de como funciona.

## Índice

1. [Autenticação dos funcionários](#1-autenticação-dos-funcionários)
2. [Tradução (i18n)](#2-tradução-i18n)
3. [Preview da imagem no menu de adicionar prato](#3-preview-da-imagem-no-menu-de-adicionar-prato)
4. [Sistema de reservas](#4-sistema-de-reservas)
5. [Atribuição da mesa](#5-atribuição-da-mesa)
6. [Sistema de emails](#6-sistema-de-emails)
7. [Verificação de segurança](#7-verificação-de-segurança)
8. [localStorage / sessionStorage (password e itens do menu)](#8-localstorage--sessionstorage-password-e-itens-do-menu)
9. [Gestão de reservas: editar, cancelar, confirmar e eliminar](#9-gestão-de-reservas-editar-cancelar-confirmar-e-eliminar)

---

## 1. Autenticação dos funcionários

A autenticação está dividida em três camadas: o **formulário** (login.html) envia as
credenciais, a **API** (`api/login.php`) recebe o pedido e a **classe `Funcionario`**
verifica a password contra a base de dados.

O ponto central da segurança é o `password_verify()`: a password **nunca é guardada em
texto simples** — na base de dados existe apenas um *hash*. Comparamos a password escrita
com esse hash, sem nunca a saber ao contrário.

```php
// classes/Funcionario.php
// Recebe o nome de utilizador e a senha escritos no formulário de login
public function autenticar($nome_utilizador, $senha) {
    // Monta a consulta SQL: procura o funcionário ATIVO com aquele nome de utilizador.
    // Usamos :nome_utilizador como MARCADOR (placeholder) em vez de colar o valor no texto.
    $query = "SELECT id, nome_utilizador, senha, nome_completo, cargo, email, telefone, ativo
              FROM " . $this->table_name . "     -- nome da tabela (ex.: funcionarios)
              WHERE nome_utilizador = :nome_utilizador AND ativo = 1  -- só contas ativas
              LIMIT 0,1";                        // basta 1 resultado (o nome é único)

    $stmt = $this->conn->prepare($query);                   // prepara a query (protege de SQL Injection)
    $stmt->bindParam(":nome_utilizador", $nome_utilizador); // liga o marcador ao valor real, "à parte" da SQL
    $stmt->execute();                                       // executa a consulta na base de dados

    if ($stmt->rowCount() > 0) {                 // encontrou algum funcionário com esse nome?
        $row = $stmt->fetch(PDO::FETCH_ASSOC);   // vai buscar essa linha como array associativo

        // password_verify: compara a senha escrita com o "hash" guardado na BD.
        // Nunca desencriptamos o hash — a função faz a comparação de forma segura.
        if (password_verify($senha, $row['senha'])) {
            $this->atualizarUltimoAcesso($row['id']);                        // regista a hora deste acesso
            $this->registrarLogAcesso($row['id'], 'login', 'Login bem-sucedido'); // grava no log de auditoria

            // Devolve os dados do funcionário para o site usar — MAS NUNCA a senha/hash
            return array(
                "sucesso" => true,
                "dados" => array(
                    "id" => $row['id'],
                    "nome_utilizador" => $row['nome_utilizador'],
                    "nome_completo" => $row['nome_completo'],
                    "cargo" => $row['cargo'],           // usado depois para as permissões
                    "email" => $row['email'],
                    "telefone" => $row['telefone']
                )
            );
        }
    }

    // Chega aqui se: o utilizador não existe OU a password está errada.
    // Regista a tentativa falhada (para auditoria) e devolve erro genérico.
    $this->registrarLogAcesso(null, 'login_falha', 'Tentativa de login falhada para: ' . $nome_utilizador);
    return array("sucesso" => false, "erro" => "Nome de utilizador ou senha incorretos");
}
```

Do lado da API, depois de a autenticação ser bem-sucedida, geramos um novo ID de sessão
(para prevenir *session fixation* / roubo de sessão) e guardamos que o funcionário está
autenticado:

```php
// api/login.php
// Chama o método autenticar() da classe (o código de cima) com o que veio do formulário
$resultado = $funcionario->autenticar($data->nome_utilizador, $data->senha);

if ($resultado['sucesso']) {                  // login válido
    session_regenerate_id(true);              // gera um novo ID de sessão → previne roubo de sessão
    http_response_code(200);                  // 200 = OK
    $_SESSION['funcionario_logado'] = true;   // marca na sessão que o funcionário está autenticado
    $_SESSION['utilizador'] = $data->nome_utilizador; // guarda quem está autenticado
    echo json_encode($resultado);             // devolve a resposta ao navegador em JSON
} else {                                      // credenciais erradas
    http_response_code(401);                  // 401 = Não autorizado
    echo json_encode(array("sucesso" => false, "erro" => $resultado['erro']));
}
```

Cada cargo tem permissões diferentes — o `admin` pode tudo, o `chef` só mexe no menu e o
`garcom` só nas reservas:

```php
// classes/Funcionario.php
// $cargo = função do funcionário; $permissao = o que ele quer fazer (ex.: 'menu', 'reservas')
public function verificarPermissao($cargo, $permissao) {
    // Tabela que define o que cada cargo pode fazer
    $permissoes = array(
        'admin'  => array('todas'),      // admin → acesso a tudo
        'chef'   => array('menu'),       // chef  → só o menu
        'garcom' => array('reservas')    // garçom → só as reservas
    );

    if (isset($permissoes[$cargo])) {    // o cargo existe na tabela?
        // Tem permissão se o cargo tiver 'todas' OU exatamente a permissão pedida
        return in_array('todas', $permissoes[$cargo]) || in_array($permissao, $permissoes[$cargo]);
    }
    return false;                        // cargo desconhecido → sem permissão
}
```

---

## 2. Tradução (i18n)

O site é bilingue (Português / Inglês). Em vez de duplicar páginas, cada elemento HTML
traduzível tem um atributo `data-i18n="chave"`. Um dicionário JavaScript guarda o texto de
cada chave nos dois idiomas, e a função `mudarIdioma()` percorre a página e substitui o
texto de cada elemento pela tradução correspondente.

```javascript
// js/i18n.js — dicionário (excerto)
// Objeto com um "sub-dicionário" por idioma. A mesma CHAVE aponta para o texto certo em cada língua.
const i18n = {
    pt: {                                    // traduções em Português
        "nav-home": "HOME",
        "nav-menu": "EMENTA",
        "res-btn":  "Confirmar Reserva",
        // ... centenas de chaves
    },
    en: {                                    // traduções em Inglês (mesmas chaves, textos diferentes)
        "nav-home": "HOME",
        "nav-menu": "MENU",
        "res-btn":  "Confirm Reservation",
        // ...
    }
};
```

```javascript
// js/i18n.js — função principal de tradução
// Recebe o código do idioma a aplicar (ex.: 'pt' ou 'en')
function mudarIdioma(idioma) {
    if (!i18n[idioma]) {                       // esse idioma existe no dicionário?
        console.warn(`Idioma "${idioma}" não suportado`); // avisa na consola
        return;                                // se não existir, sai sem fazer nada
    }

    localStorage.setItem('idioma', idioma);    // guarda a escolha para a próxima visita
    const textos = i18n[idioma];               // atalho para o sub-dicionário do idioma escolhido

    // 1) Traduz todo o texto: procura todos os elementos com o atributo data-i18n
    document.querySelectorAll('[data-i18n]').forEach(elemento => {
        const chave = elemento.getAttribute('data-i18n');  // lê a chave (ex.: "nav-menu")
        if (textos[chave]) elemento.textContent = textos[chave]; // troca o texto pela tradução
    });

    // 2) Traduz também os placeholders (texto-dica dentro dos inputs)
    document.querySelectorAll('[data-i18n-placeholder]').forEach(elemento => {
        const chave = elemento.getAttribute('data-i18n-placeholder');
        if (textos[chave]) elemento.placeholder = textos[chave];
    });

    document.documentElement.lang = idioma;    // atualiza <html lang="..."> (bom para SEO/acessibilidade)
}
```

Ao carregar a página, o idioma é escolhido por esta ordem de prioridade:
**escolha guardada → idioma do navegador → Português (padrão)**.

```javascript
// js/i18n.js — idioma inicial
// Decide que idioma mostrar quando a página abre, por ordem de prioridade
function obterIdiomaInicial() {
    const idiomaSalvo = localStorage.getItem('idioma');   // 1) já houve uma escolha anterior?
    if (idiomaSalvo && i18n[idiomaSalvo]) return idiomaSalvo; // se sim e for válida, usa-a

    // 2) senão, tenta o idioma do navegador. Ex.: "pt-PT" → fica só "pt"
    const idiomaNav = (navigator.language || 'pt').split('-')[0];
    if (i18n[idiomaNav]) return idiomaNav;                 // usa-o se o tivermos no dicionário

    return 'pt';                                           // 3) por defeito, Português
}

// Quando o HTML acaba de carregar, aplica o idioma inicial calculado acima
document.addEventListener('DOMContentLoaded', () => {
    mudarIdioma(obterIdiomaInicial());
});
```

---

## 3. Preview da imagem no menu de adicionar prato

Quando um funcionário seleciona uma imagem para um prato, mostramos logo uma pré-visualização
**sem enviar nada para o servidor**. Isto é feito com a API `FileReader` do navegador, que lê
o ficheiro escolhido e o converte numa URL de dados (base64) que pode ser usada diretamente no
`src` de uma `<img>`.

```javascript
// js/menu-dashboard.js
// Chamada quando o funcionário escolhe uma imagem no <input type="file">. input = esse campo.
window.previewImageMenu = function(input) {
    const fileLabel        = document.getElementById('menuItemFileName');     // onde mostramos o nome do ficheiro
    const previewContainer = document.getElementById('menuItemImagePreview'); // a caixa da pré-visualização
    const previewImg       = document.getElementById('previewMenuImg');       // a <img> onde a imagem aparece

    if (input.files && input.files[0]) {           // o utilizador chegou a escolher um ficheiro?
        const file = input.files[0];               // fica com o primeiro (e único) ficheiro
        if (fileLabel) fileLabel.textContent = file.name; // mostra o nome do ficheiro no ecrã

        const reader = new FileReader();           // objeto do navegador que lê ficheiros localmente
        reader.onload = function(e) {              // esta função corre QUANDO a leitura terminar
            if (previewImg) previewImg.src = e.target.result;   // e.target.result = a imagem em base64
            if (previewContainer) previewContainer.style.display = 'block'; // torna a caixa visível
        };
        reader.readAsDataURL(file);                // arranca a leitura → converte para base64 (data URL)
    } else {                                       // não há ficheiro (ex.: o utilizador cancelou)
        if (fileLabel) fileLabel.textContent = 'Nenhuma imagem selecionada';
        if (previewContainer) previewContainer.style.display = 'none';  // esconde a pré-visualização
    }
};
```

Só quando o formulário é submetido é que a imagem é **realmente enviada** para o servidor
(`upload_imagem.php`), e é o URL devolvido pelo servidor que fica guardado no prato:

```javascript
// js/menu-dashboard.js — dentro do submit do formulário
const fileInput = document.getElementById('menuItemImageFile');  // o campo de ficheiro
// URL escrito à mão (se existir); senão fica null. É o valor por defeito caso não haja upload.
let finalImageUrl = document.getElementById('menuItemImage').value.trim() || null;

if (fileInput && fileInput.files && fileInput.files.length > 0) {  // o utilizador escolheu um ficheiro?
    const formData = new FormData();               // "envelope" para enviar o ficheiro por HTTP
    formData.append('imagem', fileInput.files[0]); // mete o ficheiro no campo 'imagem'

    // Envia o ficheiro para o servidor e espera (await) pela resposta
    const uploadResp = await fetch('../api/upload_imagem.php', { method: 'POST', body: formData });
    const uploadResult = await uploadResp.json();  // converte a resposta (JSON) num objeto

    if (uploadResult.sucesso) {                    // o servidor guardou a imagem?
        finalImageUrl = uploadResult.url;          // ficamos com o URL final devolvido pelo servidor
    } else {                                       // falhou o upload
        mostrarToast('Erro no upload: ' + uploadResult.erro, 'error'); // avisa o utilizador
        throw new Error('Upload failed');          // interrompe a submissão do formulário
    }
}
```

---

## 4. Sistema de reservas

Uma reserva percorre este caminho: **formulário → API `reservas.php` → classe `Reserva`**.
A API recebe os dados, **valida-os**, verifica a **disponibilidade** (capacidade) e só depois
cria a reserva. No momento em que a reserva é criada, é logo associada uma mesa automaticamente
(ver secção 5).

```php
// api/reservas.php — criar reserva
function criarReserva() {
    global $db;   // usa a ligação à base de dados criada noutro sítio
    // Lê o corpo do pedido (JSON enviado pelo formulário). Se não for JSON, usa $_POST.
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $reserva = new Reserva($db);                     // cria um objeto Reserva ligado à BD
    // Preenche cada campo do objeto com o valor recebido. O ?? '' evita erro se faltar o campo.
    $reserva->nome         = $input['nome'] ?? '';
    $reserva->email        = $input['email'] ?? '';
    $reserva->telefone     = $input['telefone'] ?? '';
    $reserva->data_reserva = $input['data'] ?? '';
    $reserva->hora_reserva = $input['hora'] ?? '';
    $reserva->num_pessoas  = $input['num_pessoas'] ?? 0;
    $reserva->observacoes  = $input['observacoes'] ?? '';

    // 1) Validar os dados. Se houver erros, responde com 400 (pedido inválido) e para.
    $erros = $reserva->validar();
    if (!empty($erros)) { json_response(['sucesso' => false, 'erros' => $erros], 400); return; }

    // 2) Verificar se ainda há lugares para aquele dia/hora (capacidade do restaurante)
    if (!$reserva->verificarDisponibilidade($reserva->data_reserva, $reserva->hora_reserva, $reserva->num_pessoas)) {
        json_response(['sucesso' => false, 'erro' => 'Não há vagas disponíveis para este horário.'], 400);
        return;                                      // sem vagas → para aqui
    }

    // 3) Criar a reserva na BD (gera o ID e associa uma mesa automaticamente — ver secção 5)
    if (!$reserva->criar()) throw new Exception('Falha ao inserir reserva no banco');

    // 4) Enviar emails: confirmação para o cliente e notificação para o restaurante
    $emailSender = new EmailSender();
    $emailSender->enviarConfirmacaoReserva($reserva);
    $emailSender->enviarNotificacaoRestaurante($reserva);

    // Responde ao navegador com sucesso e o ID da nova reserva
    json_response(['sucesso' => true, 'id' => $reserva->id, /* ...mesa... */]);
}
```

A **validação** garante que os dados fazem sentido antes de tocar na base de dados: nome,
email válido, telefone, data que não seja no passado, antecedência mínima e horário dentro do
funcionamento.

```php
// classes/Reserva.php — validar()
public function validar() {
    $erros = [];   // lista vazia onde vamos acumulando as mensagens de erro encontradas

    // Nome: obrigatório e com pelo menos 2 caracteres
    if (empty($this->nome) || strlen($this->nome) < 2)
        $erros[] = "Nome deve ter pelo menos 2 caracteres";

    // Email: obrigatório e com formato válido (filter_var faz a verificação)
    if (empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL))
        $erros[] = "Email inválido";

    // Telefone: obrigatório e com no mínimo 9 dígitos
    if (empty($this->telefone) || strlen($this->telefone) < 9)
        $erros[] = "Telefone inválido";

    // Data: transforma o texto num objeto de data e compara com agora
    $data_reserva = new DateTime($this->data_reserva);
    if ($data_reserva < new DateTime())          // é anterior a este momento?
        $erros[] = "Data da reserva não pode ser no passado";

    // Número de pessoas: tem de estar entre 1 e 12
    if (empty($this->num_pessoas) || $this->num_pessoas < 1 || $this->num_pessoas > 12)
        $erros[] = "Número de pessoas deve estar entre 1 e 12";

    // Antecedência mínima: a reserva não pode ser "para já"
    $reserva_dt = new DateTime($this->data_reserva . ' ' . $this->hora_reserva); // data + hora juntas
    // Diferença em segundos entre o momento da reserva e agora
    $diff_segundos = $reserva_dt->getTimestamp() - (new DateTime())->getTimestamp();
    if ($diff_segundos < (Config::HORAS_ANTECEDENCIA_MINIMA * 3600))   // horas × 3600 = segundos
        $erros[] = "Reserva deve ser feita com pelo menos " . Config::HORAS_ANTECEDENCIA_MINIMA . " horas de antecedência";

    return $erros;   // devolve a lista (vazia = tudo ok)
}
```

A **disponibilidade** soma quantas pessoas já têm reserva num horário que se **sobreponha** ao
pedido e verifica se ainda cabe a nova reserva dentro da capacidade máxima do restaurante:

```php
// classes/Reserva.php — verificarDisponibilidade()
// Soma quantas pessoas já têm reserva num horário que se SOBREPONHA ao pedido.
$query = "SELECT SUM(num_pessoas) as total_pessoas    -- total de pessoas já reservadas
          FROM reservas
          WHERE data_reserva = :data_reserva           -- no mesmo dia
            AND status IN ('pendente', 'confirmada')    -- só reservas que contam (não canceladas)
            AND (
              -- Condição clássica de sobreposição de intervalos de tempo:
              -- a reserva existente começa antes de a nova acabar...
              (TIME(hora_reserva) < ADDTIME(:hora_nova, SEC_TO_TIME(:duracao * 60)))
              -- ...E a reserva existente acaba depois de a nova começar.
              AND (ADDTIME(hora_reserva, SEC_TO_TIME(duracao_minutos * 60)) > :hora_nova)
            )";
// ... (prepare, bind e execute da query) ...
$total_existente = $result['total_pessoas'] ?? 0;         // se não houver nenhuma, conta como 0
return ($total_existente + $num_pessoas) <= $capacidade_total; // ainda cabe a nova reserva?
```

---

## 5. Atribuição da mesa

Quando uma reserva é criada, o sistema escolhe **automaticamente** a melhor mesa livre. O
algoritmo procura mesas ativas com **capacidade suficiente**, ordenadas da mais pequena para a
maior (para não "gastar" uma mesa grande com poucas pessoas), e depois fica só com as que **não
têm conflito de horário**.

```php
// classes/Mesa.php — o coração da atribuição automática
// static = pertence à classe, não a uma mesa específica. Recebe a BD e os dados da reserva.
public static function obterMesasDisponiveis($db, $data, $hora, $num_pessoas,
                                             $duracao_minutos = Config::DURACAO_PADRAO_RESERVA) {
    // 1) Vai buscar mesas ATIVAS com lugares suficientes, da menor para a maior.
    //    capacidade ASC = tenta primeiro a mesa mais pequena; RAND() desempata entre iguais.
    $query = "SELECT * FROM mesas
              WHERE capacidade >= :capacidade_min AND ativa = 1
              ORDER BY capacidade ASC, RAND()";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':capacidade_min', $num_pessoas, PDO::PARAM_INT); // nº de pessoas = mínimo de lugares
    $stmt->execute();
    $todas_mesas = $stmt->fetchAll();                // todas as mesas grandes o suficiente

    // 2) Dessas, ficar só com as que estão realmente LIVRES naquele horário
    $mesas_disponiveis = [];
    foreach ($todas_mesas as $mesa) {                // percorre mesa a mesa
        $obj_mesa = new Mesa($db);
        $obj_mesa->id = $mesa['id'];
        // pergunta a essa mesa se está livre no dia/hora pedidos (função abaixo)
        if ($obj_mesa->verificarDisponibilidade($data, $hora, $duracao_minutos)) {
            $mesas_disponiveis[] = $mesa;            // se sim, entra na lista final
        }
    }
    return $mesas_disponiveis;   // lista já ordenada: a 1.ª é a melhor mesa a usar
}
```

A verificação de conflito, mesa a mesa, conta quantas reservas ativas dessa mesa se **cruzam**
com o horário pedido — se forem 0, a mesa está livre:

```php
// classes/Mesa.php — verificarDisponibilidade() de uma mesa
// Conta quantas reservas ativas DESTA mesa se cruzam com o horário pedido.
$query = "SELECT COUNT(*) as count FROM reservas
          WHERE mesa_id = :mesa_id                    -- só esta mesa
            AND status IN ('pendente', 'confirmada')  -- reservas que ocupam a mesa
            AND data_reserva = :data_reserva          -- no mesmo dia
            AND (
              -- mesma lógica de sobreposição de horários da secção 4:
              TIME(:hora_inicio) < ADDTIME(hora_reserva, SEC_TO_TIME(duracao_minutos * 60))
              AND TIME(:hora_fim) > hora_reserva
            )";
// ... (prepare, bind e execute) ...
return $result['count'] == 0;   // 0 reservas em conflito = a mesa está livre
```

Na criação da reserva, a primeira mesa disponível (a mais pequena que serve) é a escolhida, e
a atribuição fica registada num histórico:

```php
// classes/Reserva.php — criar()
// Pede a lista de mesas livres (já ordenada da menor para a maior)
$mesas_disponiveis = Mesa::obterMesasDisponiveis($this->conn, $this->data_reserva, $this->hora_reserva, $this->num_pessoas);
if (!empty($mesas_disponiveis)) {                  // há pelo menos uma mesa livre?
    $this->mesa_id = $mesas_disponiveis[0]['id'];  // escolhe a 1.ª = a mais pequena que serve
}
// ... aqui é feito o INSERT da reserva na base de dados ...
if ($this->mesa_id) {                              // se ficou com mesa atribuída
    $this->registrarHistorico($this->mesa_id, 'automatica'); // guarda no histórico (atribuição automática)
}
```

Também é possível **reatribuir a mesa manualmente** (na dashboard), verificando primeiro se a
nova mesa está mesmo livre para aquela reserva.

---

## 6. Sistema de emails

O envio de emails está centralizado na classe `EmailSender`, que usa o **PHPMailer** para
enviar através do Gmail por SMTP. Cada tipo de email (confirmação ao cliente, notificação ao
restaurante, voucher, contacto) tem um método que **constrói o HTML** e um método comum que
**envia**.

```php
// classes/EmailSender.php — construtor (configuração SMTP)
// Corre automaticamente ao criar (new) um EmailSender. Lê as configurações do ficheiro .env.
public function __construct() {
    // ?? = "se não existir no .env, usa este valor por defeito"
    $this->smtp_host     = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';  // servidor de envio (Gmail)
    $this->smtp_port     = (int)($_ENV['SMTP_PORT'] ?? 587);        // porta SMTP (587 = STARTTLS)
    $this->smtp_username = $_ENV['SMTP_USER'] ?? '';                // conta de email que envia
    $this->smtp_password = $_ENV['SMTP_PASS'] ?? '';   // password de APLICAÇÃO, lida do .env (nunca no código)
    $this->from_email    = $_ENV['SMTP_FROM'] ?? Config::EMAIL_RESTAURANTE; // remetente mostrado
    $this->from_name     = Config::NOME_RESTAURANTE;                // nome que aparece como remetente
    $this->usar_phpmailer = true;                                  // usar a biblioteca PHPMailer
}
```

O método público monta o assunto e o corpo, e delega no `enviarEmail()`:

```php
// classes/EmailSender.php
// Recebe o objeto da reserva e trata de enviar o email de confirmação ao cliente
public function enviarConfirmacaoReserva($reserva) {
    $subject = "Confirmação de Reserva - " . Config::NOME_RESTAURANTE;  // assunto do email
    $body    = $this->gerarTemplateConfirmacao($reserva);  // monta o HTML com os dados da reserva
    // Delega no método comum enviarEmail(): para quem, assunto e corpo
    return $this->enviarEmail($reserva->email, $subject, $body);
}
```

O envio propriamente dito, com PHPMailer configurado para o Gmail:

```php
// classes/EmailSender.php — enviarComPHPMailer()
$mail = new PHPMailer\PHPMailer\PHPMailer(true);  // (true) = lançar exceções em caso de erro
try {
    $mail->isSMTP();                              // enviar via servidor SMTP (não pela função mail() do PHP)
    $mail->Host       = $this->smtp_host;         // servidor (smtp.gmail.com)
    $mail->SMTPAuth   = true;                      // o servidor exige autenticação
    $mail->Username   = $this->smtp_username;      // conta de email
    $mail->Password   = $this->smtp_password;      // password de aplicação
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // ligação encriptada
    $mail->Port       = $this->smtp_port;          // porta (587)

    $mail->setFrom($this->from_email, $this->from_name); // de quem vem o email
    $mail->addAddress($para);                      // para quem vai
    $mail->isHTML(true);                           // o corpo é HTML (com formatação)
    $mail->Subject = $assunto;                     // assunto
    $mail->Body    = $corpo;                       // conteúdo HTML
    $mail->send();                                 // envia mesmo o email
    return true;                                   // correu bem
} catch (Exception $e) {                           // algo falhou no envio
    error_log("Erro ao enviar email via PHPMailer: " . $mail->ErrorInfo); // regista o erro no log
    return false;                                  // avisa que falhou
}
```

Antes de mostrar os dados dentro do email (por ex. as observações do cliente), estes são
**escapados** com `htmlspecialchars()` para evitar injeção de HTML no email.

---

## 7. Verificação de segurança

A segurança do projeto assenta em várias camadas:

**a) Proteção contra SQL Injection** — todas as consultas usam *prepared statements* com
marcadores (`:parametro`), e os valores nunca são concatenados diretamente na query:

```php
// classes/Funcionario.php
$stmt = $this->conn->prepare("SELECT ... WHERE nome_utilizador = :nome_utilizador AND ativo = 1");
$stmt->bindParam(":nome_utilizador", $nome_utilizador);  // o valor entra "à parte", nunca no texto da SQL
$stmt->execute();
```

**b) Passwords com hash** — nunca guardamos a password real, só o hash. A verificação é feita
com `password_verify()` (ver secção 1).

**c) Limpeza de dados de entrada (XSS)** — os dados que vêm do utilizador são "lavados" com
`htmlspecialchars()` + `strip_tags()` antes de ir para a base de dados:

```php
// classes/Reserva.php — criar()
// strip_tags remove tags HTML (<script>...); htmlspecialchars transforma < > & em texto inofensivo.
// Juntos, impedem que código malicioso do utilizador seja guardado/executado (XSS).
$this->nome        = htmlspecialchars(strip_tags($this->nome));
$this->email       = htmlspecialchars(strip_tags($this->email));
$this->observacoes = htmlspecialchars(strip_tags($this->observacoes));
```

**d) Cookies de sessão seguros** — a sessão é configurada com flags que dificultam ataques XSS
e CSRF, e o ID de sessão é regenerado após o login:

```php
// api/login.php
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,     // o JavaScript não consegue ler o cookie (protege de XSS)
    'samesite' => 'Strict'  // o cookie não é enviado a partir de outros sites (protege de CSRF)
]);
session_start();
// ...
session_regenerate_id(true); // após login → previne roubo/fixação de sessão
```

**e) Registo de acessos (auditoria)** — cada login (com ou sem sucesso) é gravado numa tabela
de logs, com IP e navegador, para deteção de tentativas suspeitas:

```php
// classes/Funcionario.php — registrarLogAcesso()
// Insere uma linha na tabela de logs com o que aconteceu (login, falha, etc.)
$stmt = $this->conn->prepare("INSERT INTO logs_acesso
        (funcionario_id, acao, detalhes, ip_address, user_agent)
        VALUES (:funcionario_id, :acao, :detalhes, :ip_address, :user_agent)");

$ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';   // IP de quem fez o pedido
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';        // navegador/dispositivo usado
// ... a seguir faz-se o bind de cada :marcador e o execute() para gravar
```

**f) Proteção das páginas de administração** — o dashboard verifica logo no topo se existe uma
sessão válida; se não existir, redireciona para o login e esconde a página (ver secção 8).

---

## 8. localStorage / sessionStorage (password e itens do menu)

### Dados do funcionário autenticado (sessionStorage)

Ao fazer login com sucesso, os **dados do funcionário** (nunca a password!) são guardados na
`sessionStorage`. Ao contrário do `localStorage`, a `sessionStorage` é apagada quando o
separador é fechado — o que é mais seguro para dados de sessão.

```javascript
// pages/login.html — após login bem-sucedido
if (data.sucesso) {                            // a API confirmou as credenciais?
    // Guarda os dados do funcionário na sessionStorage. JSON.stringify converte o objeto em texto.
    sessionStorage.setItem('loggedInUser', JSON.stringify({
        id: data.dados.id,
        nome_utilizador: data.dados.nome_utilizador,
        nome_completo: data.dados.nome_completo,
        cargo: data.dados.cargo,               // guardado para saber o que ele pode fazer
        email: data.dados.email
        // repara: a password NUNCA é guardada
    }));
    window.location.href = 'dashboard.html';   // redireciona para a área restrita
}
```

Todas as páginas de administração verificam esta chave **antes de mostrar o conteúdo**. Se não
houver sessão, o utilizador é imediatamente reenviado para o login:

```javascript
// pages/dashboard.html — logo no <head>, antes de renderizar
// Se NÃO existir a chave da sessão, é porque ninguém fez login → bloqueia o acesso
if (!sessionStorage.getItem('loggedInUser')) {
    window.location.href = 'login.html';              // manda de volta para o login
    document.documentElement.style.display = 'none';  // esconde a página enquanto redireciona
}
```

E o logout simplesmente remove a chave:

```javascript
// pages/dashboard.html
function fazerLogout() {
    if (confirm('Tem a certeza que deseja sair?')) {   // pede confirmação ao utilizador
        sessionStorage.removeItem('loggedInUser');     // apaga a sessão (deixa de estar autenticado)
        window.location.href = 'login.html';           // volta para o login
    }
}
```

### Itens do menu (localStorage) — cópia de segurança

Sempre que a ementa é carregada da API, guardamos uma **cópia local** no `localStorage`. Assim,
se a API falhar numa próxima visita, o site continua a mostrar a última ementa conhecida em vez
de ficar vazio.

```javascript
// js/menu-dashboard.js — ao carregar do servidor, guarda cópia
todosItensMenu = data.dados || data || [];     // usa data.dados; se não existir, data; senão, lista vazia
try {
    // Guarda a ementa como texto no localStorage (cópia de segurança para offline)
    localStorage.setItem('menuItems_100sabores', JSON.stringify(todosItensMenu));
} catch (e) {
    // Pode falhar se o armazenamento estiver cheio → apenas regista o erro, não quebra o site
    console.error('Erro ao guardar menu no localStorage:', e);
}
```

```javascript
// js/menu-public.js — plano B se a API falhar
async function loadPublicMenu() {
    try {
        const response = await fetch('../api/menu.php?public=true'); // pede a ementa ao servidor
        const data = await response.json();                          // converte a resposta em objeto
        if (data.sucesso && data.dados) {                            // veio a ementa?
            renderMenuFromData(data.dados);                          // mostra-a na página
            localStorage.setItem('menuItems_100sabores', JSON.stringify(data.dados)); // atualiza a cópia local
        }
    } catch (err) {                                                  // a API falhou (ex.: sem internet)
        // Plano B: usar a última cópia guardada no localStorage
        const saved = localStorage.getItem('menuItems_100sabores');
        if (saved) renderMenuFromData(JSON.parse(saved));            // JSON.parse: texto → objeto
    }
}
```

Como bónus, isto permite **sincronização entre separadores**: se o dashboard alterar um prato,
a página pública (aberta noutro separador) atualiza-se sozinha através do evento `storage`:

```javascript
// js/menu-public.js
// O evento 'storage' dispara quando OUTRO separador altera o localStorage
window.addEventListener('storage', function (e) {
    if (e.key === 'menuItems_100sabores' && e.newValue) {  // foi a ementa que mudou?
        renderMenuFromData(JSON.parse(e.newValue));        // volta a desenhar com os dados novos
    }
});
```

> **Nota:** a página pública guarda a **ementa** em `localStorage` porque é informação pública e
> queremos que persista. Os **dados do funcionário** ficam em `sessionStorage` porque são
> sensíveis e devem desaparecer ao fechar o separador. Em nenhum dos casos a password é guardada
> no navegador.

---

## 9. Gestão de reservas: editar, cancelar, confirmar e eliminar

Na dashboard, cada reserva tem botões de ação. Todas as operações comunicam com a API
`reservas.php` através de `fetch()` e, no fim, recarregam a lista para refletir a mudança.

### Confirmar / Cancelar (alterar estado)

Confirmar e cancelar são a **mesma operação** — mudam apenas o campo `status` da reserva —, por
isso partilham a função `alterarStatus()`:

```javascript
// js/reservas-dashboard.js
// id = qual a reserva; novoStatus = novo estado ('confirmada' ou 'cancelada')
window.alterarStatus = async function(id, novoStatus) {
    const response = await fetch('../api/reservas.php', {
        method: 'PUT',                                     // PUT = atualizar algo existente
        headers: { 'Content-Type': 'application/json' },   // avisa que enviamos JSON
        body: JSON.stringify({ id, status: novoStatus })   // corpo: só o id e o novo estado
    });

    const result = await response.json();                  // lê a resposta do servidor
    if (result.sucesso) {                                  // correu bem?
        // Escolhe a mensagem certa consoante o estado
        const msgs = { confirmada: 'Reserva confirmada!', cancelada: 'Reserva cancelada.' };
        mostrarToast(msgs[novoStatus] || 'Estado atualizado!',
                     novoStatus === 'cancelada' ? 'warning' : 'success'); // cor do aviso
        await carregarReservas();   // recarrega a lista para mostrar a mudança
    } else {
        mostrarToast('Erro: ' + (result.erro || 'Falha ao atualizar estado'), 'error');
    }
};
```

Do lado do servidor, o `atualizarStatus()` grava o novo estado e, se for um **cancelamento**,
liberta automaticamente a mesa que estava associada:

```php
// classes/Reserva.php — atualizarStatus()
public function atualizarStatus($id, $novo_status) {
    // Atualiza o estado da reserva e a data/hora da alteração
    $stmt = $this->conn->prepare("UPDATE reservas
            SET status = :status, atualizada_em = CURRENT_TIMESTAMP
            WHERE id = :id");
    $stmt->bindParam(':status', $novo_status);   // novo estado
    $stmt->bindParam(':id', $id);                // qual a reserva

    if ($stmt->execute()) {                      // a atualização correu bem?
        // Se foi um cancelamento (e a reserva existe), liberta a mesa que estava ocupada
        if ($novo_status === 'cancelada' && $this->obterPorId($id)) {
            $this->liberarMesa($id);   // mesa fica livre outra vez para outras reservas
        }
        return true;
    }
    return false;   // falhou a atualização
}
```

### Editar reserva

A edição usa também o método `PUT`, mas envia os **dados completos**. A API deteta que vêm
campos de dados (e não só o estado) e faz uma atualização completa, tentando depois **reatribuir
uma mesa** caso a data, hora ou número de pessoas tenham mudado:

```php
// api/reservas.php — atualizarReserva() (edição completa)
// Deteta se o pedido traz dados completos (edição) e não apenas o estado (confirmar/cancelar)
$temDadosCompletos = isset($input['nome']) || isset($input['data']) || isset($input['num_pessoas']) /* ... */;

if ($temDadosCompletos) {
    // Atualiza TODOS os campos da reserva de uma vez
    $stmt = $db->prepare("UPDATE reservas SET
                nome = :nome, email = :email, telefone = :telefone,
                data_reserva = :data_reserva, hora_reserva = :hora_reserva,
                num_pessoas = :num_pessoas, observacoes = :observacoes,
                status = :status, atualizada_em = NOW()
              WHERE id = :id");
    // ... a seguir liga-se cada :marcador ao valor (todos limpos com htmlspecialchars/strip_tags) ...
    $stmt->execute();

    // Como a data/hora/nº de pessoas podem ter mudado, volta a escolher a mesa mais adequada
    Mesa::associarMesaAutomatica($db, (int)$id, (int)$input['num_pessoas'], $input['data'], $input['hora']);

    json_response(['sucesso' => true, 'mensagem' => 'Reserva atualizada com sucesso']);
}
```

### Eliminar reserva

Eliminar apaga a reserva definitivamente da base de dados. Usa o método `DELETE`, passando o
`id` no URL:

```javascript
// js/reservas-dashboard.js
window.eliminarReserva = async function(id) {
    // Método DELETE; o id da reserva vai no próprio URL (?id=...)
    const response = await fetch('../api/reservas.php?id=' + id, { method: 'DELETE' });
    const result = await response.json();          // lê a resposta

    if (result.sucesso) {                          // apagou?
        mostrarToast('Reserva eliminada com sucesso!', 'success');
        await carregarReservas();                  // recarrega a lista (a reserva já não aparece)
    } else {
        mostrarToast('Erro: ' + (result.erro || 'Falha ao eliminar'), 'error');
    }
};
```

```php
// classes/Reserva.php — eliminar()
public function eliminar($id) {
    // Apaga definitivamente a linha da reserva com aquele id
    $stmt = $this->conn->prepare("DELETE FROM reservas WHERE id = :id");
    $stmt->bindParam(':id', $id);        // liga o marcador ao id (protege de SQL Injection)
    return $stmt->execute();             // true = apagou; false = falhou
}
```

> **Editar** e **eliminar** mesas seguem a mesma lógica na classe `Mesa`, com uma diferença
> importante: eliminar uma mesa **não a apaga** — apenas a marca como inativa (`ativa = 0`), para
> não perder o histórico de reservas antigas ligadas a essa mesa.

---

*Documento gerado como material de apoio à PAP — Restaurante 100 Sabores.*
