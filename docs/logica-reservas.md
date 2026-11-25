# Lógica de Reservas — Restaurante 100 Sabores

## Visão geral

O cliente **não escolhe mesa**. O sistema atribui a mesa automaticamente com base na data, hora e número de pessoas, tentando ocupar primeiro as mesas mais pequenas que caibam no grupo.

---

## Fluxo completo

### 1. Cliente preenche o formulário (`pages/Reservas.html` + `js/reservas-form.js`)

Campos enviados para `POST /api/reservas.php`:
- `nome`, `email`, `telefone`
- `data` (YYYY-MM-DD)
- `hora` (HH:MM)
- `num_pessoas` (1–12)
- `observacoes` (opcional)

> Não existe nenhum campo de escolha de mesa no formulário público.

---

### 2. Validação (`classes/Reserva::validar()`)

Antes de criar a reserva, o sistema verifica:

| Regra | Descrição |
|-------|-----------|
| Nome | ≥ 2 caracteres |
| E-mail | formato válido |
| Telefone | ≥ 9 caracteres |
| Data | não pode ser no passado |
| Antecedência máxima | 30 dias |
| Antecedência mínima | 2 horas de antecedência |
| Hora | dentro do horário de funcionamento (12:00–15:00 ou 19:00–23:00) |
| Pessoas | entre 1 e 12 |

Se qualquer regra falhar, a API devolve `erros` e a reserva não é criada.

---

### 3. Verificação de capacidade total (`classes/Reserva::verificarDisponibilidade()`)

Mesmo que haja mesas livres, o sistema impede a reserva se a soma de pessoas já reservadas naquele horário exceder a capacidade máxima do restaurante.

- **Capacidade máxima:** `Config::CAPACIDADE_MAXIMA = 80`
- A query soma `num_pessoas` de todas as reservas com `status IN ('pendente', 'confirmada')` que tenham sobreposição de horário com a nova reserva, usando `duracao_minutos` (padrão 210 min = 3h30, definido em `Config::DURACAO_PADRAO_RESERVA`) para calcular conflitos.
- Se `(total_existente + num_pessoas_novas) > 80`, a API devolve erro: *"Não há vagas disponíveis para este horário"*.

---

### 4. Atribuição automática de mesa (`classes/Reserva::criar()` + `classes/Mesa::obterMesasDisponiveis()`)

Passo a passo:

1. **Obter mesas candidatas**
   - SELECT em `mesas` onde `capacidade >= num_pessoas` e `ativa = 1`
   - Ordena por capacidade **ascendente** (menor primeiro)

2. **Filtrar por disponibilidade real**
   - Para cada mesa candidata, chama `Mesa::verificarDisponibilidade()`
   - Esta função verifica se a mesa já tem reservas `pendente`/`confirmada` na mesma data/hora com sobreposição de duração.

3. **Escolher a melhor mesa**
   - A primeira mesa da lista ordenada (menor capacidade que ainda cabe)
   - Ex.: grupo de 2 → mesa de 2 lugares; grupo de 3 → mesa de 4 (a menor que couber)

4. **Associar ou não**
   - Se encontrar mesa → `reserva.mesa_id = mesa.id`
   - Se não encontrar nenhuma → `mesa_id = NULL` (reserva fica “por atribuir”)

---

### 5. Inserção da reserva

Query principal (`Reserva::criar()`):
```
INSERT INTO reservas SET
  nome = :nome,
  email = :email,
  telefone = :telefone,
  data_reserva = :data_reserva,
  hora_reserva = :hora_reserva,
  num_pessoas = :num_pessoas,
  observacoes = :observacoes,
  status = 'pendente',
  mesa_id = :mesa_id,
  duracao_minutos = :duracao_minutos
```

- `status` começa sempre como **`pendente`**.
- `data_criacao` e `atualizada_em` são preenchidas automaticamente pelo MySQL (`DEFAULT CURRENT_TIMESTAMP` / `ON UPDATE CURRENT_TIMESTAMP`).
- Se `mesa_id` foi atribuído, fica registado em `mesas_historico` com motivo `automatica`.

---

### 6. Resposta ao cliente

A API devolve JSON com:
- `sucesso: true`
- `id` da reserva
- `mesa` (objeto com `id`, `numero`, `capacidade`, `localizacao`) ou `null`
- Confirmação de e-mails enviados

Exemplo:
```json
{
  "sucesso": true,
  "id": "1",
  "mesa": {
    "id": "2",
    "numero": "2",
    "capacidade": 2,
    "localizacao": "Sala Principal"
  },
  "email_enviado": true,
  "notificacao_enviada": true
}
```

---

## Regras resumidas de negócio

| Aspeto | Regra |
|--------|-------|
| Escolha de mesa | Automática; cliente não escolhe |
| Prioridade | Menor mesa disponível que caiba |
| Capacidade máxima | 80 pessoas por horário |
| Antecedência | Mínimo 2h, Máximo 30 dias |
| Duração padrão | 210 minutos (3h30) — `Config::DURACAO_PADRAO_RESERVA` |
| Conflito de mesa | Não permite sobreposição de horários |
| Estado inicial da reserva | `pendente` |
| Mesa sem disponibilidade | Reserva criada com `mesa_id = NULL` |

---

## Ficheiros envolvidos

| Ficheiro | Função |
|----------|--------|
| `api/reservas.php` | Endpoint REST; coordena criação |
| `classes/Reserva.php` | Validação, verificação de capacidade, inserção |
| `classes/Mesa.php` | Pesquisa e validação de disponibilidade por mesa |
| `config/database.php` | `Config::CAPACIDADE_MAXIMA`, constantes de antecedência |
| `js/reservas-form.js` | Formulário público |
| `js/reservas-dashboard.js` | Dashboard; mostra mesa atribuída, sem turnos |
| `sql/database_completo.sql` | Esquema completo (sem `turnos`) |
