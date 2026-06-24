# Modelo de Dados — Restaurante 100 Sabores

## Visão Geral

O sistema usa **4 tabelas principais** com relações entre si. Tabelas em `database_simplificado.sql`.

---

## Tabelas e Estrutura

### 1. `reservas` (Core do negócio)
Armazena cada reserva feita por um cliente.

```
id (PK, AUTO_INCREMENT)
nome, email, telefone
data_reserva, hora_reserva
num_pessoas, observacoes
status → 'pendente' | 'confirmada' | 'cancelada'
mesa_id → FK para mesas.id (pode ser NULL)
duracao_minutos → padrão 120
data_criacao, atualizada_em
```

**Regras:**
- `mesa_id` é preenchido automaticamente pelo sistema (não é escolhido pelo cliente)
- O status começa como `pendente` e pode ser alterado para `confirmada` ou `cancelada`
- Quando `status = 'cancelada'`, o sistema limpa `mesa_id` (libera a mesa)

---

### 2. `mesas`
Mesas físicas do restaurante.

```
id (PK)
numero → ex: "1", "2", "A1"
capacidade → ex: 2, 4, 6, 8, 10
localizacao → ex: "Salão Principal", "Terraço", "Sala Privada"
ativa → 0 ou 1 (soft delete)
observacoes
```

**Regras:**
- Cada mesa tem capacidade fixa
- `ativa = 0` desloga a mesa sem a apagar (preserva histórico)
- A capacidade total do restaurante é a soma de todas as mesas ativas (= 80 lugares segundo Config)
- O sistema procura sempre a **menor mesa disponível que caiba** o grupo (ex: grupo de 3 → mesa de 4, não mesa de 8)

---

### 3. `funcionarios`
Utilizadores com acesso ao dashboard.

```
id (PK)
nome, email, telefone
senha → hash com password_hash()
cargo → 'admin' | 'gerente' | 'chef' | 'garcom'
nivel_acesso → 1 (admin) | 2 | 3 (staff básico)
ativo → 0 ou 1
data_criacao
```

**Regras de perfil (RBAC):**
| Cargo | Permissões |
|-------|-----------|
| admin | Acesso total a tudo |
| gerente | Reservas, Menu, Relatórios |
| chef | Menu, Visualizar reservas |
| garcom | Visualizar reservas, Mesas |

⚠️ **A verificação de permissões (`verificarPermissao()`) existe na classe mas não é chamada em lado nenhum no código atual.** Qualquer funcionário autenticado tem acesso total.

---

### 4. `contactos`
Mensagens enviadas pelo formulário de contacto.

```
id (PK)
nome, email, telefone, assunto, mensagem
data_envio, ip
```

Não tem relações com outras tabelas (é independente).

---

### 5. `mesas_historico`
Regista cada vez que uma mesa é atribuída ou reagrupada a uma reserva.

```
id (PK)
reserva_id → FK para reservas.id
mesa_id → FK para mesas.id
motivo → 'automatica' | 'manual' | 'remanejamento'
observacoes
data_registro
```

**Regras:**
- Criado automaticamente quando `Reserva::criar()` atribui mesa
- Criado quando staff muda mesa manualmente via dashboard
- Usado para saber qual mesa foi usada em cada reserva histórica

---

### 6. `menu_items` (não presente no SQL principal, criada via migration do dashboard)
Pratos do menu geridos no dashboard.

```
id (PK)
nome, categoria → 'entradas' | 'principais' | 'sobremesas' | 'bebidas'
tipo → 'normal' | 'especial' | 'vegetariano' | 'vegan'
preco, descricao
disponivel → 0 ou 1
imagem_path (opcional)
data_criacao, data_atualizacao
```

---

## Diagrama de Relações

```
┌─────────────────┐     ┌──────────────────┐     ┌──────────────┐
│   funcionarios  │     │     reservas     │────▶│    mesas     │
│                 │     │                  │     │              │
│  id (PK)        │     │  id (PK)         │     │  id (PK)     │
│  nome           │     │  nome            │     │  numero      │
│  email          │     │  email           │     │  capacidade  │
│  senha (hash)   │     │  telefone        │     │  localizacao │
│  cargo          │     │  data_reserva    │     │  ativa       │
│  nivel_acesso   │     │  hora_reserva    │     └──────────────┘
│  ativo          │     │  num_pessoas     │     FK: mesa_id (nullable)
└─────────────────┘     │  status          │
                        │  mesa_id (FK)    │────▶ mesas_historico
                        │  duracao_minutos │     └─────────────────
                        └──────────────────┘

┌─────────────────┐                     ┌──────────────────┐
│   contactos     │                     │   menu_items     │
│                 │                     │                  │
│  id (PK)        │                     │  id (PK)         │
│  nome, email... │                     │  nome, preco...  │
│  data_envio     │                     │  categoria...    │
└─────────────────┘                     └──────────────────┘
(independente)                         (independente)
```

---

## Pontos Importantes

### Foreign Keys não enforce no MySQL
As colunas `mesa_id` em `reservas` não têm `FOREIGN KEY` constraint ativa no SQL original (não foi definida). Isto significa:
- Pode existir uma reserva com `mesa_id` que não corresponde a nenhuma mesa real
- É tratado por código PHP (validações na classe `Reserva`)

### Capacidade Total
- **Total (Config::CAPACIDADE_MAXIMA = 80)**: soma de todas as mesas ativas
- O sistema valida a capacidade total em `Reserva::verificarDisponibilidade()`

### Soft Deletes
`mesas.ativa` e `funcionarios.ativo` usam `ativa = 0` para desativar sem apagar registos.
