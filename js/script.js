/* ============================================================
   script.js — Efeitos gerais do site (usado em todas as páginas)
   Contém: barra de navegação, efeito parallax, animações de
   scroll e o menu hambúrguer para telemóvel.
   ============================================================ */

// Quando a página termina de carregar, arranca tudo.
document.addEventListener('DOMContentLoaded', function () {
    ativarNavbarScroll();
    ativarParallax();
    ativarAnimacoesScroll();
    ativarMenuHamburguer();
});


/* ------------------------------------------------------------
   1) BARRA DE NAVEGAÇÃO
   Adiciona a classe "scrolled" à navbar quando o utilizador
   faz scroll para baixo (para mudar o estilo, ex: fundo sólido).
   ------------------------------------------------------------ */
function ativarNavbarScroll() {
    const navbar = document.querySelector('.main-navigation');
    if (!navbar) return; // Se a página não tiver navbar, não faz nada.

    // Função que decide se a navbar deve ter a classe "scrolled".
    function atualizar() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }

    window.addEventListener('scroll', atualizar);
    atualizar(); // Verifica logo o estado inicial ao abrir a página.
}


/* ------------------------------------------------------------
   2) EFEITO PARALLAX
   Faz a imagem de fundo mover-se mais devagar que o scroll,
   dando sensação de profundidade. Aplica-se ao hero da página
   inicial (.hero-section) e ao hero do menu (.menu-hero).
   ------------------------------------------------------------ */
function ativarParallax() {
    // Lista das secções que têm fundo parallax.
    const seccoes = ['.hero-section', '.menu-hero'];

    seccoes.forEach(function (seletor) {
        const seccao = document.querySelector(seletor);
        if (!seccao) return; // Salta se essa secção não existir na página.

        const fundo = seccao.querySelector('.parallax-bg');
        if (!fundo) return;

        // Ao fazer scroll, desloca o fundo proporcionalmente.
        window.addEventListener('scroll', function () {
            const deslocamento = window.pageYOffset * 0.3;
            fundo.style.transform =
                `translateZ(-1px) scale(1.5) translateY(${deslocamento}px)`;
        });
    });
}


/* ------------------------------------------------------------
   3) ANIMAÇÕES AO FAZER SCROLL
   Os elementos começam invisíveis e deslocados para baixo;
   quando entram no ecrã, aparecem suavemente (fade + subida).
   Usa IntersectionObserver para detetar quando ficam visíveis.
   ------------------------------------------------------------ */
function ativarAnimacoesScroll() {
    // Todos os elementos que devem ser animados.
    const seletores = [
        '.hero-content > *',
        '.intro-section', '.intro-content', '.feature-card', '.quote-section',
        '.menu-preview-section', '.info-section',
        '.menu-item', '.menu-section', '.story-paragraph',
        '.caracteristica-item', '.destaque-restaurante',
        '.story-text', '.final-section', '.final-content',
        '.menu-items-grid > div', '.caracteristicas > div',
        '.intro-text-side', '.intro-images', '.intro-feature',
        '.menu-preview-item', '.info-card', '.feature-number'
    ];

    const elementos = document.querySelectorAll(seletores.join(', '));

    // Se o navegador for muito antigo e não tiver IntersectionObserver,
    // mostramos tudo logo (sem animação) para garantir que aparece.
    if (!('IntersectionObserver' in window)) {
        elementos.forEach(function (el) {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        });
        return;
    }

    // O observador "observa" cada elemento e reage quando fica visível.
    const observador = new IntersectionObserver(function (entradas) {
        entradas.forEach(function (entrada) {
            if (!entrada.isIntersecting) return; // Ainda não está no ecrã.

            const el = entrada.target;
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
            el.style.transition =
                'opacity 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94), ' +
                'transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94)';

            observador.unobserve(el); // Já animou, deixa de observar.
        });
    }, { threshold: 0.1 }); // Dispara quando 10% do elemento está visível.

    // Estado inicial: invisível e deslocado. Depois manda observar.
    elementos.forEach(function (el, indice) {
        el.style.opacity = '0';
        el.style.transform = 'translateY(40px)';
        el.style.transitionDelay = (indice * 0.05) + 's'; // Efeito em cascata.
        observador.observe(el);
    });
}


/* ------------------------------------------------------------
   4) MENU HAMBÚRGUER (telemóvel)
   Abre/fecha o menu lateral em ecrãs pequenos e trata de o
   fechar nas situações certas (clicar num link, redimensionar
   para desktop, ou clicar fora do menu).
   ------------------------------------------------------------ */
function ativarMenuHamburguer() {
    const botao = document.querySelector('.menu-toggle');
    const menu = document.querySelector('.nav-menu');
    const dropdowns = document.querySelectorAll('.dropdown');

    // Considera-se "telemóvel" quando a largura do ecrã é <= 768px.
    function ehTelemovel() {
        return window.innerWidth <= 768;
    }

    // Fecha o menu e volta a permitir scroll na página.
    function fecharMenu() {
        if (menu) menu.classList.remove('active');
        if (botao) botao.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Abrir/fechar ao clicar no botão hambúrguer.
    if (botao && menu) {
        botao.addEventListener('click', function () {
            botao.classList.toggle('active');
            const aberto = menu.classList.toggle('active');
            // Bloqueia o scroll do fundo enquanto o menu está aberto.
            document.body.style.overflow = aberto ? 'hidden' : '';
        });
    }

    // Fechar o menu ao clicar num link normal (não nos dropdowns).
    if (menu) {
        const links = menu.querySelectorAll('.nav-link:not(.dropdown-toggle)');
        links.forEach(function (link) {
            link.addEventListener('click', function () {
                if (ehTelemovel()) fecharMenu();
            });
        });
    }

    // Abrir/fechar cada submenu (dropdown) no telemóvel.
    dropdowns.forEach(function (dropdown) {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        if (!toggle) return;
        toggle.addEventListener('click', function (e) {
            if (ehTelemovel()) {
                e.preventDefault(); // Não navega, só abre o submenu.
                dropdown.classList.toggle('active');
            }
        });
    });

    // Se o ecrã passar a desktop, garante que o menu fica fechado.
    window.addEventListener('resize', function () {
        if (!ehTelemovel()) fecharMenu();
    });

    // Fechar o menu ao clicar fora dele (no telemóvel).
    document.addEventListener('click', function (e) {
        const menuAberto = menu && menu.classList.contains('active');
        const clicouFora = menu && !menu.contains(e.target) &&
                           botao && !botao.contains(e.target);
        if (ehTelemovel() && menuAberto && clicouFora) {
            fecharMenu();
        }
    });
}
