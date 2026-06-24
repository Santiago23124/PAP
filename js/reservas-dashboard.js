// ============================================
// RESERVAS DASHBOARD - JAVASCRIPT
// Dashboard interativa com estatisticas, filtros, graficos e gestao de reservas
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    inicializarDashboard();
});

// ============================================
// ESTADO GLOBAL
// ============================================

let todasReservas = [];
let reservasFiltradas = [];
let mesasDisponiveis = [];
let primeiraAnimacao = true;

// Estado da paginação
let paginaAtualReservas = 1;
const reservasPorPagina = 8;

// ============================================
// INICIALIZACAO
// ============================================

async function inicializarDashboard() {
    await carregarDadosIniciais();
    configurarEventListeners();
    atualizarEstatisticas();
    renderizarTabela();
    gerarHorariosDisponiveis();
    desenharGraficoOcupacao();
    atualizarDebug();
}

async function carregarDadosIniciais() {
        try {
            const resMesas = await fetch('../api/reservas.php?action=mesas');
            const dataMesas = await resMesas.json();
            if (dataMesas.sucesso) {
                mesasDisponiveis = dataMesas.mesas;
            }
    } catch (e) {
        console.error('Erro carregar mesas:', e);
    }

    await carregarReservas();
}

async function carregarReservas() {
    try {
        const res = await fetch('../api/reservas.php');
        const data = await res.json();

        if (data.sucesso && Array.isArray(data.reservas)) {
            todasReservas = data.reservas.map(r => ({
                id: Number(r.id),
                nome: r.nome,
                email: r.email,
                telefone: r.telefone,
                data: r.data_reserva,
                hora: normalizarHora(r.hora_reserva),
                num_pessoas: Number(r.num_pessoas) || 0,
                observacoes: r.observacoes,
                status: r.status,
                mesa_id: r.mesa_id,
                mesa_numero: r.mesa_numero || null,
                mesa_capacidade: r.mesa_capacidade,
                mesa_localizacao: r.mesa_localizacao
            }));
        } else {
            todasReservas = [];
        }
    } catch (err) {
        console.error('Erro carregar reservas:', err);
        todasReservas = [];
    }

    reservasFiltradas = [...todasReservas];
    paginaAtualReservas = 1; // Reset para página 1 ao recarregar
    gerarHorariosDisponiveis();
    renderizarTabela();
    atualizarEstatisticas();
    atualizarDebug();
}

window.carregarReservas = carregarReservas;

// ============================================
// CONFIGURACAO DE EVENT LISTENERS
// ============================================

function configurarEventListeners() {
    const form = document.getElementById('reservaForm');
    if (form) {
        form.addEventListener('submit', handleSubmit);
    }

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', filtrarReservas);
    }

    const filterStatus = document.getElementById('filterStatus');
    if (filterStatus) {
        filterStatus.addEventListener('change', filtrarReservas);
    }

    const filterData = document.getElementById('filterData');
    if (filterData) {
        filterData.addEventListener('change', filtrarReservas);
    }

    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    const dataInput = document.getElementById('data');
    if (dataInput) {
        dataInput.min = getTodayDate();
        if (!dataInput.value) {
            dataInput.value = getTodayDate();
        }
        dataInput.addEventListener('change', gerarHorariosDisponiveis);
    }
}

// ============================================
// SUBMISSAO DE FORMULARIO
// ============================================

async function handleSubmit(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const formData = new FormData(e.target);

    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> A enviar...';
        submitBtn.disabled = true;
    }

    const reserva = {
        nome: formData.get('nome'),
        email: formData.get('email'),
        telefone: formData.get('telefone'),
        data: formData.get('data'),
        hora: formData.get('hora'),
        num_pessoas: parseInt(formData.get('num_pessoas'), 10),
        observacoes: formData.get('observacoes') || '',
        status: 'pendente',
        criado_em: new Date().toISOString()
    };

    try {
        const response = await fetch('../api/reservas.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(reserva)
        });

        const result = await response.json();

        if (result.sucesso) {
            await carregarReservas();
            e.target.reset();
            const dataInput = document.getElementById('data');
            if (dataInput) {
                dataInput.value = getTodayDate();
            }
            if (typeof mostrarToast === 'function') {
                mostrarToast(
                    result.mesa
                        ? `Reserva criada! Mesa ${result.mesa.numero} atribuída.`
                        : 'Reserva criada com sucesso!',
                    'success'
                );
            } else {
                mostrarSucesso(result.mesa ? `Reserva criada! Mesa ${result.mesa.numero} atribuída.` : 'Reserva criada!');
            }
        } else {
            if (typeof mostrarToast === 'function') {
                mostrarToast('Erro: ' + (result.erro || 'Falha na reserva'), 'error');
            } else {
                alert('Erro: ' + (result.erro || 'Falha na reserva'));
            }
        }
    } catch (error) {
        console.error('Erro:', error);
        if (typeof mostrarToast === 'function') {
            mostrarToast('Erro de ligação. Tenta novamente.', 'error');
        } else {
            alert('Erro de ligação. Tenta novamente.');
        }
    } finally {
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="far fa-check-circle"></i> Confirmar Reserva';
            submitBtn.disabled = false;
        }
    }
}

function mostrarSucesso(msg = 'Reserva criada com sucesso!') {
    const toast = document.getElementById('successMessage');
    if (!toast) {
        return;
    }

    const span = toast.querySelector('span');
    if (span) {
        span.textContent = msg;
    }

    toast.style.display = 'flex';
    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => {
            toast.style.display = 'none';
            toast.classList.remove('hide');
            if (span) {
                span.textContent = 'Reserva criada com sucesso!';
            }
        }, 500);
    }, 3000);
}

// ============================================
// FILTRAGEM
// ============================================

function filtrarReservas() {
    const search = (document.getElementById('searchInput')?.value || '').toLowerCase();
    const statusFilter = document.getElementById('filterStatus')?.value || 'all';
    const dataFilter = document.getElementById('filterData')?.value || 'all';

    reservasFiltradas = todasReservas.filter(reserva => {
        const matchSearch =
            reserva.nome.toLowerCase().includes(search) ||
            reserva.email.toLowerCase().includes(search) ||
            reserva.telefone.includes(search);

        const matchStatus = statusFilter === 'all' || reserva.status === statusFilter;

        let matchData = true;
        const hoje = new Date();
        const dataReserva = new Date(reserva.data);

        if (dataFilter === 'today') {
            matchData = isSameDay(dataReserva, hoje);
        } else if (dataFilter === 'week') {
            matchData = dataReserva >= startOfWeek(hoje) && dataReserva <= endOfWeek(hoje);
        } else if (dataFilter === 'month') {
            matchData =
                dataReserva.getMonth() === hoje.getMonth() &&
                dataReserva.getFullYear() === hoje.getFullYear();
        }

        return matchSearch && matchStatus && matchData;
    });

    paginaAtualReservas = 1; // Reiniciar para a página 1 sempre que os filtros mudam
    renderizarTabela();
}

// ============================================
// RENDERIZACAO DA TABELA E PAGINAÇÃO
// ============================================

function renderizarTabela() {
    const tbody = document.getElementById('reservasTableBody');
    if (!tbody) {
        return;
    }

    tbody.innerHTML = '';

    if (reservasFiltradas.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align:center; padding:40px; color:#888;">
                    <i class="far fa-calendar-times" style="font-size:2em; margin-bottom:10px; display:block;"></i>
                    Nenhuma reserva encontrada
                </td>
            </tr>
        `;
        const pagContainer = document.getElementById('pagination');
        if (pagContainer) pagContainer.innerHTML = '';
        return;
    }

    // Ordenar as reservas
    const reservasOrdenadas = reservasFiltradas
        .slice()
        .sort((a, b) => new Date(a.data + 'T' + a.hora) - new Date(b.data + 'T' + b.hora));

    const totalReservas = reservasOrdenadas.length;
    const totalPaginas = Math.ceil(totalReservas / reservasPorPagina);

    // Garantir que a página atual está nos limites
    if (paginaAtualReservas > totalPaginas) {
        paginaAtualReservas = Math.max(1, totalPaginas);
    }

    // Fatiar os dados para exibir na página ativa
    const inicio = (paginaAtualReservas - 1) * reservasPorPagina;
    const fim = inicio + reservasPorPagina;
    const reservasPagina = reservasOrdenadas.slice(inicio, fim);

    reservasPagina.forEach(reserva => {
        const tr = document.createElement('tr');

        const mesaDisplay = reserva.mesa_numero
            ? `<span title="${escapeHtml(reserva.mesa_localizacao || '')} (cap. ${reserva.mesa_capacidade})">Mesa ${escapeHtml(String(reserva.mesa_numero))}</span>`
            : '<span style="color:#999; font-style:italic;">Não atribuída</span>';

        const statusDisplay = `<span class="status-badge status-${escapeHtml(reserva.status)}">${escapeHtml(reserva.status)}</span>`;

        tr.innerHTML = `
            <td>
                <strong>${escapeHtml(reserva.nome)}</strong><br>
                <small style="color:#888;">${escapeHtml(reserva.email)}</small>
            </td>
            <td>${formatData(reserva.data)}</td>
            <td>${escapeHtml(reserva.hora)}</td>
            <td>${reserva.num_pessoas} ${reserva.num_pessoas === 1 ? 'pessoa' : 'pessoas'}</td>
            <td>${mesaDisplay}</td>
            <td>${statusDisplay}</td>
            <td>
                    <button class="btn-action" title="Ver detalhes" onclick="abrirModalDetalhesReserva(${reserva.id})" style="color:#3498db; border-color:#3498db;">
                        <i class="far fa-eye"></i>
                    </button>
                    <button class="btn-action btn-confirm" title="Confirmar reserva" onclick="alterarStatus(${reserva.id}, 'confirmada')" ${reserva.status === 'confirmada' ? 'disabled style="opacity:0.4;cursor:not-allowed;"' : ''}>
                        <i class="far fa-check"></i>
                    </button>
                    <button class="btn-action btn-cancel" title="Cancelar reserva" onclick="alterarStatus(${reserva.id}, 'cancelada')" ${reserva.status === 'cancelada' ? 'disabled style="opacity:0.4;cursor:not-allowed;"' : ''}>
                        <i class="far fa-times"></i>
                    </button>
                    <button class="btn-action btn-edit" title="Editar reserva" onclick="editarReserva(${reserva.id})">
                        <i class="far fa-edit"></i>
                    </button>
                    <button class="btn-action btn-delete" title="Eliminar reserva" onclick="eliminarReserva(${reserva.id})">
                        <i class="far fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        `;

        tbody.appendChild(tr);
    });

    renderizarPaginacao(totalPaginas);
}

function renderizarPaginacao(totalPaginas) {
    const pagContainer = document.getElementById('pagination');
    if (!pagContainer) return;

    if (totalPaginas <= 1) {
        pagContainer.innerHTML = '';
        return;
    }

    let html = '';


        // Botão Anterior
        html += `<button class="page-btn prev-btn" ${paginaAtualReservas === 1 ? 'disabled style="opacity:0.4;cursor:not-allowed;"' : ''} onclick="mudarPaginaReservas(${paginaAtualReservas - 1})">
        <i class="fas fa-chevron-left"></i> Anterior
    </button>`;



    // Números das Páginas (Máximo 5 botões visíveis de forma inteligente)
    const maxBotoesVisiveis = 5;
    let inicioBotao = Math.max(1, paginaAtualReservas - Math.floor(maxBotoesVisiveis / 2));
    let fimBotao = Math.min(totalPaginas, inicioBotao + maxBotoesVisiveis - 1);

    if (fimBotao - inicioBotao + 1 < maxBotoesVisiveis) {
        inicioBotao = Math.max(1, fimBotao - maxBotoesVisiveis + 1);
    }

    if (inicioBotao > 1) {
        html += `<button class="page-btn" onclick="mudarPaginaReservas(1)">1</button>`;
        if (inicioBotao > 2) {
            html += `<span class="page-dots">...</span>`;
        }
    }

    for (let i = inicioBotao; i <= fimBotao; i++) {
        html += `<button class="page-btn ${paginaAtualReservas === i ? 'active' : ''}" onclick="mudarPaginaReservas(${i})">${i}</button>`;
    }

    if (fimBotao < totalPaginas) {
        if (fimBotao < totalPaginas - 1) {
            html += `<span class="page-dots">...</span>`;
        }
        html += `<button class="page-btn" onclick="mudarPaginaReservas(${totalPaginas})">${totalPaginas}</button>`;
    }

    // Botão Seguinte
    html += `<button class="page-btn next-btn" ${paginaAtualReservas === totalPaginas ? 'disabled style="opacity:0.4;cursor:not-allowed;"' : ''} onclick="mudarPaginaReservas(${paginaAtualReservas + 1})">
        Seguinte <i class="fas fa-chevron-right"></i>
    </button>`;

    pagContainer.innerHTML = html;
}

window.mudarPaginaReservas = function(pagina) {
    paginaAtualReservas = pagina;
    renderizarTabela();
    // Scroll suave para o topo do painel das reservas
    const rightPanel = document.querySelector('.table-card');
    if (rightPanel) {
        rightPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
};

// ============================================
// ACOES DE RESERVA
// ============================================

window.alterarStatus = async function(id, novoStatus) {
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

    const labelStatus = { confirmada: 'confirmar', cancelada: 'cancelar' };
    const label = labelStatus[novoStatus] || 'alterar o estado de';

    // if (!confirm(`Tens a certeza que queres ${label} a reserva de ${reserva.nome}?`)) return;

    try {
        const response = await fetch('../api/reservas.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, status: novoStatus })
        });

        const result = await response.json();

        if (result.sucesso) {
            const msgs = { confirmada: 'Reserva confirmada!', cancelada: 'Reserva cancelada.' };
            if (typeof mostrarToast === 'function') {
                mostrarToast(msgs[novoStatus] || 'Estado atualizado!', novoStatus === 'cancelada' ? 'warning' : 'success');
            }
            await carregarReservas();
        } else {
            if (typeof mostrarToast === 'function') {
                mostrarToast('Erro: ' + (result.erro || 'Falha ao atualizar estado'), 'error');
            } else {
                alert('Erro: ' + (result.erro || 'Falha ao atualizar estado'));
            }
        }
    } catch (error) {
        console.error('Erro:', error);
        if (typeof mostrarToast === 'function') {
            mostrarToast('Erro de ligação. Tenta novamente.', 'error');
        } else {
            alert('Erro de ligação');
        }
    }
};

window.editarReserva = function(id) {
    if (typeof abrirModalEditarReserva === 'function') {
        abrirModalEditarReserva(id);
    } else {
        console.error('abrirModalEditarReserva não está definida.');
    }
};

window.eliminarReserva = async function(id) {
    const idNum = Number(id);
    const reserva = (Array.isArray(todasReservas))
        ? todasReservas.find(r => Number(r.id) === idNum)
        : null;
    if (!reserva) {
        return;
    }

    // if (!confirm(`Eliminar reserva de ${reserva.nome}?`)) {
    //     return;
    // }

    try {
        const response = await fetch('../api/reservas.php?id=' + id, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.sucesso) {
            if (typeof mostrarToast === 'function') {
                mostrarToast('Reserva eliminada com sucesso!', 'success');
            }
            await carregarReservas();
        } else {
            if (typeof mostrarToast === 'function') {
                mostrarToast('Erro: ' + (result.erro || 'Falha ao eliminar'), 'error');
            } else {
                alert('Erro: ' + (result.erro || 'Falha ao eliminar'));
            }
        }
    } catch (error) {
        console.error('Erro:', error);
        if (typeof mostrarToast === 'function') {
            mostrarToast('Erro de ligação. Tenta novamente.', 'error');
        } else {
            alert('Erro de ligação');
        }
    }
};

// ============================================
// ESTATISTICAS
// ============================================

function atualizarEstatisticas() {
    const hoje = new Date();
    const hojeStr = formatDateISO(hoje);
    const semanaInicio = startOfWeek(hoje);
    const semanaFim = endOfWeek(hoje);

    const total = todasReservas.length;
    const hojeCount = todasReservas.filter(r => r.data === hojeStr).length;
    const semanaCount = todasReservas.filter(r => {
        const d = new Date(r.data);
        return d >= semanaInicio && d <= semanaFim;
    }).length;

    const ocupacaoPessoasHoje = todasReservas
        .filter(r => r.data === hojeStr && r.status !== 'cancelada')
        .reduce((sum, r) => sum + r.num_pessoas, 0);

    const ocupacao = Math.round((ocupacaoPessoasHoje / 80) * 100);

    if (primeiraAnimacao) {
        animarNumero('totalReservas', total);
        animarNumero('reservasHoje', hojeCount);
        animarNumero('reservasSemana', semanaCount);
        primeiraAnimacao = false;
    } else {
        definirTexto('totalReservas', total);
        definirTexto('reservasHoje', hojeCount);
        definirTexto('reservasSemana', semanaCount);
    }

    definirTexto('ocupacao', ocupacao + '%');
}

function animarNumero(elementId, valorFinal) {
    const element = document.getElementById(elementId);
    if (!element) {
        return;
    }

    const valorInicial = parseInt(element.textContent, 10) || 0;
    const duracao = 800;
    const passos = 30;
    const incremento = (valorFinal - valorInicial) / passos;
    let atual = valorInicial;
    let step = 0;

    const intervalo = setInterval(() => {
        step++;
        atual += incremento;
        if (step >= passos) {
            element.textContent = valorFinal;
            clearInterval(intervalo);
        } else {
            element.textContent = Math.round(atual);
        }
    }, duracao / passos);
}

// ============================================
// HORARIOS DISPONIVEIS
// ============================================

function gerarHorariosDisponiveis() {
    const grid = document.getElementById('horariosGrid');
    const dataInput = document.getElementById('data');

    if (!grid || !dataInput) {
        return;
    }

    grid.innerHTML = '';

    const horarios = [
        '12:00', '12:30', '13:00', '13:30',
        '19:00', '19:30', '20:00', '20:30', '21:00', '21:30'
    ];

    const dataSelecionada = dataInput.value || formatDateISO(new Date());
    if (!dataInput.value) {
        dataInput.value = dataSelecionada;
    }

    horarios.forEach(hora => {
        const reservasNesseHorario = todasReservas.filter(r =>
            r.data === dataSelecionada &&
            r.hora === hora &&
            r.status !== 'cancelada'
        );

        const pessoas = reservasNesseHorario.reduce((sum, r) => sum + r.num_pessoas, 0);
        const capacidadeTotal = 80;
        const vagasRestantes = Math.max(0, capacidadeTotal - pessoas);
        const disponivel = vagasRestantes > 0;

        const card = document.createElement('div');
        card.className = `horario-card ${disponivel ? 'disponivel' : 'indisponivel'}`;
        card.innerHTML = `
            <div class="hora">${hora}</div>
            <div class="vagas">${disponivel ? vagasRestantes + ' vagas' : 'Lotado'}</div>
            <div class="ocupacao" style="font-size:0.75em; color:#888; margin-top:4px;">${pessoas}/${capacidadeTotal}</div>
        `;

        if (disponivel) {
            card.addEventListener('click', () => {
                const horaInput = document.getElementById('hora');
                if (horaInput) {
                    horaInput.value = hora;
                }
                document.querySelectorAll('.horario-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                card.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    card.style.transform = '';
                }, 200);
            });
        }

        grid.appendChild(card);
    });
}

// ============================================
// GRAFICO DE OCUPACAO SEMANAL
// ============================================

function desenharGraficoOcupacao() {
    const canvas = document.getElementById('occupancyChart');
    if (!canvas) {
        return;
    }

    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();

    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);

    const width = rect.width;
    const height = rect.height;
    const padding = 50;
    const chartWidth = width - padding * 2;
    const chartHeight = height - padding * 2;
    const dias = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab', 'Dom'];
    const dados = gerarDadosSemana();

    ctx.clearRect(0, 0, width, height);
    ctx.strokeStyle = '#ddd';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(padding, padding);
    ctx.lineTo(padding, height - padding);
    ctx.lineTo(width - padding, height - padding);
    ctx.stroke();

    ctx.strokeStyle = '#f0f0f0';
    for (let i = 0; i <= 4; i++) {
        const y = padding + (chartHeight / 4) * i;
        ctx.beginPath();
        ctx.moveTo(padding, y);
        ctx.lineTo(width - padding, y);
        ctx.stroke();

        ctx.fillStyle = '#666';
        ctx.font = '12px Montserrat, sans-serif';
        ctx.textAlign = 'right';
        ctx.fillText((100 - 25 * i) + '%', padding - 10, y + 4);
    }

    const barWidth = chartWidth / dias.length * 0.6;
    const gap = chartWidth / dias.length * 0.4;

    dias.forEach((dia, i) => {
        const barHeight = (dados[i] / 100) * chartHeight;
        const x = padding + gap / 2 + i * (barWidth + gap);
        const y = height - padding - barHeight;

        const gradient = ctx.createLinearGradient(0, y, 0, height - padding);
        gradient.addColorStop(0, '#A8A8A8');
        gradient.addColorStop(1, '#C8C8C8');

        ctx.fillStyle = gradient;
        if (ctx.roundRect) {
            ctx.beginPath();
            ctx.roundRect(x, y, barWidth, barHeight, [6, 6, 0, 0]);
            ctx.fill();
        } else {
            ctx.fillRect(x, y, barWidth, barHeight);
        }

        ctx.fillStyle = '#3D2F2F';
        ctx.font = '13px Montserrat, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(dia, x + barWidth / 2, height - padding + 20);

        if (dados[i] > 0) {
            ctx.fillStyle = '#5A4A4A';
            ctx.font = 'bold 12px Montserrat, sans-serif';
            ctx.fillText(dados[i] + '%', x + barWidth / 2, y - 8);
        }
    });

    ctx.fillStyle = '#c9a961';
    ctx.font = 'italic 14px Cormorant Garamond, serif';
    ctx.textAlign = 'center';
    ctx.fillText('Taxa de ocupacao semanal', width / 2, padding - 15);
}

window.addEventListener('resize', () => {
    clearTimeout(window.__reservasChartResizeTimer);
    window.__reservasChartResizeTimer = setTimeout(() => {
        desenharGraficoOcupacao();
    }, 250);
});

function gerarDadosSemana() {
    const hoje = new Date();
    const dados = [];

    for (let i = 0; i < 7; i++) {
        const data = new Date(hoje);
        data.setDate(data.getDate() - hoje.getDay() + 1 + i);
        const dataStr = formatDateISO(data);
        const reservasDoDia = todasReservas.filter(r => r.data === dataStr && r.status !== 'cancelada');
        const pessoas = reservasDoDia.reduce((sum, r) => sum + r.num_pessoas, 0);
        dados.push(Math.min(100, Math.round((pessoas / 80) * 100)));
    }

    return dados;
}

// ============================================
// DEBUG
// ============================================

function atualizarDebug() {
    const outputEl = document.getElementById('debugOutput');
    if (outputEl) {
        outputEl.textContent = `Reservas totais: ${todasReservas.length}
Filtradas: ${reservasFiltradas.length}
Mesas carregadas: ${mesasDisponiveis.length}

Detalhes das reservas:
${JSON.stringify(todasReservas, null, 2)}`;
    }
}

// ============================================
// UTILITARIOS
// ============================================

function getTodayDate() {
    return formatDateISO(new Date());
}

function formatDateISO(date) {
    return date.toISOString().split('T')[0];
}

function formatData(dateStr) {
    const [ano, mes, dia] = dateStr.split('-');
    const data = new Date(ano, parseInt(mes, 10) - 1, dia);
    return data.toLocaleDateString('pt-PT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

function isSameDay(d1, d2) {
    return d1.getFullYear() === d2.getFullYear() &&
        d1.getMonth() === d2.getMonth() &&
        d1.getDate() === d2.getDate();
}

function startOfWeek(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1);
    d.setDate(diff);
    d.setHours(0, 0, 0, 0);
    return d;
}

function endOfWeek(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() + (6 - (day === 0 ? 6 : day));
    d.setDate(diff);
    d.setHours(23, 59, 59, 999);
    return d;
}

function normalizarHora(hora) {
    if (!hora) {
        return '';
    }
    return hora.length >= 5 ? hora.slice(0, 5) : hora;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function definirTexto(id, valor) {
    const el = document.getElementById(id);
    if (el) {
        el.textContent = valor;
    }
}
