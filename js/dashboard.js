/**
 * Dashboard JavaScript - Gestao do restaurante ligada a API
 */

const API_BASE = {
    reservas: '../api/reservas.php',
    menu: '../api/menu.php'
};

let currentUser = null;
let reservations = [];
let menuItems = [];
let statistics = null;
let tables = [];

document.addEventListener('DOMContentLoaded', function() {
    checkAuthentication();
    initializeDashboard();
});

function checkAuthentication() {
    const loggedInUser = sessionStorage.getItem('loggedInUser');
    if (!loggedInUser) {
        window.location.href = 'login.html';
        return;
    }

    try {
        currentUser = JSON.parse(loggedInUser);
        const userNameEl = document.getElementById('userName');
        const userRoleEl = document.getElementById('userRole');

        if (userNameEl) {
            userNameEl.textContent = currentUser.nome_completo || currentUser.nome_utilizador || 'Funcionario';
        }

        if (userRoleEl) {
            userRoleEl.textContent = (currentUser.cargo || 'funcionario').toUpperCase();
        }
    } catch (e) {
        console.error('Erro ao ler dados do utilizador:', e);
        sessionStorage.removeItem('loggedInUser');
        window.location.href = 'login.html';
    }
}

async function initializeDashboard() {
    try {
        setupMenuForm();
        await Promise.all([
            loadReservations(),
            loadMenuItems(),
            loadStatistics(),
            loadTables()
        ]);
        updateDashboardCounters();
        showReservations();
    } catch (error) {
        console.error('Erro ao inicializar dashboard:', error);
        showNotification('Nao foi possivel carregar todos os dados do dashboard.', 'error');
    }
}

function getAuthHeaders(includeJson = false) {
    const headers = {};

    if (includeJson) {
        headers['Content-Type'] = 'application/json';
    }

    headers['Authorization'] = 'Bearer ' + (sessionStorage.getItem('authToken') || 'demo');
    return headers;
}

async function fetchJson(url, options = {}) {
    const response = await fetch(url, options);
    const text = await response.text();
    let data = {};

    try {
        data = text ? JSON.parse(text) : {};
    } catch (error) {
        throw new Error('Resposta invalida da API');
    }

    if (!response.ok) {
        throw new Error(data.erro || data.mensagem || `HTTP ${response.status}`);
    }

    return data;
}

function setupMenuForm() {
    const menuForm = document.getElementById('menuForm');
    if (!menuForm) {
        return;
    }

    menuForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const payload = {
            nome: document.getElementById('menuItemName').value.trim(),
            preco: parseFloat(document.getElementById('menuItemPrice').value),
            descricao: document.getElementById('menuItemDescription').value.trim(),
            categoria: document.getElementById('menuItemCategory').value,
            tipo: document.getElementById('menuItemType').value,
            disponivel: true
        };

        try {
            await fetchJson(API_BASE.menu, {
                method: 'POST',
                headers: getAuthHeaders(true),
                body: JSON.stringify(payload)
            });

            menuForm.reset();
            await loadMenuItems();
            updateDashboardCounters();
            showNotification('Item adicionado com sucesso!', 'success');
        } catch (error) {
            console.error(error);
            showNotification(error.message || 'Erro ao adicionar item.', 'error');
        }
    });
}

// ============================================
// RESERVAS
// ============================================

async function loadReservations() {
    try {
        const result = await fetchJson(API_BASE.reservas);
        reservations = result.reservas || [];
        renderReservationsTable();
        updateDashboardCounters();
    } catch (error) {
        console.error('Erro ao carregar reservas:', error);
        reservations = [];
        renderReservationsTable();
        updateDashboardCounters();
        throw error;
    }
}

window.loadReservations = loadReservations;

function renderReservationsTable() {
    const tbody = document.getElementById('reservationsTableBody');
    if (!tbody) {
        return;
    }

    tbody.innerHTML = '';

    if (reservations.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;">Nenhuma reserva encontrada</td></tr>';
        return;
    }

    reservations.forEach(reservation => {
        const row = document.createElement('tr');
        const statusClass = getStatusClass(reservation.status);
        const statusText = getStatusText(reservation.status);

        row.innerHTML = `
            <td>${reservation.id || 'N/A'}</td>
            <td><strong>${escapeHtml(reservation.nome || 'N/A')}</strong><br><small>${escapeHtml(reservation.email || '')}</small></td>
            <td>${formatDate(reservation.data_reserva)}</td>
            <td>${formatTime(reservation.hora_reserva)}</td>
            <td><span class="badge">${reservation.num_pessoas || 0} pessoas</span></td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            <td class="action-buttons">
                <button class="btn-icon" onclick="viewReservationDetails(${reservation.id})" title="Ver detalhes">Ver</button>
                <button class="btn-icon btn-confirm" onclick="confirmReservation(${reservation.id})" title="Confirmar" ${reservation.status === 'confirmada' ? 'disabled' : ''}>OK</button>
                <button class="btn-icon btn-cancel" onclick="cancelReservation(${reservation.id})" title="Cancelar" ${reservation.status === 'cancelada' ? 'disabled' : ''}>X</button>
                <button class="btn-icon btn-edit" onclick="editReservation(${reservation.id})" title="Editar">Editar</button>
                <button class="btn-icon btn-delete" onclick="deleteReservation(${reservation.id})" title="Excluir">Apagar</button>
            </td>
        `;

        tbody.appendChild(row);
    });
}

async function updateReservationStatus(id, status) {
    await fetchJson(API_BASE.reservas, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status })
    });

    await Promise.all([loadReservations(), loadStatistics(), loadTables()]);
    updateDashboardCounters();
}

async function confirmReservation(id) {
    if (!confirm('Confirmar esta reserva?')) {
        return;
    }

    try {
        await updateReservationStatus(id, 'confirmada');
        showNotification('Reserva confirmada!', 'success');
    } catch (error) {
        console.error(error);
        showNotification(error.message || 'Erro ao confirmar reserva.', 'error');
    }
}

async function cancelReservation(id) {
    if (!confirm('Cancelar esta reserva?')) {
        return;
    }

    try {
        await updateReservationStatus(id, 'cancelada');
        showNotification('Reserva cancelada!', 'success');
    } catch (error) {
        console.error(error);
        showNotification(error.message || 'Erro ao cancelar reserva.', 'error');
    }
}

async function deleteReservation(id) {
    if (!confirm('Tem certeza que deseja EXCLUIR permanentemente esta reserva?')) {
        return;
    }

    try {
        await fetchJson(API_BASE.reservas + `?id=${id}`, { method: 'DELETE' });
        await Promise.all([loadReservations(), loadStatistics(), loadTables()]);
        updateDashboardCounters();
        showNotification('Reserva excluida!', 'success');
    } catch (error) {
        console.error(error);
        showNotification(error.message || 'Erro ao excluir reserva.', 'error');
    }
}

function viewReservationDetails(id) {
    const reservation = reservations.find(r => Number(r.id) === Number(id));
    if (!reservation) {
        showNotification('Reserva nao encontrada.', 'error');
        return;
    }

    const details = `
        <div class="reservation-details">
            <h3>Detalhes da Reserva #${reservation.id}</h3>
            <div class="detail-row"><strong>Nome:</strong> ${escapeHtml(reservation.nome || 'N/A')}</div>
            <div class="detail-row"><strong>Email:</strong> ${escapeHtml(reservation.email || 'N/A')}</div>
            <div class="detail-row"><strong>Telefone:</strong> ${escapeHtml(reservation.telefone || 'N/A')}</div>
            <div class="detail-row"><strong>Data:</strong> ${formatDate(reservation.data_reserva)}</div>
            <div class="detail-row"><strong>Hora:</strong> ${formatTime(reservation.hora_reserva)}</div>
            <div class="detail-row"><strong>Pessoas:</strong> ${reservation.num_pessoas || 0}</div>
            <div class="detail-row"><strong>Status:</strong> ${getStatusText(reservation.status)}</div>
            <div class="detail-row"><strong>Mesa:</strong> ${reservation.mesa_numero ? 'Mesa ' + escapeHtml(String(reservation.mesa_numero)) : 'Nao atribuida'}</div>
            <div class="detail-row"><strong>Turno:</strong> ${escapeHtml(reservation.turno_nome || 'N/A')}</div>
            ${reservation.observacoes ? `<div class="detail-row"><strong>Observacoes:</strong><br><p>${escapeHtml(reservation.observacoes)}</p></div>` : ''}
            <div class="detail-row"><strong>Criada em:</strong> ${formatDateTime(reservation.data_criacao)}</div>
        </div>
    `;

    showModal('Detalhes da Reserva', details);
}

function editReservation(id) {
    const reservation = reservations.find(r => Number(r.id) === Number(id));
    if (!reservation) {
        showNotification('Reserva nao encontrada.', 'error');
        return;
    }

    const form = `
        <form id="editReservationForm" class="modal-form">
            <div class="form-group">
                <label>Status:</label>
                <select id="edit_status">
                    <option value="pendente" ${reservation.status === 'pendente' ? 'selected' : ''}>Pendente</option>
                    <option value="confirmada" ${reservation.status === 'confirmada' ? 'selected' : ''}>Confirmada</option>
                    <option value="cancelada" ${reservation.status === 'cancelada' ? 'selected' : ''}>Cancelada</option>
                </select>
            </div>
            <p>Edicao completa ainda nao existe na API. Aqui podes alterar o estado da reserva.</p>
            <div class="form-actions">
                <button type="button" onclick="closeModal()" class="btn-secondary">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
    `;

    showModal('Atualizar Reserva', form);

    document.getElementById('editReservationForm').addEventListener('submit', async e => {
        e.preventDefault();
        const status = document.getElementById('edit_status').value;

        try {
            await updateReservationStatus(id, status);
            closeModal();
            showNotification('Reserva atualizada!', 'success');
        } catch (error) {
            console.error(error);
            showNotification(error.message || 'Erro ao atualizar reserva.', 'error');
        }
    });
}

// ============================================
// MENU
// ============================================

async function loadMenuItems() {
    try {
        const result = await fetchJson(API_BASE.menu, {
            headers: getAuthHeaders()
        });

        menuItems = result.dados || [];
        renderMenuItems();
        updateDashboardCounters();
    } catch (error) {
        console.error('Erro ao carregar menu:', error);
        menuItems = [];
        renderMenuItems();
        throw error;
    }
}

function renderMenuItems() {
    const container = document.getElementById('menuItemsList');
    if (!container) {
        return;
    }

    container.innerHTML = '';

    if (menuItems.length === 0) {
        container.innerHTML = '<p style="text-align: center; padding: 40px;">Nenhum item no menu</p>';
        return;
    }

    menuItems.forEach(item => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'menu-item-row';
        itemDiv.innerHTML = `
            <div class="menu-item-info">
                <h4>${escapeHtml(item.nome || 'N/A')}</h4>
                <p>${escapeHtml(item.descricao || 'Sem descricao')}</p>
                <small>
                    <span class="badge">${getCategoryText(item.categoria)}</span>
                    <span class="badge">${getTypeText(item.tipo)}</span>
                    <span class="price">EUR ${parseFloat(item.preco || 0).toFixed(2)}</span>
                    ${item.disponivel != 0 ? '<span class="badge badge-success">Disponivel</span>' : '<span class="badge badge-danger">Indisponivel</span>'}
                </small>
            </div>
            <div class="menu-item-actions">
                <button class="btn-icon btn-edit" onclick="editMenuItem(${item.id})" title="Editar">Editar</button>
                <button class="btn-icon ${item.disponivel != 0 ? 'btn-disable' : 'btn-enable'}" onclick="toggleMenuItemAvailability(${item.id})" title="Alternar disponibilidade">${item.disponivel != 0 ? 'Desativar' : 'Ativar'}</button>
                <button class="btn-icon btn-delete" onclick="deleteMenuItem(${item.id})" title="Excluir">Apagar</button>
            </div>
        `;

        container.appendChild(itemDiv);
    });
}

async function deleteMenuItem(id) {
    if (!confirm('Tem certeza que deseja EXCLUIR permanentemente este item do menu?')) {
        return;
    }

    try {
        await fetchJson(API_BASE.menu + `?id=${id}`, {
            method: 'DELETE',
            headers: getAuthHeaders()
        });

        await loadMenuItems();
        showNotification('Item excluido!', 'success');
    } catch (error) {
        console.error(error);
        showNotification(error.message || 'Erro ao excluir item.', 'error');
    }
}

async function toggleMenuItemAvailability(id) {
    const item = menuItems.find(m => Number(m.id) === Number(id));
    if (!item) {
        showNotification('Item nao encontrado.', 'error');
        return;
    }

    const payload = {
        nome: item.nome,
        preco: parseFloat(item.preco),
        descricao: item.descricao,
        categoria: item.categoria,
        tipo: item.tipo,
        disponivel: item.disponivel == 0 ? 1 : 0
    };

    try {
        await fetchJson(API_BASE.menu + `?id=${id}`, {
            method: 'PUT',
            headers: getAuthHeaders(true),
            body: JSON.stringify(payload)
        });

        await loadMenuItems();
        showNotification('Disponibilidade atualizada!', 'success');
    } catch (error) {
        console.error(error);
        showNotification(error.message || 'Erro ao atualizar item.', 'error');
    }
}

function editMenuItem(id) {
    const item = menuItems.find(m => Number(m.id) === Number(id));
    if (!item) {
        showNotification('Item nao encontrado.', 'error');
        return;
    }

    const form = `
        <form id="editMenuItemForm" class="modal-form">
            <div class="form-group">
                <label>Nome do Prato:</label>
                <input type="text" id="edit_item_nome" value="${escapeHtml(item.nome || '')}" required>
            </div>
            <div class="form-group">
                <label>Preco:</label>
                <input type="number" id="edit_item_preco" value="${item.preco || 0}" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label>Categoria:</label>
                <select id="edit_item_categoria" required>
                    <option value="entradas" ${item.categoria === 'entradas' ? 'selected' : ''}>Entradas</option>
                    <option value="principais" ${item.categoria === 'principais' ? 'selected' : ''}>Principais</option>
                    <option value="sobremesas" ${item.categoria === 'sobremesas' ? 'selected' : ''}>Sobremesas</option>
                    <option value="bebidas" ${item.categoria === 'bebidas' ? 'selected' : ''}>Bebidas</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tipo:</label>
                <select id="edit_item_tipo" required>
                    <option value="normal" ${item.tipo === 'normal' ? 'selected' : ''}>Normal</option>
                    <option value="especial" ${item.tipo === 'especial' ? 'selected' : ''}>Especial</option>
                    <option value="vegetariano" ${item.tipo === 'vegetariano' ? 'selected' : ''}>Vegetariano</option>
                    <option value="vegan" ${item.tipo === 'vegan' ? 'selected' : ''}>Vegan</option>
                </select>
            </div>
            <div class="form-group full-width">
                <label>Descricao:</label>
                <textarea id="edit_item_descricao" rows="4" required>${escapeHtml(item.descricao || '')}</textarea>
            </div>
            <div class="form-group">
                <label><input type="checkbox" id="edit_item_disponivel" ${item.disponivel != 0 ? 'checked' : ''}> Disponivel</label>
            </div>
            <div class="form-actions">
                <button type="button" onclick="closeModal()" class="btn-secondary">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
    `;

    showModal('Editar Item do Menu', form);

    document.getElementById('editMenuItemForm').addEventListener('submit', async e => {
        e.preventDefault();

        const payload = {
            nome: document.getElementById('edit_item_nome').value.trim(),
            preco: parseFloat(document.getElementById('edit_item_preco').value),
            descricao: document.getElementById('edit_item_descricao').value.trim(),
            categoria: document.getElementById('edit_item_categoria').value,
            tipo: document.getElementById('edit_item_tipo').value,
            disponivel: document.getElementById('edit_item_disponivel').checked ? 1 : 0
        };

        try {
            await fetchJson(API_BASE.menu + `?id=${id}`, {
                method: 'PUT',
                headers: getAuthHeaders(true),
                body: JSON.stringify(payload)
            });

            closeModal();
            await loadMenuItems();
            showNotification('Item atualizado!', 'success');
        } catch (error) {
            console.error(error);
            showNotification(error.message || 'Erro ao atualizar item.', 'error');
        }
    });
}

// ============================================
// ESTATISTICAS E MESAS
// ============================================

async function loadStatistics() {
    try {
        const result = await fetchJson(API_BASE.reservas + '?action=estatisticas');
        statistics = result.estatisticas || null;
        renderStatistics();
        updateDashboardCounters();
    } catch (error) {
        console.error('Erro ao carregar estatisticas:', error);
        statistics = null;
        renderStatistics();
        throw error;
    }
}

async function loadTables() {
    try {
        const result = await fetchJson(API_BASE.reservas + '?action=mesas');
        tables = result.mesas || [];
        updateDashboardCounters();
    } catch (error) {
        console.error('Erro ao carregar mesas:', error);
        tables = [];
    }
}

function renderStatistics() {
    const container = document.getElementById('statisticsContent');
    if (!container) {
        return;
    }

    if (!statistics) {
        container.innerHTML = '<p>Nao foi possivel carregar estatisticas.</p>';
        return;
    }

    const weekly = Array.isArray(statistics.ocupacao_semanal) ? statistics.ocupacao_semanal : [];
    const weeklySummary = weekly.length
        ? weekly.map(day => `${escapeHtml(day.data)}: ${escapeHtml(String(day.ocupacao_percentual || 0))}%`).join('<br>')
        : 'Sem dados';

    container.innerHTML = `
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-value">${statistics.total_reservas ?? 0}</div><div class="stat-label">Total de Reservas</div></div>
            <div class="stat-card"><div class="stat-value">${statistics.reservas_hoje ?? 0}</div><div class="stat-label">Reservas Hoje</div></div>
            <div class="stat-card"><div class="stat-value">${statistics.reservas_semana ?? 0}</div><div class="stat-label">Reservas Esta Semana</div></div>
            <div class="stat-card"><div class="stat-value">${menuItems.length}</div><div class="stat-label">Itens no Menu</div></div>
            <div class="stat-card"><div class="stat-value">${menuItems.filter(item => item.disponivel != 0).length}</div><div class="stat-label">Itens Disponiveis</div></div>
            <div class="stat-card"><div class="stat-value">${tables.length}</div><div class="stat-label">Mesas Ativas</div></div>
        </div>
        <div class="settings-card" style="margin-top: 24px;">
            <h3>Ocupacao Semanal</h3>
            <p>${weeklySummary}</p>
        </div>
    `;
}

// ============================================
// SECOES
// ============================================

function showReservations() {
    document.getElementById('reservationsSection').style.display = 'block';
    document.getElementById('menuManagementSection').style.display = 'none';
    document.getElementById('statisticsSection').style.display = 'none';
    document.getElementById('settingsSection').style.display = 'none';
    loadReservations();
}

function showMenuManagement() {
    document.getElementById('reservationsSection').style.display = 'none';
    document.getElementById('menuManagementSection').style.display = 'block';
    document.getElementById('statisticsSection').style.display = 'none';
    document.getElementById('settingsSection').style.display = 'none';
    loadMenuItems();
}

function showStatistics() {
    document.getElementById('reservationsSection').style.display = 'none';
    document.getElementById('menuManagementSection').style.display = 'none';
    document.getElementById('statisticsSection').style.display = 'block';
    document.getElementById('settingsSection').style.display = 'none';
    loadStatistics();
}

function showSettings() {
    document.getElementById('reservationsSection').style.display = 'none';
    document.getElementById('menuManagementSection').style.display = 'none';
    document.getElementById('statisticsSection').style.display = 'none';
    document.getElementById('settingsSection').style.display = 'block';
}

function exportReservations() {
    if (reservations.length === 0) {
        showNotification('Nao ha reservas para exportar', 'warning');
        return;
    }

    let csv = 'ID,Nome,Email,Telefone,Data,Hora,Pessoas,Status\n';
    reservations.forEach(r => {
        csv += `${r.id || ''},${r.nome || ''},${r.email || ''},${r.telefone || ''},${r.data_reserva || ''},${formatTime(r.hora_reserva)},${r.num_pessoas || 0},${r.status || ''}\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `reservas_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    showNotification('Reservas exportadas com sucesso!', 'success');
}

function viewMenuPreview() {
    window.open('menu.html', '_blank');
}

function updateDashboardCounters() {
    const reservationsCountEl = document.getElementById('reservationsCount');
    if (reservationsCountEl) {
        reservationsCountEl.textContent = reservations.length;
    }

    const menuItemsCountEl = document.getElementById('menuItemsCount');
    if (menuItemsCountEl) {
        menuItemsCountEl.textContent = menuItems.length;
    }

    const today = new Date().toISOString().split('T')[0];
    const todayReservations = reservations.filter(r => r.data_reserva === today && r.status !== 'cancelada');
    const confirmedToday = todayReservations.filter(r => r.status === 'confirmada');
    const mesasOcupadas = new Set(confirmedToday.map(r => r.mesa_id).filter(Boolean));

    const totalReservationsEl = document.getElementById('totalReservations');
    if (totalReservationsEl) {
        totalReservationsEl.textContent = todayReservations.length;
    }

    const totalGuestsEl = document.getElementById('totalGuests');
    if (totalGuestsEl) {
        totalGuestsEl.textContent = todayReservations.reduce((sum, r) => sum + (parseInt(r.num_pessoas, 10) || 0), 0);
    }

    const pendingEl = document.getElementById('pendingReservations');
    if (pendingEl) {
        pendingEl.textContent = reservations.filter(r => r.status === 'pendente').length;
    }

    const availableTablesEl = document.getElementById('availableTables');
    if (availableTablesEl) {
        const totalMesas = tables.length;
        availableTablesEl.textContent = Math.max(0, totalMesas - mesasOcupadas.size);
    }
}

function logout() {
    if (confirm('Tem certeza que deseja sair?')) {
        sessionStorage.removeItem('loggedInUser');
        sessionStorage.removeItem('authToken');
        window.location.href = 'login.html';
    }
}

// ============================================
// AUXILIARES
// ============================================

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-PT');
}

function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('pt-PT');
}

function formatTime(timeString) {
    if (!timeString) return 'N/A';
    return String(timeString).slice(0, 5);
}

function getStatusText(status) {
    const statusMap = {
        pendente: 'Pendente',
        confirmada: 'Confirmada',
        cancelada: 'Cancelada'
    };
    return statusMap[status] || status;
}

function getStatusClass(status) {
    const classMap = {
        pendente: 'status-pending',
        confirmada: 'status-confirmed',
        cancelada: 'status-cancelled'
    };
    return classMap[status] || '';
}

function getCategoryText(categoria) {
    const map = {
        entradas: 'Entradas',
        principais: 'Principais',
        sobremesas: 'Sobremesas',
        bebidas: 'Bebidas'
    };
    return map[categoria] || categoria;
}

function getTypeText(tipo) {
    const map = {
        normal: 'Normal',
        especial: 'Especial',
        vegetariano: 'Vegetariano',
        vegan: 'Vegan'
    };
    return map[tipo] || tipo;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.add('show');
    }, 10);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function showModal(title, content) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2>${title}</h2>
                <button class="modal-close" onclick="closeModal()">x</button>
            </div>
            <div class="modal-body">
                ${content}
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    setTimeout(() => modal.classList.add('show'), 10);
}

function closeModal() {
    const modal = document.querySelector('.modal-overlay');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => modal.remove(), 300);
    }
}

window.showReservations = showReservations;
window.showMenuManagement = showMenuManagement;
window.showStatistics = showStatistics;
window.showSettings = showSettings;
window.exportReservations = exportReservations;
window.viewMenuPreview = viewMenuPreview;
window.logout = logout;
window.confirmReservation = confirmReservation;
window.cancelReservation = cancelReservation;
window.deleteReservation = deleteReservation;
window.viewReservationDetails = viewReservationDetails;
window.editReservation = editReservation;
window.editMenuItem = editMenuItem;
window.deleteMenuItem = deleteMenuItem;
window.toggleMenuItemAvailability = toggleMenuItemAvailability;
window.closeModal = closeModal;
window.updateDashboardCounters = updateDashboardCounters;
