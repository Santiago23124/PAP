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
                horaSelect.innerHTML = '<option value="">Selecione um horário</option>';
                response.horarios.forEach(h => {
                    const opt = document.createElement('option');
                    opt.value = h.hora;
                    opt.textContent = h.hora + ' (' + h.vagas + ' vagas)';
                    horaSelect.appendChild(opt);
                });
            } else {
                carregarHorariosFallback();
            }
            horaSelect.disabled = false;
        })
        .catch(err => {
            console.error('Erro carregar horários:', err);
            carregarHorariosFallback();
            horaSelect.disabled = false;
        });
}

function carregarHorariosFallback() {
    const horaSelect = document.getElementById('hora');
    if (!horaSelect) return;

    const horarios = ['12:00', '12:30', '13:00', '13:30', '14:00', '19:00', '19:30', '20:00', '20:30', '21:00', '21:30'];
    horaSelect.innerHTML = '<option value="">Selecione um horário</option>';
    horarios.forEach(h => {
        const opt = document.createElement('option');
        opt.value = h;
        opt.textContent = h;
        horaSelect.appendChild(opt);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reservaForm');
    const debugEl = document.getElementById('reservasDebug');

    let reservas = JSON.parse(localStorage.getItem('reservas_publico')) || [];

    function atualizarDebug() {
        if (debugEl) {
            debugEl.textContent = JSON.stringify(reservas, null, 2);
        }
    }
    atualizarDebug();

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
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
                submitBtn.innerHTML = 'Confirmar Reserva';
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao processar a reserva. Por favor, tente novamente mais tarde.');
            submitBtn.innerHTML = 'Confirmar Reserva';
            submitBtn.disabled = false;
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
