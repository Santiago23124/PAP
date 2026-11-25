# Documentação dos Ficheiros JavaScript

Estrutura do projeto:
```
js/
├── script.js                 # Efeitos visuais e animações gerais do site
├── i18n.js                   # Sistema de tradução PT/EN
├── menu-public.js            # Carregamento dinâmico da ementa pública (menu.html)
├── reservas-form.js          # Formulário de reservas na página pública
├── reservas-dashboard.js     # Dashboard de reservas (admin)
├── menu-dashboard.js         # CRUD de pratos no dashboard
├── dashboard-tabs.js         # Navegação por abas e modais do dashboard
└── voucher-form.js           # Formulário de compra de voucher-presente
```

---

## 1. `script.js` — Efeitos Visuais e Animações

**Função principal:** Efeitos visuais globais do site público.

**Responsabilidades:**
- Efeito parallax nos backgrounds (`hero-section` e `menu-hero`)
- Animações de scroll reveal (elementos aparecem ao fazer scroll)
- Menu hambúrguer para dispositivos móveis
- Fechar menu mobile ao redimensionar ou clicar fora

**Eventos:** `DOMContentLoaded`, `scroll`, `resize`, `click`

---

## 2. `i18n.js` — Internacionalização (PT/EN)

**Função principal:** Sistema de tradução entre Português e Inglês.

**Responsabilidades:**
- Dicionário de traduções em `const i18n` com chaves `pt` e `en`
- Traduz elementos com atributo `data-i18n`
- Alternância de idioma via `mudarIdioma('pt')` / `mudarIdioma('en')`
- Guarda preferência no `localStorage`

**Uso:** Incluído em todas as páginas públicas.

---

## 3. `reservas-dashboard.js` — Dashboard de Reservas (Admin)

**Função principal:** Painel administrativo de gestão de reservas.

**Responsabilidades:**
- Carregar reservas e mesas via API (`../api/reservas.php`)
- Renderizar tabela de reservas com paginação (10 por página)
- Filtros: pesquisa por nome/email/telefone, estado, data (hoje/semana/mês)
- Formulário de nova reserva com validação
- Gráfico de ocupação semanal (Canvas 2D)
- Horários disponíveis com cálculo de vagas (capacidade 80 pessoas)
- Ações: confirmar, cancelar, editar (modal), eliminar reserva
- Estatísticas animadas: total, hoje, semana, ocupação %
- Utilitários: formatação de datas, escape HTML

**Estado global:** `todasReservas`, `reservasFiltradas`, `mesasDisponiveis`

---

## 4. `dashboard-tabs.js` — Abas e Modais do Dashboard

**Função principal:** Navegação entre abas Reservas/Menu e gestão de modais.

**Responsabilidades:**
- Configurar tabs (`.tab-btn`) — alternar entre Reservas e Gestão do Menu
- Modal de edição de reserva (`modalReserva`)
- Modal de detalhes de reserva (`modalDetalhes`)
- Fecho de modais com tecla ESC ou clique fora
- Função `alterarStatusReserva()` usada nos botões do modal de detalhes
- Função `mostrarToast()` partilhada entre ficheiros

**Dependência:** Usa dados carregados por `reservas-dashboard.js` (variável `todasReservas`)

---

## 5. `menu-dashboard.js` — Gestão do Menu (CRUD Pratos)

**Função principal:** Adicionar, editar, desativar/ativar e eliminar pratos do menu.

**Responsabilidades:**
- Carregar lista de pratos via `../api/menu.php` com auth `Bearer dashboard`
- Formulário de adicionar/editar prato
- `iniciarEdicaoMenu(id)` — preencher formulário com dados do prato
- `cancelarEdicaoMenu()` — resetar formulário para modo "adicionar"
- `alternarDisponibilidadeMenu(id)` — toggle disponível/indisponível via PUT
- `eliminarItemMenu(id)` — eliminar prato via DELETE
- Filtros por categoria (Entradas, Principais, Sobremesas, Bebidas)
- Estatísticas do menu (total, disponíveis, indisponíveis, especiais)
- Guardar lista no `localStorage` após cada carregamento (sincronização com página pública)
- Badges de tipo (Normal, Especial, Vegetariano, Vegan)

**Dependência:** `MenuItem.php` (API)

---

## 6. `reservas-form.js` — Formulário de Reservas (Página Pública)

**Função principal:** Handler do formulário de reservas na página `Reservas.html`.

**Responsabilidades:**
- Submissão via POST para `../api/reservas.php`
- Validação básica de campos
- Desabilitar botão durante envio (prevenção de duplo clique)
- Exibir mensagem de sucesso com número da reserva e mesa atribuída
- Data mínima = hoje
- Feedback visual (loading spinner, toast)

---

## 7. `voucher-form.js` — Formulário de Voucher-Presente (Página Pública)

**Função principal:** Handler do formulário de compra de voucher na página `voucher.html`.

**Responsabilidades:**
- Recolher dados (`nome_recebe`, `nome_oferece`, `email`, `valor`, `mensagem`)
- Submissão via POST (JSON) para `../api/voucher.php`
- Desabilitar botão durante o envio (prevenção de duplo clique)
- Mostrar ecrã de sucesso com o **código do voucher** gerado e instruções de pagamento por IBAN
- Tratamento de erros devolvidos pela API (`result.erro` / `result.erros`)

**Dependência:** `api/voucher.php` + `classes/Voucher.php`

> **Nota:** os antigos ficheiros `dashboard.js` (dashboard legado) e `firebase-config.js`
> (template Firebase nunca integrado) foram **removidos** do projeto. O dashboard atual é
> composto por `reservas-dashboard.js` + `menu-dashboard.js` + `dashboard-tabs.js`.

---

## Fluxo de Dados (Reservas)

```
[Página Pública Reservas.html]
    │
    ├── reservas-form.js  →  POST ../api/reservas.php  →  MySQL
    │
    └── (formulário standalone, sem dependência do dashboard)

[Dashboard dashboard.html]
    │
    ├── dashboard-tabs.js  (navegação + modais)
    ├── reservas-dashboard.js  (tabela + gráficos + ações)
    └── menu-dashboard.js  (CRUD pratos)
    │
    └── API: ../api/reservas.php, ../api/menu.php  →  MySQL
```

## Fluxo de Dados (Menu)

```
[dashboard.html → tab Gestão do Menu]
    │
    └── menu-dashboard.js
            │
            ├── GET  ../api/menu.php  (listar)
            ├── POST ../api/menu.php  (criar)
            ├── PUT  ../api/menu.php?id=X  (editar/toggle)
            └── DELETE ../api/menu.php?id=X  (eliminar)
            │
            └── Guarda no localStorage → 'menuItems_100sabores'

[menu.html (página pública)]
    │
    └── loadPublicMenu()  →  GET ../api/menu.php?public=true
            │
            └── renderMenuFromData()  →  injecta HTML no DOM
```
