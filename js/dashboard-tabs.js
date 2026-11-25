
// ============================================
// DASHBOARD TABS + MODAIS DE RESERVAS
// Controlo das tabs de navegação e modais
// de edição/detalhes de reservas
// ============================================

// Código Single page Application (SPA), sem recarregamento de página.
// Manipula o DOM e faz pedidos AJAX/Fetch para a API PHP.

// Exemplo explicado: indexOf verifica se um item existe num array.
// Se devolver -1, o item não existe; se devolver >= 0, o item existe.
// DOM é a página HTML vista como uma árvore, e o JS manipula cada parte.

document.addEventListener('DOMContentLoaded', function () {
    // Quando a página termina de carregar, executa estas funções.
    aplicarPermissoes(); // Ajusta visibilidade das tabs segundo o cargo do utilizador.
    configurarTabs(); // Prepara o comportamento de navegação entre tabs.
    configurarModalReserva(); // Configura os modais relacionados às reservas.
    configurarFechoModalEsc(); // Fecha modais quando o utilizador pressiona ESC.
});

// ============================================
// PERMISSÕES POR CARGO
// ============================================

function aplicarPermissoes() {
    // Lê os dados de utilizador guardados na sessionStorage.
    const userData = JSON.parse(sessionStorage.getItem('loggedInUser') || '{}');
    const cargo = userData.cargo; // Cargo pode ser 'admin', 'chef' ou 'garcom'.

    if (!cargo) return; // Se não existir cargo, não altera nada.

    const permissoes = {
        'admin': ['reservas', 'menu'],
        'chef': ['menu'],
        'garcom': ['reservas']
    };

    // Lista de tabs permitidas para o cargo atual.
    const tabsPermitidas = permissoes[cargo] || [];

    // Esconde os botões de tab que não estão permitidos.
    document.querySelectorAll('.tab-btn').forEach(btn => {
        const tab = btn.dataset.tab;
        if (tabsPermitidas.indexOf(tab) === -1) {
            btn.style.display = 'none';
        }
    });

    // Esconde o conteúdo dos tabs que não estão permitidos.
    document.querySelectorAll('.tab-content').forEach(content => {
        const tabId = content.id.replace('tab-', '');
        if (tabsPermitidas.indexOf(tabId) === -1) {
            content.style.display = 'none';
        }
    });

    if (tabsPermitidas.length > 0) {
        // Seleciona o primeiro tab permitido e mostra-o como ativo.
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

            // Atualiza o estado visual dos botões.
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Esconde todos os conteúdos de tab e ativa só o tab clicado.
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
                tab.classList.remove('active');
            });

            const tabEl = document.getElementById('tab-' + tabAlvo);
            if (tabEl) {
                tabEl.style.display = 'block';
                tabEl.classList.add('active');
            }

            // Mostra o gráfico apenas quando o tab de reservas estiver ativo.
            if (chartSection) {
                chartSection.style.display = tabAlvo === 'reservas' ? 'block' : 'none';
            }

            // Carrega dados do menu quando abre o tab do menu.
            if (tabAlvo === 'menu' && typeof carregarItensMenu === 'function') {
                carregarItensMenu();
            }

            // Re-desenha o gráfico de ocupação ao voltar ao tab de reservas.
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
            // Fechar modal se clicar no fundo transparente do overlay.
            if (e.target === overlayReserva) {
                fecharModalReserva();
            }
        });
    }

    const overlayDetalhes = document.getElementById('modalDetalhes');
    if (overlayDetalhes) {
        overlayDetalhes.addEventListener('click', function (e) {
            // Fecha o modal de detalhes apenas se o clique for no overlay, não no conteúdo.
            if (e.target === e.currentTarget) {
                fecharModalDetalhes();
            }
        });
    }

    const detalhesBody = document.getElementById('modalDetalhesBody');
    if (detalhesBody) {
        detalhesBody.addEventListener('click', function (e) {
            // Captura o botão clicado dentro do modal de detalhes.
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
        e.preventDefault(); // Evita o envio normal do formulário.

        const id = document.getElementById('editReservaId').value;
        if (!id) return;

        // Cria o objeto de dados que vai ser enviado para a API.
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
        // Obtém a reserva a editar. Se a lista ainda não estiver carregada,
        // devolve null e volta a chamar esta função quando os dados chegarem.
        const reserva = obterReservaOuCarregar(id, abrirModalEditarReserva);
        if (!reserva) return;

        const setField = (fieldId, val) => {
            const el = document.getElementById(fieldId);
            if (el) el.value = val;
        };

        // Preenche os campos do formulário com os valores da reserva.
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
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    } catch (error) {
        console.error('Erro ao abrir modal de edição:', error);
        mostrarToast('Erro ao abrir formulário de edição.', 'error');
    }
};

window.fecharModalReserva = function () {
    fecharModalPorId('modalReserva', 'modalDetalhes');
};

// ============================================
// MODAL DE DETALHES
// ============================================

window.abrirModalDetalhesReserva = function (id) {
    try {
        // Obtém a reserva a mostrar (carrega a lista primeiro, se preciso).
        const reserva = obterReservaOuCarregar(id, abrirModalDetalhesReserva);
        if (!reserva) return;

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
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    } catch (error) {
        console.error('Erro ao abrir modal de detalhes:', error);
        mostrarToast('Erro ao abrir detalhes da reserva.', 'error');
    }
};

window.fecharModalDetalhes = function () {
    fecharModalPorId('modalDetalhes', 'modalReserva');
};

// ============================================
// FECHAR MODAIS COM ESC
// ============================================

function configurarFechoModalEsc() {
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            fecharModalReserva();
            fecharModalDetalhes();
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

// Devolve a reserva com o id indicado, garantindo que a lista está carregada.
// - Se a lista ainda estiver vazia: vai buscá-la à API e, quando chegar,
//   volta a chamar `callback(id)` (a função que pediu a reserva). Devolve null.
// - Se a reserva não aparecer na lista: recarrega e tenta outra vez. Devolve null.
// - Se encontrar a reserva: devolve-a logo.
// Ou seja: se devolver null, quem chamou deve simplesmente parar (return).
function obterReservaOuCarregar(id, callback) {
    const idNum = Number(id);
    const listaVazia = typeof todasReservas === 'undefined' ||
                       !Array.isArray(todasReservas) ||
                       todasReservas.length === 0;

    // Caso 1: ainda não há reservas carregadas -> ir buscá-las à API.
    if (listaVazia) {
        mostrarToast('A carregar dados das reservas...', 'info');
        fetch('../api/reservas.php')
            .then(r => r.json())
            .then(data => {
                if (data.sucesso && Array.isArray(data.reservas)) {
                    carregarReservas();
                    setTimeout(() => callback(idNum), 500); // Tenta de novo após carregar.
                } else {
                    mostrarToast('Erro ao carregar reservas da API.', 'error');
                }
            })
            .catch(err => {
                console.error('Erro fetch reservas:', err);
                mostrarToast('Erro de ligação ao servidor.', 'error');
            });
        return null;
    }

    // Caso 2: há lista, mas a reserva não está lá -> recarregar e tentar de novo.
    const reserva = todasReservas.find(r => Number(r.id) === idNum);
    if (!reserva) {
        mostrarToast('Reserva não encontrada. A recarregar lista...', 'warning');
        carregarReservas().then(() => {
            const r2 = Array.isArray(todasReservas)
                ? todasReservas.find(x => Number(x.id) === idNum)
                : null;
            if (r2) {
                callback(idNum);
            } else {
                mostrarToast('Reserva não encontrada após recarregar.', 'error');
            }
        });
        return null;
    }

    // Caso 3: encontrada -> devolve a reserva.
    return reserva;
}

// Fecha o modal `idFechar` e, se o outro modal (`idOutro`) também estiver
// fechado, volta a permitir o scroll da página.
function fecharModalPorId(idFechar, idOutro) {
    const modal = document.getElementById(idFechar);
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
    }
    const outro = document.getElementById(idOutro);
    const outroFechado = !outro ||
                         outro.style.display === 'none' ||
                         outro.style.display === '';
    if (outroFechado) {
        document.body.style.overflow = '';
    }
}

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
