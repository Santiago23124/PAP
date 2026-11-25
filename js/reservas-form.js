// Preenche o <select id="hora"> a partir de uma lista de { valor, texto }
function preencherHoras(opcoes) {
    const horaSelect = document.getElementById('hora');
    if (!horaSelect) return;

    horaSelect.innerHTML = '<option value="">Selecione um horário</option>';
    opcoes.forEach(function(o) {
        const opt = document.createElement('option');
        opt.value = o.valor;
        opt.textContent = o.texto;
        horaSelect.appendChild(opt);
    });
}

function carregarHorariosDisponiveis(data) {
    const horaSelect = document.getElementById('hora');
    if (!horaSelect) return;

    horaSelect.innerHTML = '<option value="">A carregar...</option>';
    horaSelect.disabled = true;

    fetch('../api/reservas.php?action=horarios&data=' + data)
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(response => {
            if (response.sucesso && response.horarios.length > 0) {
                preencherHoras(response.horarios.map(h => ({
                    valor: h.hora,
                    texto: h.hora + ' (' + h.vagas + ' vagas)'
                })));
            } else {
                carregarHorariosFallback(data);
            }
            horaSelect.disabled = false;
        })
        .catch(err => {
            console.error('Erro carregar horários:', err);
            carregarHorariosFallback(data);
            horaSelect.disabled = false;
        });
}

// Gera os mesmos slots que o servidor: dia de semana (almoço + jantar)
// ou fim de semana (apenas jantar 18:00–22:00), de 30 em 30 minutos.
function gerarHorarios(data) {
    const d = new Date(data + 'T00:00:00');
    const dia = d.getDay(); // 0=domingo, 6=sábado
    const fimDeSemana = (dia === 0 || dia === 6);
    const faixas = fimDeSemana
        ? [['18:00', '22:00']]
        : [['12:00', '13:30'], ['19:00', '21:30']];

    const slots = [];
    faixas.forEach(function(faixa) {
        let [h, m] = faixa[0].split(':').map(Number);
        const [hf, mf] = faixa[1].split(':').map(Number);
        while (h < hf || (h === hf && m <= mf)) {
            slots.push(String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0'));
            m += 30;
            if (m >= 60) { m -= 60; h++; }
        }
    });
    return slots;
}

function carregarHorariosFallback(data) {
    const dia = data || new Date().toISOString().split('T')[0];
    preencherHoras(gerarHorarios(dia).map(h => ({ valor: h, texto: h })));
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reservaForm');
    const submitBtn = document.getElementById('submitBtn');

    function resetarBotao() {
        submitBtn.innerHTML = 'Confirmar Reserva';
        submitBtn.disabled = false;
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        submitBtn.innerHTML = 'A enviar...';
        submitBtn.disabled = true;

        const reserva = {
            nome: formData.get('nome'),
            email: formData.get('email'),
            telefone: formData.get('telefone'),
            data: formData.get('data'),
            hora: formData.get('hora'),
            num_pessoas: parseInt(formData.get('num_pessoas')),
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
                form.style.display = 'none';
                const successDiv = document.getElementById('successMessage');
                successDiv.style.display = 'block';
                successDiv.innerHTML = `
                    <h3>Reserva Confirmada!</h3>
                    <p>Obrigado ${reserva.nome}!</p>
                    <p>Nº da reserva: <strong>#${result.id}</strong></p>
                    ${result.mesa ? '<p>Mesa: <strong>' + result.mesa.numero + '</strong></p>' : ''}
                `;
            } else {
                alert('Erro: ' + (result.erro || result.erros?.join(', ') || 'Falha na reserva'));
                resetarBotao();
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao processar a reserva. Por favor, tente novamente mais tarde.');
            resetarBotao();
        }
    });

    const dataInput = document.getElementById('data');
    const hoje = new Date().toISOString().split('T')[0];
    dataInput.min = hoje;
    if (!dataInput.value) {
        dataInput.value = hoje;
    }
    carregarHorariosDisponiveis(hoje);

    dataInput.addEventListener('change', function() {
        carregarHorariosDisponiveis(this.value);
    });
});
