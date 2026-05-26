# Configuração - Restaurante 100 Sabores Dashboard

## Pré-requisitos
- XAMPP (Apache + MySQL)
- PHP 7.4+

## Passos de Configuração

### 1. Iniciar XAMPP
- Iniciar Apache e MySQL no painel de controlo do XAMPP

### 2. Criar Base de Dados
Opção A - Via phpMyAdmin:
1. Aceder a `http://localhost/phpmyadmin`
2. Criar base de dados `restaurante_100_sabores`
3. Importar o ficheiro `database/schema.sql`

Opção B - Via linha de comandos:
```bash
mysql -u root -p < database/schema.sql
```

### 3. Executar Migration (Mesas e Turnos)
```bash
cd PAP-100_Sabores
php setup-database.php
```

Ou via browser:
```
http://localhost/PAP-100_Sabores/api/reservas.php?install=true
```

### 4. Verificar APIs
Testar as APIs no browser:
- `http://localhost/PAP-100_Sabores/api/reservas.php?action=mesas`
- `http://localhost/PAP-100_Sabores/api/reservas.php?action=turnos`
- `http://localhost/PAP-100_Sabores/api/menu.php`

### 5. Aceder ao Dashboard
- URL: `http://localhost/PAP-100_Sabores/pages/login.html`
- Credenciais default:
  - Utilizador: `admin`
  - Senha: `password`

## Estrutura de Ficheiros
```
PAP-100_Sabores/
├── api/           # APIs PHP (reservas, menu, login, etc.)
├── classes/       # Classes PHP (Reserva, Mesa, Turno, etc.)
├── config/        # Configuração da BD
├── database/      # Schema SQL
├── js/            # JavaScript do dashboard
├── pages/         # Páginas HTML
└── sql/           # Migrations
```

## Endpoints da API

### Reservas
- `GET /api/reservas.php` - Listar todas as reservas
- `GET /api/reservas.php?data=YYYY-MM-DD` - Reservas por data
- `POST /api/reservas.php` - Criar nova reserva
- `PUT /api/reservas.php` - Atualizar reserva
- `DELETE /api/reservas.php?id=X` - Eliminar reserva
- `GET /api/reservas.php?action=mesas` - Listar mesas
- `GET /api/reservas.php?action=turnos` - Listar turnos

### Menu
- `GET /api/menu.php` - Listar itens do menu
- `POST /api/menu.php` - Adicionar prato
- `PUT /api/menu.php?id=X` - Atualizar prato
- `DELETE /api/menu.php?id=X` - Eliminar prato