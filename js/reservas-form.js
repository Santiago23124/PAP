// ============================================
// RESERVAS FORM - PÁGINA PÚBLICA
// Handler simples para formulário de reserva
// ============================================
// Função principal que ouve o navegador, esta garante que o codigo js so comece a ser carregado depois do HTML
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reservaForm');
    const debugEl = document.getElementById('reservasDebug');

    // Carregar reservas salvas (localStorage fallback)
    let reservas = JSON.parse(localStorage.getItem('reservas_publico')) || [];

    // Função de utilidade para atualizar o debug
    function atualizarDebug() {
        if (debugEl) {
            debugEl.textContent = JSON.stringify(reservas, null, 2);
        }
    }
    atualizarDebug();

    // Submissão do formulário
    form.addEventListener('submit', async function(e) { // anync- Function assincrona (espera a resposta do servidor)
        e.preventDefault(); // Previne o comportamento padrão do HTML (Refresh) assim nao existe perda de dados

        const submitBtn = document.getElementById('submitBtn');
        const formData = new FormData(form);

        submitBtn.innerHTML = 'A enviar...';
        submitBtn.disabled = true; // Desabilita o botão de submit para evitar multiplas submissões
        // criar um objeto chamado reserva
        const reserva = {
            nome: formData.get('nome'), //FormData() - objeto que vai buscar o que foi escrito no input do formulario
            email: formData.get('email'),
            telefone: formData.get('telefone'),
            data: formData.get('data'),
            hora: formData.get('hora'),
            num_pessoas: parseInt(formData.get('num_pessoas')), //Converte a string em um numero inteiro
            observacoes: formData.get('observacoes') || '',
            status: 'pendente',
            criado_em: new Date().toISOString() //Gera uma marca para saber exatamente quando foi criada a reserva
        };
           // fetch() -Função que faz o pedido HTTP ao servidor PHP (API)
        try {
            //await -espera a resposta do servidor PHP (API)
            const response = await fetch('../api/reservas.php', {
                method: 'POST', //Metodo usado para enviar dados e nao para obter dados
                headers: { 'Content-Type': 'application/json' }, // O envio dos dados é em JSON
                body: JSON.stringify(reserva) // Converte o objeto reserva em uma string JSON
            });

            const result = await response.json(); // Converte a resposta do servidor PHP (API) em um objeto JSON

            if (result.sucesso) {
                // Mostrar sucesso
                form.style.display = 'none'; //Se correr tudo bem, esconde o formulario
                const successDiv = document.getElementById('successMessage');
                successDiv.style.display = 'block'; //Mostra a div de sucesso
                successDiv.innerHTML = ` 
                    <h3>Reserva Confirmada!</h3>
                    <p>Obrigado ${reserva.nome}!</p>
                    <p>Nº da reserva: <strong>#${result.id}</strong></p>
                    ${result.mesa ? '<p>Mesa: <strong>' + result.mesa.numero + '</strong></p>' : ''}
                `;
            } else {
                // Se der erro, mostra uma mensagem de erro
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

    // Data mínima: hoje
    const dataInput = document.getElementById('data');
    const hoje = new Date().toISOString().split('T')[0]; //toISOString() - Converte a data e hora em uma string ISO (formato: YYYY-MM-DDTHH:MM:SS.SSSZ)
    dataInput.min = hoje;
    if (!dataInput.value) {
        dataInput.value = hoje;
    }
});
