/* ============================================================
   menu-public.js — Ementa pública (página menu.html)
   Vai buscar os pratos à base de dados (api/menu.php) e monta
   a lista na página, separada por categorias.
   ============================================================ */

// As 4 categorias, pela ordem em que aparecem no HTML (.menu-section).
const CATEGORIAS = ['entradas', 'principais', 'sobremesas', 'bebidas'];


/* ------------------------------------------------------------
   Escapar texto para HTML.
   Evita problemas se o nome/descrição de um prato tiver
   caracteres especiais (ex: < > &). Devolve texto seguro.
   ------------------------------------------------------------ */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}


/* ------------------------------------------------------------
   Descobrir o caminho da imagem de um prato.
   Trata 3 casos: sem imagem, URL/caminho já válido, ou só o
   nome do ficheiro.
   ------------------------------------------------------------ */
function resolverCaminhoImagem(item) {
    if (!item.imagem) {
        // Sem imagem definida -> usar imagem padrão.
        return '../imagens/pratos/placeholder.jpg';
    }
    // Já é um URL (http) ou um caminho relativo/absoluto (../, ./, /).
    if (/^(https?:\/\/|\.{1,2}\/|\/)/.test(item.imagem)) {
        return item.imagem;
    }
    // Só o nome do ficheiro -> apontar para a pasta imagens/ da raiz.
    return '../imagens/' + item.imagem.replace(/^imagens\//, '');
}


/* ------------------------------------------------------------
   Criar o cartão (div) de um prato, pronto a inserir na página.
   ------------------------------------------------------------ */
function criarCartaoPrato(item) {
    const div = document.createElement('div');
    div.className = 'menu-item';

    // Se for um prato especial, marca como destaque e põe uma etiqueta.
    if (item.tipo === 'especial') {
        div.classList.add('featured');
        const badge = document.createElement('div');
        badge.className = 'menu-item-badge';
        badge.textContent = 'Especial da Casa';
        div.appendChild(badge);
    }

    // Imagem do prato. Se falhar a carregar, mostra "Sem imagem".
    const imgPath = resolverCaminhoImagem(item);
    const img = document.createElement('div');
    img.className = 'menu-item-image';
    img.innerHTML =
        `<img src="${imgPath}" alt="${escapeHtml(item.nome)}" ` +
        `onerror="this.style.display='none'; ` +
        `this.parentElement.style.background='#f0f0f0'; ` +
        `this.parentElement.innerHTML='<span style=color:#999>Sem imagem</span>';">`;
    div.appendChild(img);

    // Texto: nome, descrição e preço (formatado em euros, ex: €9,50).
    const precoFormatado = parseFloat(item.preco).toFixed(2).replace('.', ',');
    const content = document.createElement('div');
    content.className = 'menu-item-content';
    content.innerHTML = `
        <h3 class="menu-item-title">${escapeHtml(item.nome)}</h3>
        <p class="menu-item-description">${escapeHtml(item.descricao)}</p>
        <div class="menu-item-price">€${precoFormatado}</div>
    `;
    div.appendChild(content);

    return div;
}


/* ------------------------------------------------------------
   Mostrar a ementa a partir de uma lista de pratos.
   Distribui cada prato pela sua categoria e esconde as
   categorias que ficarem vazias.
   ------------------------------------------------------------ */
function renderMenuFromData(items) {
    const seccoes = document.querySelectorAll('.menu-section');

    // Esconder o indicador "A carregar..." se existir.
    const loadingEl = document.getElementById('menuLoadingIndicator');
    if (loadingEl) loadingEl.style.display = 'none';

    // Para cada categoria, guardar a grelha onde entram os pratos
    // e limpá-la (para não duplicar quando recarrega).
    const grelhas = {};
    CATEGORIAS.forEach(function (cat, idx) {
        const seccao = seccoes[idx];
        const grelha = seccao ? seccao.querySelector('.menu-items-grid') : null;
        grelhas[cat] = grelha;
        if (grelha) grelha.innerHTML = '';
    });

    if (!items || items.length === 0) return;

    // Contar quantos pratos ficam em cada categoria.
    const contagem = { entradas: 0, principais: 0, sobremesas: 0, bebidas: 0 };

    items.forEach(function (item) {
        // Ignorar pratos marcados como indisponíveis.
        if (parseInt(item.disponivel) === 0) return;

        const grelha = grelhas[item.categoria];
        if (!grelha) return; // Categoria desconhecida -> ignora.

        contagem[item.categoria]++;
        grelha.appendChild(criarCartaoPrato(item));
    });

    // Esconder as secções sem pratos; mostrar as que têm.
    seccoes.forEach(function (seccao, idx) {
        if (!seccao) return;
        const cat = CATEGORIAS[idx];
        seccao.style.display = (contagem[cat] === 0) ? 'none' : '';
    });

    // Os pratos acabaram de ser inseridos: se o site estiver noutra
    // língua, traduzir também estes elementos novos.
    if (typeof window.traduzirNovoConteudo === 'function') {
        window.traduzirNovoConteudo();
    }
}


/* ------------------------------------------------------------
   Ir buscar a ementa à base de dados (API).
   Se a ligação falhar, tenta usar a última versão guardada
   no navegador (localStorage) para o site não ficar vazio.
   ------------------------------------------------------------ */
async function loadPublicMenu() {
    try {
        const response = await fetch('../api/menu.php?public=true');
        const data = await response.json();

        if (data.sucesso && data.dados) {
            renderMenuFromData(data.dados);
            // Guardar cópia local (caso a API falhe da próxima vez).
            try {
                localStorage.setItem('menuItems_100sabores', JSON.stringify(data.dados));
            } catch (e) {}
        }
    } catch (err) {
        console.error('Erro ao carregar ementa pública:', err);
        // Plano B: usar a cópia guardada anteriormente.
        const saved = localStorage.getItem('menuItems_100sabores');
        if (saved) {
            try { renderMenuFromData(JSON.parse(saved)); } catch (e) {}
        }
    }
}


// Quando a página carrega: mostrar a ementa e ficar atento a
// alterações feitas noutro separador (ex: o dashboard atualizou pratos).
document.addEventListener('DOMContentLoaded', function () {
    loadPublicMenu();

    window.addEventListener('storage', function (e) {
        if (e.key === 'menuItems_100sabores' && e.newValue) {
            try { renderMenuFromData(JSON.parse(e.newValue)); } catch (err) {}
        }
    });
});
