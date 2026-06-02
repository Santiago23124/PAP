// ============================================
// MENU DASHBOARD - JAVASCRIPT
// Gestão completa do menu: adicionar, editar,
// ativar/desativar e eliminar pratos
// ============================================

const MENU_API = '../api/menu.php';

let todosItensMenu = [];
let itensFiltradosMenu = [];
let categoriaAtualFiltro = 'all';
let modoEdicaoMenu = false;

// ============================================
// INICIALIZAÇÃO
// ============================================
//Document- Objeto de representacao do html
//addEventListener - metodo para adicionar eventos
//DOMContentLoaded - evento que ocorre quando o documento e carregado
//function - funcao que sera executada quando o evento ocorrer
document.addEventListener('DOMContentLoaded', function () {
    configurarFormularioMenu();
    configurarFiltrosCategorias();
    carregarItensMenu();
});

// ============================================
// CARREGAR ITENS DO MENU
// ============================================
//ansync- Diz ao html para nao travar o site
async function carregarItensMenu() {
    const loadingEl = document.getElementById('menuLoadingIndicator');
    if (loadingEl) loadingEl.style.display = 'inline';

    try {
        const response = await fetch(MENU_API, {
            headers: { 'Authorization': 'Bearer dashboard' }
        });
        const data = await response.json();

        if (data.sucesso !== false && (data.dados || Array.isArray(data))) {
            todosItensMenu = data.dados || data || [];
            try {
                localStorage.setItem('menuItems_100sabores', JSON.stringify(todosItensMenu));
            } catch (e) {
                console.error('Erro ao guardar menu no localStorage:', e);
            }
        } else if (data.erro) {
            console.error('Erro API menu:', data.erro);
            todosItensMenu = [];
        } else {
            todosItensMenu = [];
        }
    } catch (err) {
        console.error('Erro ao carregar menu:', err);
        todosItensMenu = [];
    }

    if (loadingEl) loadingEl.style.display = 'none';

    aplicarFiltroCategoria(categoriaAtualFiltro);
    atualizarStatsMenu();
}

// ============================================
// RENDERIZAR LISTA DE PRATOS
// ============================================

function renderizarItensMenu(itens) {
    const container = document.getElementById('menuItemsList');
    if (!container) return;

    if (!itens || itens.length === 0) {
        container.innerHTML = `
            <div style="text-align:center; padding:50px; color:#888;">
                <i class="fas fa-utensils" style="font-size:2.5em; margin-bottom:15px; display:block; opacity:0.4;"></i>
                <p style="font-size:1.1em; margin:0;">Nenhum prato encontrado</p>
                <p style="font-size:0.85em; margin-top:8px; opacity:0.7;">Adiciona o primeiro prato usando o formulário ao lado.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = '';

    itens.forEach(item => {
        const div = document.createElement('div');
        div.className = `menu-item-row${item.disponivel == 0 ? ' indisponivel' : ''}`; //${...}: Chama-se Interpolação. Permite colocar variáveis dentro de strings (textos) usando as crases (`).
        div.id = `menu-item-${item.id}`;

        const badgeTipo = getBadgeTipo(item.tipo);
        const badgeDisp = item.disponivel != 0
            ? '<span class="badge badge-disponivel">Disponível</span>'
            : '<span class="badge badge-indisponivel">Indisponível</span>';

        div.innerHTML = `
            <div class="menu-item-info">
                <h4>${escapeHtmlMenu(item.nome || 'Sem nome')}</h4>
                <p>${escapeHtmlMenu(item.descricao || 'Sem descrição')}</p>
                <div class="menu-item-badges">
                    <span class="badge badge-categoria">${getCategoriaTexto(item.categoria)}</span>
                    ${badgeTipo}
                    ${badgeDisp}
                    <span class="price-tag">€${parseFloat(item.preco || 0).toFixed(2)}</span>
                </div>
            </div>
            <div class="menu-item-actions">
                <button class="btn-menu-action btn-menu-edit" onclick="iniciarEdicaoMenu(${item.id})" title="Editar prato">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button class="btn-menu-action ${item.disponivel != 0 ? 'btn-menu-toggle-on' : 'btn-menu-toggle-off'}"
                    onclick="alternarDisponibilidadeMenu(${item.id})" title="${item.disponivel != 0 ? 'Desativar' : 'Ativar'}">
                    <i class="fas fa-${item.disponivel != 0 ? 'eye-slash' : 'eye'}"></i>
                    ${item.disponivel != 0 ? 'Desativar' : 'Ativar'}
                </button>
                <button class="btn-menu-action btn-menu-delete" onclick="eliminarItemMenu(${item.id})" title="Eliminar prato">
                    <i class="fas fa-trash-alt"></i> Eliminar
                </button>
            </div>
        `;

        container.appendChild(div);
    });
}

// ============================================
// ESTATÍSTICAS DO MENU
// ============================================

function atualizarStatsMenu() {
    const total = todosItensMenu.length;
    const disponiveis = todosItensMenu.filter(i => i.disponivel != 0).length;
    const indisponiveis = total - disponiveis;
    const especiais = todosItensMenu.filter(i => i.tipo === 'especial').length;

    definirTextoMenu('totalPratos', total);
    definirTextoMenu('pratosDisponiveis', disponiveis);
    definirTextoMenu('pratosIndisponiveis', indisponiveis);
    definirTextoMenu('pratosEspeciais', especiais);
}

// ============================================
// FILTRO DE CATEGORIAS
// ============================================

function configurarFiltrosCategorias() {
    document.querySelectorAll('.filter-cat-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-cat-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            categoriaAtualFiltro = this.dataset.cat;
            aplicarFiltroCategoria(categoriaAtualFiltro);
        });
    });
}

function aplicarFiltroCategoria(cat) {
    if (cat === 'all') {
        itensFiltradosMenu = [...todosItensMenu];
    } else {
        itensFiltradosMenu = todosItensMenu.filter(i => i.categoria === cat);
    }
    renderizarItensMenu(itensFiltradosMenu);
}

// ============================================
// FORMULÁRIO - ADICIONAR / EDITAR
// ============================================

function configurarFormularioMenu() {
    const form = document.getElementById('menuForm');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const id = document.getElementById('menuItemId').value;
        const isEdicao = id && id !== '';

        const payload = {
            nome: document.getElementById('menuItemName').value.trim(),
            preco: parseFloat(document.getElementById('menuItemPrice').value),
            descricao: document.getElementById('menuItemDescription').value.trim(),
            categoria: document.getElementById('menuItemCategory').value,
            tipo: document.getElementById('menuItemType').value,
            disponivel: isEdicao
                ? (document.getElementById('menuItemDisponivel').checked ? 1 : 0)
                : 1
        };

        if (!payload.nome || !payload.categoria || isNaN(payload.preco) || !payload.descricao) {
            mostrarToast('Preenche todos os campos obrigatórios.', 'error');
            return;
        }

        const submitBtn = document.getElementById('menuSubmitBtn');
        const submitText = document.getElementById('menuSubmitText');
        const submitIcon = document.getElementById('menuSubmitIcon');

        if (submitBtn) {
            submitBtn.disabled = true;
            if (submitIcon) submitIcon.className = 'fas fa-spinner fa-spin';
            if (submitText) submitText.textContent = 'A guardar...';
        }

        try {
            let url = MENU_API;
            let method = 'POST';

            if (isEdicao) {
                url = MENU_API + '?id=' + id;
                method = 'PUT';
            }

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer dashboard'
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (result.sucesso !== false && !result.erro) {
                mostrarToast(
                    isEdicao ? 'Prato atualizado com sucesso!' : 'Prato adicionado com sucesso!',
                    'success'
                );
                cancelarEdicaoMenu();
                await carregarItensMenu();
            } else {
                mostrarToast(result.erro || result.mensagem || 'Erro ao guardar prato.', 'error');
            }
        } catch (err) {
            console.error('Erro ao guardar prato:', err);
            mostrarToast('Erro de ligação. Tenta novamente.', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                if (submitIcon) submitIcon.className = modoEdicaoMenu ? 'fas fa-save' : 'fas fa-plus';
                if (submitText) submitText.textContent = modoEdicaoMenu ? 'Guardar Alterações' : 'Adicionar Prato';
            }
        }
    });
}

// ============================================
// INICIAR EDIÇÃO DE UM PRATO
// ============================================

window.iniciarEdicaoMenu = function (id) {
    const item = todosItensMenu.find(i => Number(i.id) === Number(id));
    if (!item) {
        mostrarToast('Prato não encontrado.', 'error');
        return;
    }

    modoEdicaoMenu = true;

    document.getElementById('menuItemId').value = item.id;
    document.getElementById('menuItemName').value = item.nome || '';
    document.getElementById('menuItemCategory').value = item.categoria || '';
    document.getElementById('menuItemType').value = item.tipo || 'normal';
    document.getElementById('menuItemPrice').value = item.preco || '';
    document.getElementById('menuItemDescription').value = item.descricao || '';
    document.getElementById('menuItemDisponivel').checked = item.disponivel != 0;

    // Mostrar campo disponível
    const dispField = document.getElementById('menuDisponivel');
    if (dispField) dispField.style.display = 'block';

    // Atualizar UI do formulário
    const titleEl = document.getElementById('menuFormTitle');
    const iconEl = document.getElementById('menuFormIcon');
    const submitText = document.getElementById('menuSubmitText');
    const submitIcon = document.getElementById('menuSubmitIcon');
    const cancelBtn = document.getElementById('menuCancelEdit');

    if (titleEl) titleEl.textContent = 'Editar Prato';
    if (iconEl) iconEl.className = 'fas fa-edit';
    if (submitText) submitText.textContent = 'Guardar Alterações';
    if (submitIcon) submitIcon.className = 'fas fa-save';
    if (cancelBtn) cancelBtn.style.display = 'flex';

    // Scroll para o formulário
    const formCard = document.querySelector('#tab-menu .form-card');
    if (formCard) {
        formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        formCard.style.border = '2px solid var(--luxury-gold)';
        setTimeout(() => {
            formCard.style.border = '2px solid rgba(184, 160, 130, 0.2)';
        }, 2000);
    }
};

// ============================================
// CANCELAR EDIÇÃO
// ============================================

window.cancelarEdicaoMenu = function () {
    modoEdicaoMenu = false;

    const form = document.getElementById('menuForm');
    if (form) form.reset();

    document.getElementById('menuItemId').value = '';

    const dispField = document.getElementById('menuDisponivel');
    if (dispField) dispField.style.display = 'none';

    const titleEl = document.getElementById('menuFormTitle');
    const iconEl = document.getElementById('menuFormIcon');
    const submitText = document.getElementById('menuSubmitText');
    const submitIcon = document.getElementById('menuSubmitIcon');
    const cancelBtn = document.getElementById('menuCancelEdit');

    if (titleEl) titleEl.textContent = 'Adicionar Prato';
    if (iconEl) iconEl.className = 'fas fa-plus-circle';
    if (submitText) submitText.textContent = 'Adicionar Prato';
    if (submitIcon) submitIcon.className = 'fas fa-plus';
    if (cancelBtn) cancelBtn.style.display = 'none';
};

// ============================================
// ALTERNAR DISPONIBILIDADE
// ============================================

window.alternarDisponibilidadeMenu = async function (id) {
    const item = todosItensMenu.find(i => Number(i.id) === Number(id));
    if (!item) return;

    const novaDisp = item.disponivel == 0 ? 1 : 0;
    const acao = novaDisp === 1 ? 'ativar' : 'desativar';

    if (!confirm(`Tens a certeza que queres ${acao} o prato "${item.nome}"?`)) return;

    try {
        const payload = {
            nome: item.nome,
            preco: parseFloat(item.preco),
            descricao: item.descricao,
            categoria: item.categoria,
            tipo: item.tipo,
            disponivel: novaDisp
        };

        const response = await fetch(MENU_API + '?id=' + id, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer dashboard'
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.sucesso !== false && !result.erro) {
            mostrarToast(
                novaDisp === 1 ? `"${item.nome}" ativado!` : `"${item.nome}" desativado!`,
                novaDisp === 1 ? 'success' : 'warning'
            );
            await carregarItensMenu();
        } else {
            mostrarToast(result.erro || 'Erro ao atualizar disponibilidade.', 'error');
        }
    } catch (err) {
        console.error('Erro:', err);
        mostrarToast('Erro de ligação.', 'error');
    }
};

// ============================================
// ELIMINAR PRATO
// ============================================

window.eliminarItemMenu = async function (id) {
    const item = todosItensMenu.find(i => Number(i.id) === Number(id));
    if (!item) return;

    if (!confirm(`Tens a certeza que queres ELIMINAR permanentemente o prato "${item.nome}"?\n\nEsta ação não pode ser desfeita.`)) return;

    try {
        const response = await fetch(MENU_API + '?id=' + id, {
            method: 'DELETE',
            headers: { 'Authorization': 'Bearer dashboard' }
        });

        const result = await response.json();

        if (result.sucesso !== false && !result.erro) {
            mostrarToast(`Prato "${item.nome}" eliminado!`, 'success');
            await carregarItensMenu();
        } else {
            mostrarToast(result.erro || 'Erro ao eliminar prato.', 'error');
        }
    } catch (err) {
        console.error('Erro:', err);
        mostrarToast('Erro de ligação.', 'error');
    }
};

// ============================================
// UTILITÁRIOS DO MENU
// ============================================

function getCategoriaTexto(cat) {
    const map = {
        entradas: 'Entradas',
        principais: 'Principais',
        sobremesas: 'Sobremesas',
        bebidas: 'Bebidas'
    };
    return map[cat] || cat || 'N/A';
}

function getBadgeTipo(tipo) {
    const classes = {
        especial: 'badge-tipo-especial',
        vegetariano: 'badge-tipo-vegetariano',
        vegan: 'badge-tipo-vegan',
        normal: 'badge-tipo-normal'
    };
    const textos = {
        especial: 'Especial',
        vegetariano: 'Vegetariano',
        vegan: 'Vegan',
        normal: 'Normal'
    };
    const cls = classes[tipo] || 'badge-tipo-normal';
    const txt = textos[tipo] || tipo || 'Normal';
    return `<span class="badge ${cls}">${txt}</span>`;
}

function escapeHtmlMenu(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function definirTextoMenu(id, valor) {
    const el = document.getElementById(id);
    if (el) el.textContent = valor;
}

// Função de toast partilhada (se não existir já definida)
if (typeof mostrarToast === 'undefined') {
    window.mostrarToast = function (msg, tipo = 'success') {
        const toast = document.getElementById('successMessage');
        if (!toast) return;

        const span = toast.querySelector('span');
        const icon = toast.querySelector('i');

        if (span) span.textContent = msg;

        // Ícones por tipo
        const icons = {
            success: 'far fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        if (icon) icon.className = icons[tipo] || icons.success;

        // Classes de cor
        toast.className = `success-toast toast-${tipo}`;
        toast.style.display = 'flex';

        setTimeout(() => {
            toast.classList.add('hide');
            setTimeout(() => {
                toast.style.display = 'none';
                toast.classList.remove('hide');
            }, 400);
        }, 3500);
    };
}
