<?php
session_start();

if (!isset($_SESSION['funcionario_logado'])) {
    header("Location: ../login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Área de Funcionários - Restaurante 100 Sabores</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../../css/pages/admin/dashboard.css">
    <link rel="stylesheet" type="text/css" href="../../css/pages/admin/dashboard-extra.css">
</head>
<body>

    <!-- Navegação -->
    <nav class="main-navigation">
        <div class="nav-container">
            <div class="nav-left">
                <a href="../index.html" class="nav-link">HOME</a>
                <a href="../menu.html" class="nav-link">EMENTA</a>
                <span class="nav-link" style="color:var(--luxury-gold);">DASHBOARD</span>
            </div>
            <a href="../index.html" class="nav-logo-center">
                <img src="../imagens/logo/Logotipo_restaurante.png" alt="100 Sabores" class="logo-img">
                <span class="logo-text">100 SABORES</span>
            </a>
            <div class="nav-right">
                <a href="../Reservas.html" class="nav-link" target="_blank">VER PÁGINA PÚBLICA</a>
                <a href="../../api/logout.php" class="nav-link">SAIR</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="parallax-bg"></div>
        <div class="parallax-overlay"></div>
        <div class="hero-content">
            <span class="hero-label">ÁREA DE GESTÃO</span>
            <h1 class="hero-title">Dashboard</h1>
            <p class="hero-tagline">Controlo total do restaurante em tempo real</p>
        </div>
    </div>

    <!-- Dashboard Content -->
    <section class="dashboard-section">
        <div class="dashboard-container">

            <!-- Tabs de Navegação -->
            <div class="dashboard-tabs">
                <button class="tab-btn active" data-tab="reservas">
                    <i class="far fa-calendar-check"></i> Reservas
                </button>
                <button class="tab-btn" data-tab="menu">
                    <i class="fas fa-utensils"></i> Gestão do Menu
                </button>
            </div>

            <!-- ============================================================
                 TAB: RESERVAS
                 ============================================================ -->
            <div id="tab-reservas" class="tab-content active">

                <!-- Cards de Estatísticas -->
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="far fa-calendar-check"></i></div>
                        <div class="stat-value" id="totalReservas">0</div>
                        <div class="stat-label">Total Reservas</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="far fa-clock"></i></div>
                        <div class="stat-value" id="reservasHoje">0</div>
                        <div class="stat-label">Hoje</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="far fa-calendar-alt"></i></div>
                        <div class="stat-value" id="reservasSemana">0</div>
                        <div class="stat-label">Esta Semana</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-chair"></i></div>
                        <div class="stat-value" id="ocupacao">0%</div>
                        <div class="stat-label">Ocupação Hoje</div>
                    </div>
                </div>

                <!-- Filtros e Busca -->
                <div class="filters-bar">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Pesquisar por nome, email ou telefone...">
                    </div>
                    <div class="filter-group">
                        <select id="filterStatus">
                            <option value="all">Todos os Estados</option>
                            <option value="pendente">Pendente</option>
                            <option value="confirmada">Confirmada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                        <select id="filterData">
                            <option value="all">Todas as Datas</option>
                            <option value="today">Hoje</option>
                            <option value="week">Esta Semana</option>
                            <option value="month">Este Mês</option>
                        </select>
                    </div>
                </div>

                <!-- Dashboard Grid -->
                <div class="dashboard-grid">

                    <!-- Painel Esquerdo: Formulário Nova Reserva -->
                    <div class="dashboard-left">
                        <div class="form-card">
                            <div class="card-header">
                                <h2><i class="far fa-calendar-plus"></i> Nova Reserva</h2>
                                <span class="live-indicator">
                                    <span class="dot"></span> Online
                                </span>
                            </div>
                            <form id="reservaForm" class="reservation-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="nome">Nome Completo *</label>
                                        <input type="text" id="nome" name="nome" required placeholder="João Silva">
                                    </div>
                                    <div class="form-group">
                                        <label for="email">E-mail *</label>
                                        <input type="email" id="email" name="email" required placeholder="joao@email.com">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="telefone">Telefone *</label>
                                        <input type="tel" id="telefone" name="telefone" required placeholder="+351 912 345 678">
                                    </div>
                                    <div class="form-group">
                                        <label for="num_pessoas">Pessoas *</label>
                                        <select id="num_pessoas" name="num_pessoas" required>
                                            <option value="">Selecione</option>
                                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?> <?= $i === 1 ? 'Pessoa' : 'Pessoas' ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="data">Data da Reserva *</label>
                                        <input type="date" id="data" name="data" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="hora">Hora *</label>
                                        <input type="time" id="hora" name="hora" required>
                                    </div>
                                </div>
                                <div class="form-group full-width">
                                    <label for="observacoes">Observações</label>
                                    <textarea id="observacoes" name="observacoes" rows="3" placeholder="Alergias, preferências especiais, ocasião especial..."></textarea>
                                </div>
                                <button type="submit" id="submitBtn" class="submit-btn">
                                    <i class="far fa-check-circle"></i> Confirmar Reserva
                                </button>
                            </form>
                        </div>

                        <!-- Horários Disponíveis -->
                        <div class="horarios-card">
                            <div class="card-header">
                                <h2><i class="far fa-clock"></i> Horários Disponíveis Hoje</h2>
                            </div>
                            <div class="horarios-grid" id="horariosGrid">
                                <!-- Gerado via JS -->
                            </div>
                        </div>
                    </div>

                    <!-- Painel Direito: Tabela de Reservas -->
                    <div class="dashboard-right">
                        <div class="table-card">
                            <div class="card-header">
                                <h2><i class="far fa-list"></i> Reservas Agendadas</h2>
                                <div class="view-toggle">
                                    <button class="view-btn active" data-view="table" title="Vista de tabela"><i class="fas fa-table"></i></button>
                                    <button class="view-btn" data-view="kanban" title="Vista kanban"><i class="fas fa-columns"></i></button>
                                </div>
                            </div>
                            <div class="table-wrapper">
                                <table class="reservations-table">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Data</th>
                                            <th>Hora</th>
                                            <th>Pessoas</th>
                                            <th>Mesa</th>
                                            <th>Turno</th>
                                            <th>Estado</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="reservasTableBody">
                                        <!-- Reservas carregadas via JS -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="pagination" id="pagination"></div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- FIM TAB RESERVAS -->

            <!-- ============================================================
                 TAB: GESTÃO DO MENU
                 ============================================================ -->
            <div id="tab-menu" class="tab-content" style="display:none;">

                <!-- Stats do Menu -->
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-utensils"></i></div>
                        <div class="stat-value" id="totalPratos">0</div>
                        <div class="stat-label">Total de Pratos</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-value" id="pratosDisponiveis">0</div>
                        <div class="stat-label">Disponíveis</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                        <div class="stat-value" id="pratosIndisponiveis">0</div>
                        <div class="stat-label">Indisponíveis</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-star"></i></div>
                        <div class="stat-value" id="pratosEspeciais">0</div>
                        <div class="stat-label">Especiais</div>
                    </div>
                </div>

                <!-- Grid: Formulário + Lista -->
                <div class="dashboard-grid">

                    <!-- Formulário Adicionar/Editar Prato -->
                    <div class="dashboard-left">
                        <div class="form-card">
                            <div class="card-header">
                                <h2><i class="fas fa-plus-circle" id="menuFormIcon"></i> <span id="menuFormTitle">Adicionar Prato</span></h2>
                            </div>
                            <form id="menuForm" class="reservation-form">
                                <input type="hidden" id="menuItemId" value="">
                                <div class="form-group">
                                    <label for="menuItemName">Nome do Prato *</label>
                                    <input type="text" id="menuItemName" required placeholder="Ex: Bacalhau à Brás">
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="menuItemCategory">Categoria *</label>
                                        <select id="menuItemCategory" required>
                                            <option value="">Selecione</option>
                                            <option value="entradas">Entradas</option>
                                            <option value="principais">Principais</option>
                                            <option value="sobremesas">Sobremesas</option>
                                            <option value="bebidas">Bebidas</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="menuItemType">Tipo *</label>
                                        <select id="menuItemType" required>
                                            <option value="normal">Normal</option>
                                            <option value="especial">Especial</option>
                                            <option value="vegetariano">Vegetariano</option>
                                            <option value="vegan">Vegan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="menuItemPrice">Preço (€) *</label>
                                    <input type="number" id="menuItemPrice" required placeholder="0.00" step="0.01" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="menuItemDescription">Descrição *</label>
                                    <textarea id="menuItemDescription" rows="3" required placeholder="Descrição do prato..."></textarea>
                                </div>
                                <div class="form-group" id="menuDisponivel" style="display:none;">
                                    <label class="checkbox-label-inline">
                                        <input type="checkbox" id="menuItemDisponivel" checked>
                                        Disponível no menu
                                    </label>
                                </div>
                                <div class="form-actions-row">
                                    <button type="submit" id="menuSubmitBtn" class="submit-btn">
                                        <i class="fas fa-plus" id="menuSubmitIcon"></i>
                                        <span id="menuSubmitText">Adicionar Prato</span>
                                    </button>
                                    <button type="button" id="menuCancelEdit" class="btn-cancel-edit" style="display:none;" onclick="cancelarEdicaoMenu()">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Filtro de Categoria -->
                        <div class="horarios-card" style="margin-top:30px;">
                            <div class="card-header">
                                <h2><i class="fas fa-filter"></i> Filtrar por Categoria</h2>
                            </div>
                            <div class="filter-cat-group">
                                <button class="filter-cat-btn active" data-cat="all">Todos</button>
                                <button class="filter-cat-btn" data-cat="entradas">Entradas</button>
                                <button class="filter-cat-btn" data-cat="principais">Principais</button>
                                <button class="filter-cat-btn" data-cat="sobremesas">Sobremesas</button>
                                <button class="filter-cat-btn" data-cat="bebidas">Bebidas</button>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Pratos -->
                    <div class="dashboard-right">
                        <div class="table-card">
                            <div class="card-header">
                                <h2><i class="fas fa-list"></i> Pratos do Menu</h2>
                                <span id="menuLoadingIndicator" style="font-size:0.8em; color:#888; display:none;">
                                    <i class="fas fa-spinner fa-spin"></i> A carregar...
                                </span>
                            </div>
                            <div id="menuItemsList">
                                <p style="text-align:center; padding:40px; color:#888;">
                                    <i class="fas fa-spinner fa-spin" style="font-size:2em; margin-bottom:10px; display:block;"></i>
                                    A carregar pratos...
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- FIM TAB MENU -->

        </div>
    </section>

    <!-- Gráfico de Ocupação -->
    <section class="chart-section" id="chartSection">
        <div class="chart-container">
            <h2 class="section-subtitle">Ocupação da Semana</h2>
            <div class="chart-wrapper">
                <canvas id="occupancyChart"></canvas>
            </div>
        </div>
    </section>

    <!-- Debug -->
    <details class="debug-panel">
        <summary>Diagnóstico &amp; Debug</summary>
        <pre id="debugOutput" style="margin-top:10px; white-space:pre-wrap; font-size:12px;"></pre>
    </details>

    <!-- Toast de Notificação -->
    <div id="successMessage" class="success-toast" style="display: none;">
        <i class="far fa-check-circle"></i>
        <span>Operação realizada com sucesso!</span>
    </div>

    <!-- ============================================================
         MODAL DE EDIÇÃO DE RESERVA
         ============================================================ -->
    <div id="modalReserva" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="far fa-edit"></i> Editar Reserva</h2>
                <button class="modal-close" onclick="fecharModalReserva()" title="Fechar">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editReservaForm" class="reservation-form">
                    <input type="hidden" id="editReservaId">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editNome">Nome Completo *</label>
                            <input type="text" id="editNome" required>
                        </div>
                        <div class="form-group">
                            <label for="editEmail">E-mail *</label>
                            <input type="email" id="editEmail" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editTelefone">Telefone *</label>
                            <input type="tel" id="editTelefone" required>
                        </div>
                        <div class="form-group">
                            <label for="editNumPessoas">Pessoas *</label>
                            <select id="editNumPessoas" required>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> <?= $i === 1 ? 'Pessoa' : 'Pessoas' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editData">Data da Reserva *</label>
                            <input type="date" id="editData" required>
                        </div>
                        <div class="form-group">
                            <label for="editHora">Hora *</label>
                            <input type="time" id="editHora" required>
                        </div>
                    </div>
                    <div class="form-group full-width">
                        <label for="editStatus">Estado</label>
                        <select id="editStatus">
                            <option value="pendente">Pendente</option>
                            <option value="confirmada">Confirmada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label for="editObservacoes">Observações</label>
                        <textarea id="editObservacoes" rows="3" placeholder="Alergias, preferências especiais..."></textarea>
                    </div>
                    <div class="modal-actions">
                        <button type="button" onclick="fecharModalReserva()" class="btn-modal-cancel">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn-modal-save">
                            <i class="fas fa-save"></i> Guardar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================================
         MODAL DE DETALHES DE RESERVA
         ============================================================ -->
    <div id="modalDetalhes" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="far fa-eye"></i> Detalhes da Reserva</h2>
                <button class="modal-close" onclick="fecharModalDetalhes()" title="Fechar">&times;</button>
            </div>
            <div class="modal-body" id="modalDetalhesBody">
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="rodape">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <img src="../imagens/logo/Logotipo_restaurante.png" alt="100 Sabores" class="footer-logo-img">
                    <span class="footer-logo-text">100 SABORES</span>
                </div>
                <p class="footer-description">
                    Cozinha portuguesa contemporânea no coração de Lisboa.
                </p>
            </div>
            <div class="footer-section">
                <h3 class="footer-title">Contactos</h3>
                <div class="footer-contact">
                    <p><i class="fas fa-map-marker-alt"></i> Largo da Trindade, Príncipe Real</p>
                    <p><i class="fas fa-phone"></i> +351 222 123 456</p>
                    <p><i class="fas fa-envelope"></i> info@100sabores.pt</p>
                </div>
            </div>
            <div class="footer-section">
                <h3 class="footer-title">Horário</h3>
                <div class="footer-hours">
                    <p>Segunda - Quinta: 12h - 22h</p>
                    <p>Sexta - Sábado: 12h - 00h</p>
                    <p>Domingo: 12h - 16h</p>
                </div>
            </div>
            <div class="footer-section">
                <h3 class="footer-title">Siga-nos</h3>
                <div class="footer-social">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-tripadvisor"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 100 Sabores. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="../../js/script.js"></script>
    <script src="../../js/reservas-dashboard.js"></script>
    <script src="../../js/menu-dashboard.js"></script>
    <script src="../../js/dashboard-tabs.js"></script>
</body>
</html>
