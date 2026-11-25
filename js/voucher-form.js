document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('voucherForm');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const submitBtn = document.getElementById('voucherSubmit');
        const formData = new FormData(form);

        const dados = {
            nome_recebe: formData.get('nome_recebe'),
            nome_oferece: formData.get('nome_oferece'),
            email: formData.get('email'),
            valor: parseInt(formData.get('valor'), 10),
            mensagem: formData.get('mensagem') || ''
        };

        submitBtn.disabled = true;
        submitBtn.textContent = 'A processar...';

        try {
            const res = await fetch('../api/voucher.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });
            const result = await res.json();

            if (result.sucesso) {
                mostrarSucessoVoucher(result, dados);
            } else {
                const msg = result.erro || (result.erros ? result.erros.join('\n') : 'Falha ao criar o voucher');
                alert(msg);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Oferecer voucher';
            }
        } catch (err) {
            console.error('Erro voucher:', err);
            alert('Erro ao processar. Tente novamente mais tarde.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Oferecer voucher';
        }
    });

    function mostrarSucessoVoucher(result, dados) {
        const box = document.getElementById('voucherSucesso');
        const formCard = document.getElementById('voucherFormCard');
        if (!box) return;

        formCard.style.display = 'none';
        box.style.display = 'block';
        box.innerHTML = `
            <h3>Voucher criado com sucesso!</h3>
            <p>Enviámos o voucher para <strong>${dados.email}</strong>.</p>
            <div class="voucher-resumo">
                <p><span>Código:</span> <strong>${result.codigo}</strong></p>
                <p><span>Valor:</span> <strong>${result.valor}&euro;</strong></p>
                <p><span>Titular:</span> ${result.titular}</p>
                <p><span>IBAN:</span> <strong>${result.iban}</strong></p>
                <p class="nota">Faça a transferência usando o código <strong>${result.codigo}</strong> como referência. O voucher fica válido após confirmarmos o pagamento.</p>
            </div>
            <a class="voucher-btn" href="${result.url_voucher}" target="_blank" rel="noopener">Ver e imprimir o voucher</a>
        `;
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
