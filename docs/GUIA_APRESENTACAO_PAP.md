# 🎤 Guia de Apresentação — PAP "100 Sabores"

> Documento de preparação para a simulação de apresentação.
> Sistema de gestão e reservas online para o restaurante **100 Sabores**.

---

## 1. Resumo do projeto (o "pitch" de 60 segundos)

> *"O 100 Sabores é um website completo para um restaurante de alta gastronomia. Tem duas vertentes: um **site público** onde o cliente conhece o restaurante, vê a ementa, a carta de vinhos e a galeria, faz reservas online com confirmação automática por email e pode ainda **comprar vouchers-presente**; e uma **área privada (dashboard)** onde os funcionários gerem as reservas e a ementa em tempo real. Foi desenvolvido em **PHP, MySQL, JavaScript e HTML/CSS**, com uma arquitetura organizada em classes e APIs, e com preocupações reais de **segurança** e **escalabilidade**."*

Decora esta ideia. É o que deves dizer logo no início para enquadrar o júri.

---

## 2. Arquitetura — como o sistema está organizado

Explica que separaste o projeto em camadas (isto impressiona porque mostra organização profissional):

```
[ Frontend ]              [ APIs (PHP) ]            [ Lógica de Negócio ]      [ Base de Dados ]
pages/*.html      ──►     api/login.php       ──►   classes/Funcionario.php
js/*.js (fetch)          api/reservas.php           classes/Reserva.php   ──►  MySQL
css/*.css                api/menu.php               classes/Mesa.php           (8 tabelas)
                         api/contacto.php           classes/MenuItem.php
                         api/upload_imagem.php      classes/EmailSender.php
                              │
                              ▼
                       config/database.php  ──► lê o .env (credenciais seguras)
```

**Frase-chave para dizer:** *"Separei as responsabilidades: o HTML/JS trata da apresentação, as APIs recebem os pedidos, as classes contêm a lógica e o config trata da ligação à base de dados. Se quiser mudar a base de dados, só mexo num ficheiro."*

### Tabelas da base de dados (11 tabelas)
`reservas`, `mesas`, `mesas_historico`, `funcionarios`, `menu_itens`, `menu_categorias`, `contactos`, `vouchers`, `configuracoes`, `logs_acesso`, `password_reset_tokens`
→ Mostra que pensaste relacionalmente (chaves estrangeiras, ENUMs, índices).

---

## 3. ⭐ Funcionalidades a dar MAIS ênfase

Estas são as que tens de mostrar a funcionar e explicar com detalhe. São o coração da PAP.

### 🥇 3.1 — Sistema de Reservas com associação AUTOMÁTICA de mesa
**Esta é a tua funcionalidade-estrela.** Não é apenas guardar uma reserva: o sistema escolhe sozinho a mesa ideal.

- O cliente preenche o formulário → o sistema valida → verifica a disponibilidade → **escolhe automaticamente a melhor mesa** → envia email de confirmação.
- O algoritmo escolhe a mesa de **menor capacidade que ainda comporta o grupo** (economia de espaço — uma mesa de 8 não é desperdiçada num casal).
- Ficheiro: [classes/Mesa.php](../classes/Mesa.php) → método `obterMesasDisponiveis()` e `associarMesaAutomatica()`.
- Verifica **sobreposição de horários** (uma reserva ocupa a mesa durante 210 minutos = 3h30), não só o dia.

**Frase de defesa:** *"O sistema não permite overbooking. Cada reserva ocupa a mesa durante 2 horas, e eu verifico matematicamente se há conflito de horários antes de associar."*

### 🥈 3.2 — Confirmação automática por email (SMTP / PHPMailer)
- Ao reservar, são enviados **2 emails**: confirmação ao cliente + notificação ao restaurante.
- Templates HTML profissionais (com as cores do restaurante).
- Ficheiro: [classes/EmailSender.php](../classes/EmailSender.php).
- Usa **PHPMailer** com **SMTP autenticado** (Gmail) e credenciais protegidas no `.env`.

**Frase de defesa:** *"Não uso a função `mail()` simples, que não funciona no XAMPP. Uso o protocolo SMTP com autenticação segura através do PHPMailer."*

### 🥉 3.3 — Dashboard de gestão (área de funcionários)
- Login protegido → gestão de reservas (confirmar/cancelar/editar) + gestão da ementa (CRUD completo com upload de imagens).
- Estatísticas: reservas de hoje, da semana, ocupação semanal, mesas mais usadas.
- Ficheiros: [api/reservas.php](../api/reservas.php), [api/menu.php](../api/menu.php), [classes/MenuItem.php](../classes/MenuItem.php).

### 🎁 3.5 — Compra de vouchers-presente (NOVA funcionalidade)
Permite a um cliente **oferecer uma experiência** no restaurante a outra pessoa.

- O cliente escolhe um valor (25€, 50€ ou 100€), indica quem oferece / quem recebe e uma data → o sistema **gera um código único** (ex: `VC-A1B2C3D4`).
- É enviado por email um **voucher imprimível** (página [pages/voucher-ver.php](../pages/voucher-ver.php)) com o código e as instruções de pagamento por **IBAN**. O restaurante também recebe uma notificação.
- Ficheiros: [api/voucher.php](../api/voucher.php), [js/voucher-form.js](../js/voucher-form.js), método `enviarVoucher()` em [classes/EmailSender.php](../classes/EmailSender.php).
- As regras (valores permitidos, valor mínimo, validade de 6 meses, IBAN) estão **centralizadas na classe `Config`**.

**Frase de defesa:** *"O voucher tem um código de controlo único gerado com `uniqid` + `md5`, e só é ativado após confirmação manual do pagamento por transferência. Os valores permitidos e o IBAN estão centralizados na configuração, não espalhados pelo código."*

> **Nota (evolução recente):** os vouchers **já são guardados na base de dados** (tabela `vouchers` + classe [classes/Voucher.php](../classes/Voucher.php)), o que permite validar o resgate. O passo seguinte seria um ecrã de gestão/resgate de vouchers na própria dashboard.

### 3.4 — Segurança (transversal — menciona sempre)
- Senhas encriptadas com `password_hash()` / `password_verify()` (nunca em texto simples).
- **PDO com prepared statements** em TODAS as queries → impede **SQL Injection**.
- `htmlspecialchars()` / `strip_tags()` nos dados → impede **XSS**.
- Credenciais no `.env` (fora do código e fora do Git).
- Cookies de sessão com `httponly` e `samesite=Strict` → proteção contra XSS e CSRF.
- `.htaccess` bloqueia acesso direto ao `.env`.
- `logs_acesso`: regista todos os logins (e tentativas falhadas) com IP.

---

## 4. 💻 Código importante para mencionares (com explicação)

O júri vai pedir para mostrares código. Tem estes trechos prontos e sabe explicá-los.

### 4.1 — Autenticação segura → [classes/Funcionario.php](../classes/Funcionario.php#L21)
```php
if (password_verify($senha, $row['senha'])) {
    $this->atualizarUltimoAcesso($row['id']);
    $this->registrarLogAcesso($row['id'], 'login', 'Login bem-sucedido');
```
**Explica:** *"A senha nunca é comparada diretamente. O `password_verify` compara o que o utilizador escreveu com o hash encriptado guardado na BD. Mesmo que alguém roube a base de dados, não consegue ler as senhas."*

### 4.2 — Prepared Statements (anti-SQL Injection) → [classes/Mesa.php](../classes/Mesa.php#L60)
```php
$stmt = $this->conn->prepare($query);
$stmt->bindParam(':capacidade_min', $capacidade_minima, PDO::PARAM_INT);
```
**Explica:** *"O `prepare` envia a estrutura da query primeiro, sem os valores. Os valores entram depois pelo `bindParam`. Assim, mesmo que alguém escreva código malicioso no formulário, ele é tratado como texto e não como comando SQL."*

### 4.3 — Algoritmo de mesa disponível → [classes/Mesa.php](../classes/Mesa.php#L140)
```php
$query = "SELECT * FROM mesas
        WHERE capacidade >= :capacidade_min AND ativa = 1
        ORDER BY capacidade ASC, RAND()";
```
**Explica:** *"Procuro mesas que comportem o grupo, ordenadas da menor para a maior. Depois filtro só as que não têm conflito de horário naquela data/hora."*

### 4.4 — Carregamento seguro do .env (PHP puro) → [config/database.php](../config/database.php#L16)
```php
function carregarEnv($dir) { ... }  // lê o .env linha a linha
```
**Explica:** *"Escrevi a minha própria função para ler o `.env` em PHP puro, sem depender de bibliotecas externas. Isto torna o sistema mais resiliente e mantém as credenciais fora do código-fonte."*

### 4.5 — Classe Config (regras de negócio centralizadas) → [config/database.php](../config/database.php#L105)
```php
const CAPACIDADE_MAXIMA = 80;
const HORAS_ANTECEDENCIA_MINIMA = 2;
const DURACAO_PADRAO_RESERVA = 210; // 3h30
```
**Explica:** *"Todas as regras do restaurante estão num só sítio. Se o dono mudar a capacidade de 80 para 100, mudo uma linha e o sistema inteiro acompanha."*

### 4.6 — Validação no servidor → [classes/Reserva.php](../classes/Reserva.php#L272)
```php
if ($data_reserva < $hoje) { $erros[] = "Data não pode ser no passado"; }
// + verifica horário de funcionamento, antecedência mínima, nº de pessoas...
```
**Explica:** *"Nunca confio só na validação do JavaScript (que o utilizador pode contornar). Valido sempre no servidor: datas no passado, horário de funcionamento (12h-15h / 19h-23h), e capacidade."*

### 4.7 — Geração do código do voucher → [api/voucher.php](../api/voucher.php#L75)
```php
$codigo = 'VC-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
// ... e valida contra valores permitidos definidos no Config:
if (!in_array($valor, Config::VOUCHER_VALORES, true)) { $erros[] = 'Valor inválido'; }
```
**Explica:** *"Gero um código de controlo único combinando `uniqid` (baseado no tempo, com entropia) e `md5`. E só aceito os valores de voucher definidos no `Config` — o utilizador não pode inventar um valor pelo formulário."*

---

## 5. 🗣️ Perguntas prováveis do júri (e respostas)

| Pergunta | Resposta curta |
|---|---|
| **Porque escolheste PHP?** | É a linguagem server-side mais usada na web, integra-se nativamente com MySQL e funciona no XAMPP que usei localmente. |
| **O que é o PDO?** | É a camada do PHP para falar com a base de dados de forma segura, com prepared statements que evitam SQL Injection. |
| **Como proteges as senhas?** | Com `password_hash()` (bcrypt). Guardo o hash, nunca a senha real. Na verificação uso `password_verify()`. |
| **Como evitas SQL Injection?** | Uso sempre prepared statements (`prepare` + `bindParam`). Os dados do utilizador nunca são concatenados diretamente na query. |
| **E XSS?** | Limpo os dados com `htmlspecialchars()` e `strip_tags()` antes de gravar. |
| **O sistema permite reservas a dobrar (overbooking)?** | Não. Verifico a sobreposição de horários e a capacidade máxima (80) antes de confirmar. |
| **O que acontece se cancelarem uma reserva?** | A mesa é libertada automaticamente (`mesa_id = NULL`) e fica disponível para outros. |
| **Como é que o frontend fala com o backend?** | Por APIs REST. O JavaScript faz `fetch()` para os ficheiros em `api/`, que devolvem JSON. |
| **O que é o .env?** | Um ficheiro com as credenciais sensíveis (senha da BD, SMTP). Está fora do código e fora do Git por segurança. |
| **Os 3 cargos (admin/chef/garçom) fazem o quê?** | Tenho um sistema de permissões: admin faz tudo, chef gere a ementa, garçom gere reservas (`verificarPermissao()`). |
| **O que farias com mais tempo? (ponto fraco)** | Ver secção 7. Responde com honestidade — mostra maturidade. |

---

## 6. ✅ Checklist ANTES da apresentação (faz hoje/amanhã de manhã)

- [ ] **Ligar o XAMPP** → Apache + MySQL a verde.
- [ ] Abrir o site: `http://localhost/PAP-100_Sabores/pages/index.html` — confirmar que carrega.
- [ ] **Testar uma reserva de ponta a ponta** e confirmar que o **email chega** (testa com antecedência — depende da internet!).
- [ ] **Testar o login** da dashboard (utilizador: `admin` / senha de teste).
- [ ] Confirmar que a base de dados está importada no phpMyAdmin (`restaurante_100_sabores`).
- [ ] Ter o **phpMyAdmin aberto** numa aba para mostrar as tabelas se pedirem.
- [ ] Ter o **VS Code aberto** nos ficheiros-chave (secção 4) prontos a mostrar.
- [ ] Preparar um **plano B**: se a internet falhar e o email não enviar, tem screenshots de um email já recebido.
- [ ] Verificar que o `.env` tem as credenciais SMTP corretas.

### Roteiro de demonstração ao vivo (ordem sugerida)
1. Página inicial → mostra o design e a navegação (e o seletor de idioma 🌐).
2. Ementa → mostra os pratos vindos da base de dados.
3. **Fazer uma reserva** → mostrar a confirmação no ecrã + abrir o email recebido. *(momento alto!)*
4. Login na dashboard → mostrar a reserva acabada de criar a aparecer na lista.
5. Confirmar/gerir a reserva + mostrar a gestão da ementa (adicionar um prato).
6. **Comprar um voucher** → mostrar o email/voucher imprimível com o código gerado. *(bom remate!)*
7. (Opcional) phpMyAdmin → mostrar a reserva gravada na tabela.

> ⚠️ **NÃO demonstres os "Eventos" ao vivo** — a API [api/eventos.php](../api/eventos.php) ainda está incompleta. Se perguntarem, diz que é uma funcionalidade em desenvolvimento. A **Galeria** e a **Carta de Vinhos** são páginas estáticas e podem ser mostradas sem problema.

---

## 7. ⚠️ Pontos fracos a conhecer (para responderes com honestidade se perguntarem)

Saber os teus próprios limites mostra maturidade técnica. Não escondas — explica o que farias a seguir:

1. **Autenticação das APIs simplificada** — algumas APIs (ex: menu) não verificam totalmente a sessão. *"Para produção, adicionaria verificação de sessão e tokens em todas as APIs."*
2. **CORS aberto (`Allow-Origin: *`)** — está aberto para facilitar o desenvolvimento local. *"Em produção restringiria ao domínio real."*
3. **Páginas HTML estáticas vs `index.php`** — o site público é em HTML; o backend é PHP via API. *"Optei por separar frontend e backend."*
4. **Ficheiros de teste/dev** na pasta `dev/` — *"São scripts que usei durante o desenvolvimento para testar; não fazem parte do produto final."*
5. **Sem confirmação por SMS** — só email. Possível melhoria futura.

**Ideias de evolução futura (diz isto se perguntarem "e a seguir?"):** app móvel, pagamento online de sinal, sistema de fidelização/pontos, integração com Google Calendar, painel de estatísticas com gráficos.

---

## 8. Funcionalidades extra (menciona se houver tempo)

- 🌐 **Site multilíngue** (i18n) — [js/i18n.js](../js/i18n.js): tradução PT/EN sem recarregar a página.
- 📧 **Formulário de contacto** que grava na BD e envia email de agradecimento automático — [api/contacto.php](../api/contacto.php).
- 🖼️ **Upload de imagens** dos pratos com validação de extensão e renomeação segura — [api/upload_imagem.php](../api/upload_imagem.php).
- 🔑 **Recuperação de senha** por token (tabela `password_reset_tokens`).
- 📊 **Estatísticas** de ocupação semanal e por mesa — classe `ReservaStats`.
- 🎁 **Vouchers-presente** com código único e voucher imprimível — [api/voucher.php](../api/voucher.php) (ver secção 3.5).
- 🍷 **Carta de vinhos** e 🖼️ **Galeria** — páginas dedicadas que enriquecem a experiência do cliente.

---

## 9. Stack tecnológica (decora isto)

| Camada | Tecnologia |
|---|---|
| Frontend | HTML5, CSS3, JavaScript (Vanilla, `fetch`) |
| Backend | PHP (Programação Orientada a Objetos) |
| Base de dados | MySQL (via PDO) |
| Email | PHPMailer + SMTP (Gmail) |
| Dependências | Composer |
| Servidor local | XAMPP (Apache + MySQL) |
| Segurança | `.env`, `.htaccess`, password_hash, prepared statements |

---

### 💪 Conselho final
Fala devagar, com confiança, e usa as palavras técnicas certas (PDO, prepared statements, SMTP, API, hash, CRUD, sessão). **Mostra entusiasmo pela funcionalidade de associação automática de mesa** — é o que distingue o teu projeto de uma simples "página de contacto". Boa sorte! 🍀
```
