// ============================================
// DASHBOARD TABS + MODAIS DE RESERVAS
// Controlo das tabs de navegação e modais
// de edição/detalhes de reservas
// ============================================

document.addEventListener('DOMContentLoaded', function () {
    configurarTabs();
    configurarModalReserva();
    configurarFechoModalEsc();
});

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
    const idNum = Number(id);

    // Se ainda nao ha reservas carregadas, carregar e voltar a tentar
    if (typeof todasReservas === 'undefined' || !Array.isArray(todasReservas) || todasReservas.length === 0) {
        mostrarToast('A carregar dados das reservas...', 'info');
        fetch('../api/reservas.php')
            .then(r => r.json())
            .then(data => {
                if (data.sucesso && Array.isArray(data.reservas)) {
                    todasReservas = data.reservas.map(r => ({
                        id: Number(r.id),
                        nome: r.nome,
                        email: r.email,
                        telefone: r.telefone,
                        data: r.data_reserva,
                        hora: r.hora_reserva ? r.hora_reserva.slice(0, 5) : '',
                        num_pessoas: Number(r.num_pessoas) || 0,
                        observacoes: r.observacoes,
                        status: r.status,
                        mesa_id: r.mesa_id,
                        mesa_numero: r.mesa_numero || null,
                        mesa_capacidade: r.mesa_capacidade,
                        mesa_localizacao: r.mesa_localizacao,
                        turno_id: r.turno_id,
                        turno_nome: r.turno_nome
                    }));
                    abrirModalEditarReserva(idNum);
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

    // Obter reserva da variavel global do reservas-dashboard.js
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

    document.getElementById('editReservaId').value = reserva.id;
    document.getElementById('editNome').value = reserva.nome || '';
    document.getElementById('editEmail').value = reserva.email || '';
    document.getElementById('editTelefone').value = reserva.telefone || '';
    document.getElementById('editData').value = reserva.data || '';
    document.getElementById('editHora').value = reserva.hora || '';
    document.getElementById('editNumPessoas').value = reserva.num_pessoas || 1;
    document.getElementById('editStatus').value = reserva.status || 'pendente';
    document.getElementById('editObservacoes').value = reserva.observacoes || '';

    const modal = document.getElementById('modalReserva');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
};

window.fecharModalReserva = function () {
    const modal = document.getElementById('modalReserva');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
};

// ============================================
// MODAL DE DETALHES
// ============================================

window.abrirModalDetalhesReserva = function (id) {
    const idNum = Number(id);

    // Se ainda não há reservas carregadas, carregar e voltar a tentar
    if (typeof todasReservas === 'undefined' || !Array.isArray(todasReservas) || todasReservas.length === 0) {
        mostrarToast('A carregar dados das reservas...', 'info');
        fetch('../api/reservas.php')
            .then(r => r.json())
            .then(data => {
                if (data.sucesso && Array.isArray(data.reservas)) {
                    todasReservas = data.reservas.map(r => ({
                        id: Number(r.id),
                        nome: r.nome,
                        email: r.email,
                        telefone: r.telefone,
                        data: r.data_reserva,
                        hora: r.hora_reserva ? r.hora_reserva.slice(0, 5) : '',
                        num_pessoas: Number(r.num_pessoas) || 0,
                        observacoes: r.observacoes,
                        status: r.status,
                        mesa_id: r.mesa_id,
                        mesa_numero: r.mesa_numero || null,
                        mesa_capacidade: r.mesa_capacidade,
                        mesa_localizacao: r.mesa_localizacao,
                        turno_id: r.turno_id,
                        turno_nome: r.turno_nome
                    }));
                    abrirModalDetalhesReserva(idNum);
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
                <strong>Turno</strong>
                <span>${escapeHtmlTabs(reserva.turno_nome || 'N/A')}</span>
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
                <button onclick="fecharModalDetalhes(); abrirModalEditarReserva(${reserva.id});" class="btn-modal-save">
                    <i class="fas fa-edit"></i> Editar Reserva
                </button>
                ${reserva.status !== 'confirmada' ? `
                <button onclick="fecharModalDetalhes(); alterarStatusReserva(${reserva.id}, 'confirmada');" class="btn-modal-save" style="background:linear-gradient(135deg,#27ae60,#229954);">
                    <i class="fas fa-check"></i> Confirmar
                </button>` : ''}
                ${reserva.status !== 'cancelada' ? `
                <button onclick="fecharModalDetalhes(); alterarStatusReserva(${reserva.id}, 'cancelada');" class="btn-modal-cancel" style="border-color:#e74c3c; color:#e74c3c;">
                    <i class="fas fa-times"></i> Cancelar
                </button>` : ''}
            </div>
        `;
    }

    const modal = document.getElementById('modalDetalhes');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
};

window.fecharModalDetalhes = function () {
    const modal = document.getElementById('modalDetalhes');
    if (modal) {
        modal.style.display = 'none';
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
        }
    });

    // Fechar ao clicar fora do conteúdo
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function (e) {
            if (e.target === this) {
                fecharModalReserva();
                fecharModalDetalhes();
            }
        });
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
