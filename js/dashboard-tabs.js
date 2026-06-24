
// ============================================
// DASHBOARD TABS + MODAIS DE RESERVAS
// Controlo das tabs de navegação e modais
// de edição/detalhes de reservas
// ============================================

// Código Single page Application (SPA),sem recarregamento de página, ele manipula o DOM(estrutura HTML)
// E faz pedidos AJAX/Fetch para a API PHP


//indexOf: Método que verifica se existe um intem dentro de um array se o resultado for -1 não existe, se for 0 ou mais existe
//exemplo
//const frutas = ['banana', 'maçã', 'laranja'];
//console.log(frutas.indexOf('maçã')); // retorna 1 (pois está na posição 1)
//console.log(frutas.indexOf('uva')); // retorna -1 (pois não existe)
//DOM é a página HTML vista como se fosse uma arvore o js consegue manipular cada parte do código


document.addEventListener('DOMContentLoaded', function () { // garante que o DOM está carregado antes de executar o código
    aplicarPermissoes(); // esconde tabs conforme cargo
    configurarTabs(); // configura as tabs
    configurarModalReserva(); // configura o modal de reservas
    configurarFechoModalEsc(); // configura o fecho do modal com a tecla ESC
}); // assim que a página executa as funções

// ============================================
// PERMISSÕES POR CARGO
// ============================================

// JSON.parse(...) — converte aquele texto num objeto JavaScript de verdade, com o qual já se pode trabalhar (ler .cargo, etc.)
function aplicarPermissoes() {// so corre quando for chamada
    const userData = JSON.parse(sessionStorage.getItem('loggedInUser') || '{}');// guarda os dados em local storage, e vai buscar o cargo guardado, e devolve um string
    const cargo = userData.cargo; // vai buscar o cargo dentro do objeto userData

    if (!cargo) return; // se não tiver cargo não faz nada

    const permissoes = { // dicionario cada cargo tem acesso a interfaces com conteudo diferente
        'admin': ['reservas', 'menu'],
        'chef': ['menu'],
        'garcom': ['reservas']
    };

    const tabsPermitidas = permissoes[cargo] || []; // vai buscar a const de permissoes

    document.querySelectorAll('.tab-btn').forEach(btn => { // seleciona todos os botões
        const tab = btn.dataset.tab;
        if (tabsPermitidas.indexOf(tab) === -1) {
            btn.style.display = 'none'; // esconde os tabs consoante o cargo
        }
    });

    document.querySelectorAll('.tab-content').forEach(content => {// seleciona tdos painéis
        const tabId = content.id.replace('tab-', '');
        if (tabsPermitidas.indexOf(tabId) === -1) {
            content.style.display = 'none';
        }
    });

    if (tabsPermitidas.length > 0) {
        const primeiroTab = tabsPermitidas[0];
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        const btnAlvo = document.querySelector('.tab-btn[data-tab="' + primeiroTab + '"]');
        if (btnAlvo) btnAlvo.classList.add('active');

        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.style.display = 'none';
            tab.classList.remove('active');
        });
        const tabEl = document.getElementById('tab-' + primeiroTab);
        if (tabEl) {
            tabEl.style.display = 'block';
            tabEl.classList.add('active');
        }
    }
}

// ============================================
// TABS DE NAVEGAÇÃO
// ============================================

function configurarTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const chartSection = document.getElementById('chartSection');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const tabAlvo = this.dataset.tab;

            // Atualizar botões
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Mostrar/esconder conteúdo
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
                tab.classList.remove('active');
            });

            const tabEl = document.getElementById('tab-' + tabAlvo);
            if (tabEl) {
                tabEl.style.display = 'block';
                tabEl.classList.add('active');
            }

            // Mostrar gráfico apenas no tab de reservas
            if (chartSection) {
                chartSection.style.display = tabAlvo === 'reservas' ? 'block' : 'none';
            }

            // Carregar dados do menu quando o tab é aberto
            if (tabAlvo === 'menu' && typeof carregarItensMenu === 'function') {
                carregarItensMenu();
            }

            // Re-desenhar gráfico quando voltar ao tab de reservas
            if (tabAlvo === 'reservas' && typeof desenharGraficoOcupacao === 'function') {
                setTimeout(desenharGraficoOcupacao, 100);
            }
        });
    });
}

// ============================================
// MODAL DE EDIÇÃO DE RESERVA
// ============================================

function configurarModalReserva() {
    const overlayReserva = document.getElementById('modalReserva');
    if (overlayReserva) {
        overlayReserva.addEventListener('click', function (e) {
            if (e.target === overlayReserva) {
                fecharModalReserva();
            }
        });
    }

    const overlayDetalhes = document.getElementById('modalDetalhes');
    if (overlayDetalhes) {
        overlayDetalhes.addEventListener('click', function (e) {
            // Só fecha quando o clique foi no PRÓPRIO fundo (overlay), não no
            // conteúdo. e.currentTarget é o overlay; e.target é o que foi clicado.
            if (e.target === e.currentTarget) {
                fecharModalDetalhes();
            }
        });
    }

    // Botões dentro do modal de detalhes são gerados dinamicamente via innerHTML.
    // Usamos event delegation num contentor fixo (#modalDetalhesBody) em vez de
    // onclick inline: um único listener trata de todos os botões, presentes e futuros.
    const detalhesBody = document.getElementById('modalDetalhesBody');
    if (detalhesBody) {
        detalhesBody.addEventListener('click', function (e) {
            // .closest() apanha também cliques no <i> dentro do botão.
            const btn = e.target.closest('button[data-acao]');
            if (!btn) return;

            const id = Number(btn.dataset.id);
            switch (btn.dataset.acao) {
                case 'editar':
                    fecharModalDetalhes();
                    abrirModalEditarReserva(id);
                    break;
                case 'confirmar':
                    alterarStatusReserva(id, 'confirmada');
                    fecharModalDetalhes();
                    break;
                case 'cancelar':
                    alterarStatusReserva(id, 'cancelada');
                    fecharModalDetalhes();
                    break;
            }
        });
    }

    const form = document.getElementById('editReservaForm');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
         e.preventDefault();

        const id = document.getElementById('editReservaId').value;
        if (!id) return;

        const payload = {
            id: parseInt(id),
            nome: document.getElementById('editNome').value.trim(),
            email: document.getElementById('editEmail').value.trim(),
            telefone: document.getElementById('editTelefone').value.trim(),
            data: document.getElementById('editData').value,
            hora: document.getElementById('editHora').value,
            num_pessoas: parseInt(document.getElementById('editNumPessoas').value),
            status: document.getElementById('editStatus').value,
            observacoes: document.getElementById('editObservacoes').value.trim()
        };

        const saveBtn = form.querySelector('.btn-modal-save');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> A guardar...';
        }

        try {
            const response = await fetch('../api/reservas.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (result.sucesso) {
                fecharModalReserva();
                mostrarToast('Reserva atualizada com sucesso!', 'success');
                if (typeof carregarReservas === 'function') {
                    await carregarReservas();
                }
            } else {
                mostrarToast(result.erro || 'Erro ao atualizar reserva.', 'error');
            }
        } catch (err) {
            console.error('Erro ao editar reserva:', err);
            mostrarToast('Erro de ligação. Tenta novamente.', 'error');
        } finally {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Alterações';
            }
        }
    });
}

// ============================================
// ABRIR MODAL DE EDIÇÃO
// ============================================

window.abrirModalEditarReserva = function (id) {
    try {
        const idNum = Number(id);

        if (typeof todasReservas === 'undefined' || !Array.isArray(todasReservas) || todasReservas.length === 0) {
            mostrarToast('A carregar dados das reservas...', 'info');
            fetch('../api/reservas.php')
                .then(r => r.json())
                .then(data => {
                    if (data.sucesso && Array.isArray(data.reservas)) {
                        carregarReservas();
                        setTimeout(() => abrirModalEditarReserva(idNum), 500);
                    } else {
                        mostrarToast('Erro ao carregar reservas da API.', 'error');
                    }
                })
                .catch(err => {
                    console.error('Erro fetch reservas:', err);
                    mostrarToast('Erro de ligacao ao servidor.', 'error');
                });
            return;
        }

        const reserva = todasReservas.find(r => Number(r.id) === idNum);
        if (!reserva) {
            mostrarToast('Reserva nao encontrada. A recarregar lista...', 'warning');
            carregarReservas().then(() => {
                const r2 = (Array.isArray(todasReservas))
                    ? todasReservas.find(x => Number(x.id) === idNum)
                    : null;
                if (r2) {
                    abrirModalEditarReserva(idNum);
                } else {
                    mostrarToast('Reserva nao encontrada apos recarregar.', 'error');
                }
            });
            return;
        }

        const setField = (fieldId, val) => {
            const el = document.getElementById(fieldId);
            if (el) el.value = val;
        };

        setField('editReservaId', reserva.id);
        setField('editNome', reserva.nome || '');
        setField('editEmail', reserva.email || '');
        setField('editTelefone', reserva.telefone || '');
        setField('editData', reserva.data || '');
        setField('editHora', reserva.hora || '');
        setField('editNumPessoas', reserva.num_pessoas || 1);
        setField('editStatus', reserva.status || 'pendente');
        setField('editObservacoes', reserva.observacoes || '');

        const modal = document.getElementById('modalReserva');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('show'); // .show ativa opacity:1 (sem isto o modal fica invisível)
            document.body.style.overflow = 'hidden';
        }
    } catch (error) {
        console.error('Erro ao abrir modal de edição:', error);
        mostrarToast('Erro ao abrir formulário de edição.', 'error');
    }
};

window.fecharModalReserva = function () {
    const modal = document.getElementById('modalReserva');
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
    }
    // Só restaurar o overflow se nenhum outro modal estiver aberto
    const modalDetalhes = document.getElementById('modalDetalhes');
    if (!modalDetalhes || modalDetalhes.style.display === 'none' || modalDetalhes.style.display === '') {
        document.body.style.overflow = '';
    }
};

// ============================================
// MODAL DE DETALHES
// ============================================

window.abrirModalDetalhesReserva = function (id) {
    try {
        const idNum = Number(id);

        if (typeof todasReservas === 'undefined' || !Array.isArray(todasReservas) || todasReservas.length === 0) {
            mostrarToast('A carregar dados das reservas...', 'info');
            fetch('../api/reservas.php')
                .then(r => r.json())
                .then(data => {
                    if (data.sucesso && Array.isArray(data.reservas)) {
                        carregarReservas();
                        setTimeout(() => abrirModalDetalhesReserva(idNum), 500);
                    } else {
                        mostrarToast('Erro ao carregar reservas da API.', 'error');
                    }
                })
                .catch(err => {
                    console.error('Erro fetch reservas:', err);
                    mostrarToast('Erro de ligação ao servidor.', 'error');
                });
            return;
        }

        const reserva = todasReservas.find(r => Number(r.id) === idNum);

        if (!reserva) {
            mostrarToast('Reserva nao encontrada. A recarregar lista...', 'warning');
            carregarReservas().then(() => {
                const r2 = (Array.isArray(todasReservas))
                    ? todasReservas.find(x => Number(x.id) === idNum)
                    : null;
                if (r2) {
                    abrirModalDetalhesReserva(idNum);
                } else {
                    mostrarToast('Reserva nao encontrada apos recarregar.', 'error');
                }
            });
            return;
        }

        const statusLabels = {
            pendente: '<span class="status-badge status-pendente">Pendente</span>',
            confirmada: '<span class="status-badge status-confirmada">Confirmada</span>',
            cancelada: '<span class="status-badge status-cancelada">Cancelada</span>'
        };

        const body = document.getElementById('modalDetalhesBody');
        if (body) {
            body.innerHTML = `
                <div class="detail-row">
                    <strong>Nome</strong>
                    <span>${escapeHtmlTabs(reserva.nome)}</span>
                </div>
                <div class="detail-row">
                    <strong>E-mail</strong>
                    <span>${escapeHtmlTabs(reserva.email)}</span>
                </div>
                <div class="detail-row">
                    <strong>Telefone</strong>
                    <span>${escapeHtmlTabs(reserva.telefone)}</span>
                </div>
                <div class="detail-row">
                    <strong>Data</strong>
                    <span>${formatDataTabs(reserva.data)}</span>
                </div>
                <div class="detail-row">
                    <strong>Hora</strong>
                    <span>${escapeHtmlTabs(reserva.hora)}</span>
                </div>
                <div class="detail-row">
                    <strong>Pessoas</strong>
                    <span>${reserva.num_pessoas} ${reserva.num_pessoas === 1 ? 'pessoa' : 'pessoas'}</span>
                </div>
                <div class="detail-row">
                    <strong>Mesa</strong>
                    <span>${reserva.mesa_numero ? 'Mesa ' + escapeHtmlTabs(String(reserva.mesa_numero)) : 'Não atribuída'}</span>
                </div>
                <div class="detail-row">
                    <strong>Estado</strong>
                    <span>${statusLabels[reserva.status] || escapeHtmlTabs(reserva.status)}</span>
                </div>
                ${reserva.observacoes ? `
                <div class="detail-row">
                    <strong>Observações</strong>
                    <p>${escapeHtmlTabs(reserva.observacoes)}</p>
                </div>` : ''}

                <div style="margin-top:25px; display:flex; gap:12px; flex-wrap:wrap;">
                    <button type="button" data-acao="editar" data-id="${reserva.id}" class="btn-modal-save">
                        <i class="fas fa-edit"></i> Editar Reserva
                    </button>

                    ${reserva.status !== 'confirmada' ? `
                    <button type="button" data-acao="confirmar" data-id="${reserva.id}" class="btn-modal-save" style="background:linear-gradient(135deg,#27ae60,#229954);">
                        <i class="fas fa-check"></i> Confirmar
                    </button>` : ''}

                    ${reserva.status !== 'cancelada' ? `
                    <button type="button" data-acao="cancelar" data-id="${reserva.id}" class="btn-modal-cancel" style="border-color:#e74c3c; color:#e74c3c;">
                        <i class="fas fa-times"></i> Cancelar
                    </button>` : ''}
                </div>
            `;
        }

        const modal = document.getElementById('modalDetalhes');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('show'); // .show ativa opacity:1 (sem isto o modal fica invisível)
            document.body.style.overflow = 'hidden';
        }
    } catch (error) {
        console.error('Erro ao abrir modal de detalhes:', error);
        mostrarToast('Erro ao abrir detalhes da reserva.', 'error');
    }
};

window.fecharModalDetalhes = function () {
    const modal = document.getElementById('modalDetalhes');
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
    }
    // Só restaurar o overflow se nenhum outro modal estiver aberto
    const modalReserva = document.getElementById('modalReserva');
    if (!modalReserva || modalReserva.style.display === 'none' || modalReserva.style.display === '') {
        document.body.style.overflow = '';
    }
};

// ============================================
// FECHAR MODAIS COM ESC
// ============================================

function configurarFechoModalEsc() {
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            fecharModalReserva();
            fecharModalDetalhes();
            // Garantir que o overflow é sempre restaurado ao pressionar ESC
            document.body.style.overflow = '';
        }
    });
}

// ============================================
// FUNÇÃO GLOBAL: ALTERAR STATUS RESERVA
// (Usada nos botões do modal de detalhes)
// ============================================

window.alterarStatusReserva = async function (id, novoStatus) {
    const idNum = Number(id);

    // Se ainda nao ha reservas carregadas, carregar e voltar a tentar
    if (typeof todasReservas === 'undefined' || !Array.isArray(todasReservas) || todasReservas.length === 0) {
        mostrarToast('A carregar dados das reservas...', 'info');
        await carregarReservas();
    }

    const reserva = (Array.isArray(todasReservas))
        ? todasReservas.find(r => Number(r.id) === idNum)
        : null;

    if (!reserva) {
        mostrarToast('Reserva nao encontrada apos carregar dados.', 'error');
        return;
    }

    const nome = reserva.nome;
    const labelStatus = { confirmada: 'confirmar', cancelada: 'cancelar' };

    if (!confirm(`Tens a certeza que queres ${labelStatus[novoStatus] || 'alterar'} a reserva de ${nome}?`)) return;

    try {
            const response = await fetch('../api/reservas.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status: novoStatus })
            });

        const result = await response.json();

        if (result.sucesso) {
            const msgs = {
                confirmada: 'Reserva confirmada com sucesso!',
                cancelada: 'Reserva cancelada.'
            };
            mostrarToast(msgs[novoStatus] || 'Estado atualizado!', novoStatus === 'cancelada' ? 'warning' : 'success');
            if (typeof carregarReservas === 'function') {
                await carregarReservas();
            }
        } else {
            mostrarToast(result.erro || 'Erro ao atualizar estado.', 'error');
        }
    } catch (err) {
        console.error('Erro:', err);
        mostrarToast('Erro de ligação.', 'error');
    }
};

// ============================================
// TOAST GLOBAL (partilhado entre ficheiros)
// ============================================

window.mostrarToast = function (msg, tipo = 'success') {
    const toast = document.getElementById('successMessage');
    if (!toast) return;

    const span = toast.querySelector('span');
    const icon = toast.querySelector('i');

    if (span) span.textContent = msg;

    const icons = {
        success: 'far fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    if (icon) icon.className = icons[tipo] || icons.success;

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

// ============================================
// UTILITÁRIOS
// ============================================

function escapeHtmlTabs(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function formatDataTabs(dateStr) {
    if (!dateStr) return 'N/A';
    const [ano, mes, dia] = dateStr.split('-');
    const d = new Date(ano, parseInt(mes, 10) - 1, dia);
    return d.toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
