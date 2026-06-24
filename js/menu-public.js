function renderMenuFromData(items) {
    const sections = document.querySelectorAll('.menu-section');
    const categories = {
        entradas: sections[0] ? sections[0].querySelector('.menu-items-grid') : null,
        principais: sections[1] ? sections[1].querySelector('.menu-items-grid') : null,
        sobremesas: sections[2] ? sections[2].querySelector('.menu-items-grid') : null,
        bebidas: sections[3] ? sections[3].querySelector('.menu-items-grid') : null
    };
    const loadingEl = document.getElementById('menuLoadingIndicator');
    if (loadingEl) loadingEl.style.display = 'none';
    Object.values(categories).forEach(c => { if (c) c.innerHTML = ''; });
    if (!items || items.length === 0) return;
    const counters = { entradas: 0, principais: 0, sobremesas: 0, bebidas: 0 };
    items.forEach(item => {
        if (parseInt(item.disponivel) === 0) return;
        const container = categories[item.categoria];
        if (!container) return;
        counters[item.categoria] = (counters[item.categoria] || 0) + 1;
        const div = document.createElement('div');
        div.className = 'menu-item';
        if (item.tipo === 'especial') {
            div.classList.add('featured');
            const badge = document.createElement('div');
            badge.className = 'menu-item-badge';
            badge.textContent = 'Especial da Casa';
            div.appendChild(badge);
        }
        const img = document.createElement('div');
        img.className = 'menu-item-image';
        const imgPath = item.imagem 
            ? (item.imagem.startsWith('http') || item.imagem.startsWith('imagens/') ? item.imagem : 'imagens/' + item.imagem)
            : 'imagens/pratos/placeholder.jpg';
        img.innerHTML = `<img src="${imgPath}" alt="${escapeHtml(item.nome)}" onerror="this.style.display='none'; this.parentElement.style.background='#f0f0f0'; this.parentElement.innerHTML='<span style=color:#999>Sem imagem</span>';">`;
        div.appendChild(img);
        const content = document.createElement('div');
        content.className = 'menu-item-content';
        content.innerHTML = `
            <h3 class="menu-item-title">${escapeHtml(item.nome)}</h3>
            <p class="menu-item-description">${escapeHtml(item.descricao)}</p>
            <div class="menu-item-price">€${parseFloat(item.preco).toFixed(2).replace('.', ',')}</div>
        `;
        div.appendChild(content);
        container.appendChild(div);
    });
    sections.forEach((section, idx) => {
        const cats = ['entradas', 'principais', 'sobremesas', 'bebidas'];
        const cat = cats[idx];
        if (counters[cat] === 0 && section) {
            section.style.display = 'none';
        } else if (section) {
            section.style.display = '';
        }
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

async function loadPublicMenu() {
    try {
        const response = await fetch('../api/menu.php?public=true');
        const data = await response.json();
        if (data.sucesso && data.dados) {
            renderMenuFromData(data.dados);
            try { localStorage.setItem('menuItems_100sabores', JSON.stringify(data.dados)); } catch (e) {}
        }
    } catch (err) {
        console.error('Erro ao carregar ementa pública:', err);
        const saved = localStorage.getItem('menuItems_100sabores');
        if (saved) {
            try { renderMenuFromData(JSON.parse(saved)); } catch (e) {}
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    loadPublicMenu();
    window.addEventListener('storage', function(e) {
        if (e.key === 'menuItems_100sabores' && e.newValue) {
            try { renderMenuFromData(JSON.parse(e.newValue)); } catch (err) {}
        }
    });
});
