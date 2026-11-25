# Contexto Completo do Projeto — Restaurante "100 Sabores"

> Documento de contexto único e detalhado do projeto (PAP).
> Serve para dar a qualquer pessoa (ou a uma IA) uma visão completa de **o que é o projeto, como está organizado, como funciona e quais as suas regras de negócio**, sem ter de ler o código todo.
>
> Última atualização: 2026-07-05

---

## 1. O que é o projeto

O **100 Sabores** é um **website completo de gestão e reservas online** para um restaurante de alta gastronomia (Lisboa). Foi desenvolvido como **Prova de Aptidão Profissional (PAP)**.

O sistema tem **duas grandes vertentes**:

1. **Site público** — onde o cliente:
   - conhece o restaurante (página inicial, sobre, contactos)
   - consulta a **ementa** (dinâmica, vinda da base de dados)
   - vê a **carta de vinhos** e a **galeria** (páginas estáticas)
   - faz **reservas online** com confirmação automática por email
   - compra **vouchers-presente**
   - envia mensagens pelo **formulário de contacto**
   - alterna entre **Português e Inglês** (i18n, sem recarregar a página)

2. **Área privada / Dashboard** — onde os funcionários (após login):
   - gerem **reservas** (confirmar, cancelar, editar, eliminar)
   - gerem a **ementa** (CRUD completo de pratos, com upload de imagens)
   - consultam **estatísticas** (reservas de hoje/semana, ocupação, mesas mais usadas)

---

## 2. Stack tecnológica

| Camada | Tecnologia |
|---|---|
| Frontend | HTML5, CSS3, JavaScript (Vanilla, `fetch`) |
| Backend | PHP (Programação Orientada a Objetos) |
| Base de dados | MySQL (acesso via **PDO**) |
| Email | **PHPMailer** + SMTP autenticado (Gmail) |
| Gestão de dependências | **Composer** |
| Servidor local | **XAMPP** (Apache + MySQL) |
| Segurança | `.env`, `.htaccess`, `password_hash`, prepared statements, sessões seguras |
| Tradução | Sistema i18n próprio (PT/EN) + estudo de LibreTranslate |

**Caminho local do projeto:** `c:\xampp\htdocs\PAP-100_Sabores`
**URL local:** `http://localhost/PAP-100_Sabores/pages/index.html`

---

## 3. Arquitetura (em camadas)

O projeto está organizado por **separação de responsabilidades**:

```
[ Frontend ]              [ APIs (PHP) ]            [ Lógica de Negócio ]      [ Base de Dados ]
pages/*.html      ──►     api/login.php       ──►   classes/Funcionario.php
js/*.js (fetch)          api/reservas.php           classes/Reserva.php   ──►  MySQL
css/*.css                api/menu.php               classes/Mesa.php
                         api/contacto.php           classes/MenuItem.php
                         api/voucher.php            classes/Voucher.php
                         api/upload_imagem.php      classes/EmailSender.php
                              │
                              ▼
                       config/database.php  ──► lê o .env (credenciais seguras)
```

**Ideia-chave:** o HTML/JS trata da apresentação, as **APIs** recebem os pedidos (devolvem JSON), as **classes** contêm a lógica de negócio, e `config/database.php` centraliza a ligação à base de dados e as constantes do negócio.

---

## 4. Estrutura de pastas e ficheiros

```
PAP-100_Sabores/
├── .env                     # Credenciais reais (BD + SMTP) — NÃO vai para o Git
├── .env.example             # Modelo do .env (sem senhas) — vai para o Git
├── .htaccess                # Bloqueia acesso ao .env, desativa listagem de pastas
├── .gitignore
├── composer.json / .lock    # Dependências (PHPMailer, phpdotenv)
├── index.php                # Entrada (redireciona para pages/)
│
├── api/                     # Endpoints REST (recebem pedidos, devolvem JSON)
│   ├── login.php            # Autenticação de funcionários
│   ├── logout.php
│   ├── reservas.php         # CRUD de reservas + associação automática de mesa
│   ├── menu.php             # CRUD de pratos da ementa
│   ├── contacto.php         # Guarda mensagens de contacto + email
│   ├── voucher.php          # Geração e envio de vouchers-presente
│   ├── upload_imagem.php    # Upload de imagens de pratos
│   ├── eventos.php          # ⚠️ Incompleto / em desenvolvimento
│   └── run_migration.php    # Utilitário de migração de BD
│
├── classes/                 # Lógica de negócio (POO)
│   ├── Funcionario.php      # Login, hash de senha, logs de acesso, permissões (RBAC)
│   ├── Reserva.php          # Validação, capacidade, criação de reservas
│   ├── Mesa.php             # Pesquisa e atribuição automática de mesa
│   ├── MenuItem.php         # CRUD de pratos
│   ├── Voucher.php          # Lógica de vouchers-presente
│   └── EmailSender.php      # Envio de emails via PHPMailer/SMTP
│
├── config/
│   └── database.php         # Classe Database (PDO) + classe Config (regras de negócio)
│
├── pages/                   # Páginas do site
│   ├── index.html           # Página inicial
│   ├── menu.html            # Ementa (dinâmica)
│   ├── Reservas.html        # Formulário de reservas
│   ├── contacto.html
│   ├── carta_vinhos.html    # Estática
│   ├── Galeria.html         # Estática
│   ├── Eventos.html         # ⚠️ Ligada a api/eventos.php (incompleto)
│   ├── voucher.html         # Compra de voucher
│   ├── voucher-ver.php      # Voucher imprimível
│   ├── politica_privacidade.html
│   ├── login.html           # Login da dashboard
│   ├── dashboard.html       # Painel de administração
│   ├── esqueci_senha.html / nova_senha.html   # Recuperação de senha
│   └── funcionarios.php
│
├── js/
│   ├── script.js            # Efeitos visuais (parallax, scroll reveal, menu mobile)
│   ├── i18n.js              # Tradução PT/EN
│   ├── menu-public.js       # Carregamento dinâmico da ementa pública
│   ├── reservas-form.js     # Formulário público de reservas
│   ├── reservas-dashboard.js# Dashboard de reservas (tabela, gráficos, ações)
│   ├── menu-dashboard.js    # CRUD de pratos no dashboard
│   ├── dashboard-tabs.js    # Navegação por abas + modais
│   └── voucher-form.js      # Formulário de voucher
│
├── css/                     # Organizado por componentes e por página
│   ├── estilos.css          # Estilos globais
│   ├── components/          # footer, login, menu-login
│   └── pages/               # um subdiretório de CSS por página
│
├── sql/
│   └── database_completo.sql# Esquema completo da base de dados
│
├── docs/                    # Documentação do projeto (este ficheiro incluído)
├── dev/                     # Scripts de teste/desenvolvimento (não fazem parte do produto)
├── imagens/                 # Imagens (pratos, galeria, etc.)
└── vendor/                  # Dependências do Composer (gerada por composer install)
```

---

## 5. Base de dados (MySQL — `restaurante_100_sabores`)

O esquema completo está em `sql/database_completo.sql`. **11 tabelas:**

| Tabela | Função |
|---|---|
| `reservas` | Núcleo do negócio: cada reserva de cliente |
| `mesas` | Mesas físicas (número, capacidade, localização, ativa) |
| `mesas_historico` | Regista atribuições de mesa a reservas |
| `funcionarios` | Utilizadores do dashboard (com senha em hash) |
| `menu_itens` | Pratos da ementa |
| `menu_categorias` | Categorias de pratos |
| `contactos` | Mensagens do formulário de contacto |
| `vouchers` | Vouchers-presente |
| `configuracoes` | Configurações do sistema em BD |
| `logs_acesso` | Registo de logins e tentativas falhadas (com IP) |
| `password_reset_tokens` | Tokens de recuperação de senha |

### Tabela `reservas` (principal)
```
id (PK), nome, email, telefone
data_reserva, hora_reserva, num_pessoas, observacoes
status → 'pendente' | 'confirmada' | 'cancelada'
mesa_id → FK para mesas.id (pode ser NULL)
duracao_minutos → coluna com DEFAULT 120 no SQL, mas a classe Reserva
                  insere sempre Config::DURACAO_PADRAO_RESERVA = 210 (3h30)
data_criacao, atualizada_em
```
- `mesa_id` é preenchido **automaticamente** pelo sistema (o cliente não escolhe a mesa).
- Estado inicial = `pendente`; pode passar a `confirmada` ou `cancelada`.
- Ao cancelar, `mesa_id` é limpo → mesa libertada.

### Tabela `mesas`
```
id (PK), numero, capacidade, localizacao
ativa → 0 ou 1 (soft delete), observacoes
```
- Cada mesa tem capacidade fixa.
- `ativa = 0` desativa sem apagar (preserva histórico).
- Capacidade total do restaurante = soma das mesas ativas (**80 lugares**).
- O sistema procura sempre a **menor mesa que caiba** o grupo.

### Tabela `funcionarios` (RBAC)
```
id (PK), nome, email, telefone
senha → hash (password_hash / bcrypt)
cargo → 'admin' | 'gerente' | 'chef' | 'garcom'
nivel_acesso → 1 (admin) | 2 | 3 (staff), ativo, data_criacao
```
| Cargo | Permissões previstas |
|-------|-----------|
| admin | Acesso total |
| gerente | Reservas, Menu, Relatórios |
| chef | Menu, ver reservas |
| garcom | Ver reservas, Mesas |

> ⚠️ **Nota honesta:** o método `verificarPermissao()` existe mas **não é chamado** no código atual — qualquer funcionário autenticado tem, na prática, acesso total. É um ponto de melhoria conhecido.

### Nota técnica sobre chaves estrangeiras
As FKs (ex.: `reservas.mesa_id`) **não têm constraint `FOREIGN KEY` enforce** no MySQL — a integridade é garantida por **código PHP** nas classes.

---

## 6. Funcionalidade-estrela: Reservas com atribuição automática de mesa

**O cliente NÃO escolhe mesa.** O sistema atribui-a sozinho. Este é o coração do projeto.

### Fluxo completo
1. **Cliente preenche o formulário** (`Reservas.html` + `reservas-form.js`) → `POST /api/reservas.php` com: `nome, email, telefone, data, hora, num_pessoas, observacoes`.

2. **Validação no servidor** (`Reserva::validar()`):
   | Regra | Descrição |
   |-------|-----------|
   | Nome | ≥ 2 caracteres |
   | Email | formato válido |
   | Telefone | ≥ 9 caracteres |
   | Data | não pode ser no passado |
   | Antecedência mínima | 2 horas |
   | Antecedência máxima | 30 dias |
   | Hora | dentro do horário de funcionamento |
   | Pessoas | entre 1 e 12 |

3. **Verificação de capacidade total** (`Reserva::verificarDisponibilidade()`):
   - Soma `num_pessoas` das reservas `pendente`/`confirmada` com **sobreposição de horário** (usando `duracao_minutos`).
   - Se `(total_existente + novas) > 80` → erro *"Não há vagas disponíveis para este horário"*. **Sem overbooking.**

4. **Atribuição automática de mesa** (`Reserva::criar()` + `Mesa::obterMesasDisponiveis()`):
   - `SELECT` mesas onde `capacidade >= num_pessoas` e `ativa = 1`, ordenadas por capacidade **ascendente**.
   - Para cada mesa candidata, verifica conflito de horário na mesma data/hora.
   - Escolhe a **primeira** (menor capacidade que ainda cabe). Ex.: grupo de 3 → mesa de 4, não de 8.
   - Se nenhuma disponível → `mesa_id = NULL` (reserva fica "por atribuir").

5. **Inserção** com `status = 'pendente'`; se atribuiu mesa, regista em `mesas_historico` (motivo `automatica`).

6. **Resposta JSON** ao cliente com `sucesso`, `id`, objeto `mesa` (ou null) e confirmação de emails enviados.

### Regras resumidas
| Aspeto | Regra |
|--------|-------|
| Escolha de mesa | Automática |
| Prioridade | Menor mesa disponível que caiba |
| Capacidade máxima | 80 pessoas por horário |
| Antecedência | Mín. 2h / Máx. 30 dias |
| Duração padrão | 210 min (3h30) — `Config::DURACAO_PADRAO_RESERVA` |
| Conflito | Não permite sobreposição de horários na mesma mesa |
| Estado inicial | `pendente` |

### Horários de funcionamento (definidos em `Config`)
- **Semana (seg–sex):** almoço 12:00–13:30, jantar 19:00–21:30
- **Fim de semana (sáb–dom):** apenas jantar 18:00–22:00
- Slots gerados de **30 em 30 minutos** por `Config::horariosDisponiveis()`.

---

## 7. Outras funcionalidades importantes

### 7.1 Confirmação automática por email (SMTP / PHPMailer)
- Ao reservar são enviados **2 emails**: confirmação ao cliente + notificação ao restaurante.
- Templates HTML com as cores do restaurante.
- Usa **PHPMailer com SMTP autenticado (Gmail)**, credenciais no `.env` — **não** usa a função `mail()` (que não funciona no XAMPP).
- Ficheiro: `classes/EmailSender.php`.

### 7.2 Dashboard de gestão
- Login protegido → gestão de reservas + ementa.
- **Reservas:** tabela com paginação (10/página), filtros (nome/email/estado/data), gráfico de ocupação semanal (Canvas 2D), estatísticas animadas, ações confirmar/cancelar/editar/eliminar.
- **Menu:** CRUD completo de pratos, filtros por categoria, badges de tipo (Normal/Especial/Vegetariano/Vegan), upload de imagens, sincronização com página pública via `localStorage`.

### 7.3 Vouchers-presente
- Cliente escolhe valor (**25€, 50€ ou 100€**), indica quem oferece/recebe e uma data.
- O sistema **gera um código único** (ex.: `VC-A1B2C3D4`, via `uniqid` + `md5`).
- Envia por email um **voucher imprimível** (`pages/voucher-ver.php`) com código e instruções de pagamento por **IBAN**; o restaurante recebe notificação.
- Regras (valores permitidos, validade de **6 meses**, IBAN) centralizadas em `Config`.
- Já **ligado à base de dados**: tabela `vouchers` + classe `Voucher.php` (permite validar o resgate).

### 7.4 Formulário de contacto
- Grava a mensagem na tabela `contactos` e envia email de agradecimento automático.

### 7.5 Multilíngue (i18n PT/EN)
- `js/i18n.js` com dicionário `pt`/`en`, traduz elementos com atributo `data-i18n`, guarda a preferência no `localStorage`, sem recarregar a página.
- Existe também um estudo de tradução automática via **LibreTranslate local + cache em BD** (requer servidor próprio).

### 7.6 Recuperação de senha
- Por **token** (tabela `password_reset_tokens`), páginas `esqueci_senha.html` / `nova_senha.html`.

### 7.7 Upload de imagens
- `api/upload_imagem.php` com validação de extensão e renomeação segura dos ficheiros.

---

## 8. Segurança (transversal)

- **Senhas** encriptadas com `password_hash()` (bcrypt) e verificadas com `password_verify()` — nunca em texto simples.
- **PDO com prepared statements** em todas as queries → impede **SQL Injection**.
- **`htmlspecialchars()` / `strip_tags()`** nos dados → mitiga **XSS**.
- **Credenciais no `.env`** (fora do código e fora do Git).
- **`.htaccess`** bloqueia acesso direto ao `.env` e desativa listagem de pastas.
- **Cookies de sessão** com `httponly` e `samesite=Strict`.
- **`logs_acesso`** regista logins e tentativas falhadas com IP.

### Padrões de projeto usados
- **Classe `Database` (Singleton/Factory)** — centraliza a ligação PDO; mudar de BD implica mexer num só ficheiro.
- **Classe `Config`** — todas as regras de negócio (capacidade, horários, antecedências, valores de voucher, IBAN, contactos) num só lugar.
- **Função `carregarEnv()` em PHP puro** — lê o `.env` linha a linha sem depender de bibliotecas externas (plano B resiliente).

---

## 9. Configuração e como correr

### Requisitos
- XAMPP (Apache + MySQL) a correr.
- Composer instalado (para gerar `vendor/` com `composer install`).

### Variáveis de ambiente (`.env`)
```
DB_HOST=localhost
DB_NAME=restaurante_100_sabores
DB_USER=root
DB_PASS=
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=<email>
SMTP_PASS=<senha de app de 16 letras>
SMTP_FROM=restaurante@100sabores.pt
```

### Passos
1. Ligar XAMPP (Apache + MySQL a verde).
2. Importar `sql/database_completo.sql` no phpMyAdmin (BD `restaurante_100_sabores`).
3. Correr `composer install` (gera `vendor/`).
4. Criar o `.env` a partir do `.env.example` com credenciais reais.
5. Abrir `http://localhost/PAP-100_Sabores/pages/index.html`.

---

## 10. Estado atual e pontos fracos conhecidos

**A funcionar / demonstrável:**
- Site público, ementa dinâmica, reservas ponta a ponta com email, dashboard, vouchers, contacto, i18n.

**Incompleto / a evitar em demonstração:**
- ⚠️ **Eventos** (`api/eventos.php` + `Eventos.html`) — incompleto/em desenvolvimento.

**Pontos fracos conhecidos (honestidade técnica):**
1. **Permissões RBAC não aplicadas** — `verificarPermissao()` existe mas não é chamado; qualquer login tem acesso total.
2. **Autenticação de algumas APIs simplificada** (ex.: menu) — em produção, sessão/tokens em todas.
3. **CORS aberto** (`Allow-Origin: *`) para facilitar desenvolvimento local.
4. **Frontend em HTML estático + backend PHP via API** (opção de separação deliberada).
5. **Scripts de teste na pasta `dev/`** — não fazem parte do produto final.
6. **Sem confirmação por SMS** — só email.
7. **Duração da reserva:** a coluna `duracao_minutos` tem `DEFAULT 120` no SQL, mas a classe `Reserva` insere sempre `Config::DURACAO_PADRAO_RESERVA = 210` (3h30). O valor efetivo é **210 min**; o default do SQL nunca é usado na prática (a documentação já está alinhada com 210).

**Evolução futura:** app móvel, pagamento online de sinal, fidelização/pontos, integração Google Calendar, resgate automático de vouchers na dashboard, painel de estatísticas mais rico.

---

## 11. Documentação relacionada (pasta `docs/`)

| Ficheiro | Conteúdo |
|---|---|
| `GUIA_APRESENTACAO_PAP.md` | Guia de apresentação da PAP (pitch, código a mostrar, perguntas do júri) |
| `banco-dados.md` | Modelo de dados detalhado |
| `logica-reservas.md` | Fluxo completo de reservas |
| `JAVASCRIPT.md` | Documentação de cada ficheiro JS |
| `ESTRUTURA.md` | Explicação de pastas/ficheiros (.env, Composer, .htaccess) |
| `README.md` / `Estudo.md` | Notas de arquitetura e segurança |
| `excertos_codigo_importantes.md` | Trechos de código importantes |
| `configurar_emails.html` / `instrucoes.html` | Instruções de configuração |
| `CONTEXTO_PROJETO.md` | **Este documento** — contexto global |

---

*Fim do documento de contexto.*
