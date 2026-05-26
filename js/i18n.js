// ============================================
// INTERNACIONALIZAÇÃO - 100 Sabores
// Sistema i18n com data-i18n (Vanilla JS)
// ============================================

// === ESTRUTURA DE TRADUÇÕES ===
const i18n = {
    pt: {
        // Header/Navegação
        "nav-home": "HOME",
        "nav-sobre": "SOBRE NÓS",
        "nav-menu": "EMENTA",
        "nav-vinhos": "VINHOS",
        "nav-galeria": "GALERIA",
        "nav-bar": "Bar",
        "nav-contacto": "CONTACTO",
        "nav-reservas": "RESERVAS",

        // Hero Section
        "hero-label": "RESERVE A SUA EXPERIÊNCIA",
        "hero-title": "100 Sabores",
        "hero-tagline": "Garanta o seu lugar no 100 Sabores",
        "hero-address": "Largo da Trindade, Príncipe Real",
        "hero-btn-reserva": "Fazer Reserva",
        "hero-btn-menu": "Ver Ementa",
        "hero-hours-1": "Seg-Sáb: 12h-00h",
        "hero-hours-2": "Dom: 18h-21h",

        // Sobre
        "sobre-label": "SOBRE NÓS",
        "sobre-title": "Uma Casa Sem Tempo",
        "sobre-text-1": "O 100 Sabores nasce de um sonho: criar um espaço onde a alta classe é prioridade, onde comer é mais que uma experiência é algo exclusivo...",
        "sobre-text-2": "Com dois andares e capacidade para 80 pessoas, o restaurante oferece um ambiente acolhedor e elegante.",
        "sobre-wine": "Referências de Vinho",
        "sobre-seats": "Lugares",
        "sobre-floors": "Andares",

        // Features
        "feature-1-title": "Pratos de Autor",
        "feature-1-text": "Criações únicas inspiradas na gastrónomia de alto padrão com influências mediterrânicas e europeias. O nosso chef traz viagens, vivências e experiências a cada prato.",
        "feature-2-title": "Carta de Vinhos",
        "feature-2-text": "Mais de 200 referências nacionais e internacionais, cuidadosamente selecionadas pelo nosso sommelier para harmonizar com a nossa cozinha.",
        "feature-3-title": "Cocktails & Bar",
        "feature-3-text": "Uma carta de cocktails que reflete a essência do 100 Sabores, com ingredientes naturais e criações exclusivas.",

        // Quote
        "quote-text": "100 Sabores não é apenas um restaurante. É família que come e sente, que bebe e deseja, que olha o mundo com ganas de o sorver.",
        "quote-link": "Explorar a Ementa",

        // Menu Preview
        "menu-label": "DESTAQUES",
        "menu-title": "O Que Não Pode Perder",
        "menu-item-1": "Especialidades da Casa",
        "menu-item-1-text": "Criações únicas do nosso chef, feitas com paixão e inovação.",
        "menu-item-2": "Pratos Diários",
        "menu-item-2-text": "Produtos da época, proteínas do dia sempre frescos.",
        "menu-item-3": "Bebidas & Acompanhamentos",
        "menu-item-3-text": "Vinhos, batatas, legumes e molhos para partilhar.",
        "menu-item-4": "Sobremesas",
        "menu-item-4-text": "O inevitável final feliz — bem doce!",
        "menu-link": "Ver Ementa Completa",

        // Info Section
        "info-title-1": "Horário",
        "info-text-1a": "Segunda a Quinta: 12h - 22h",
        "info-text-1b": "Sexta a Sábado: 12h - 00h",
        "info-text-1c": "Domingo: 12h - 16h",
        "info-title-2": "Localização",
        "info-text-2a": "Largo da Trindade",
        "info-text-2b": "Príncipe Real, Lisboa",
        "info-text-2c": "Metro: Rato",
        "info-title-3": "Reservas",
        "info-text-3a": "Reservas online",
        "info-text-3b": "Eventos privados",
        "info-text-3c": "Jantares de grupo",

        // Footer
        "footer-desc": "Cozinha com exclusividade no coração de Lisboa.",
        "footer-contact": "Contactos",
        "footer-follow": "Siga-nos",
        "footer-copyright": "© 2025 Restaurante 100 Sabores. Todos os direitos reservados."
    },

    en: {
        // Header/Navigation
        "nav-home": "HOME",
        "nav-sobre": "ABOUT US",
        "nav-menu": "MENU",
        "nav-vinhos": "WINES",
        "nav-galeria": "GALLERY",
        "nav-bar": "Bar",
        "nav-contacto": "CONTACT",
        "nav-reservas": "RESERVATIONS",

        // Hero Section
        "hero-label": "RESERVE YOUR EXPERIENCE",
        "hero-title": "100 Flavours",
        "hero-tagline": "Secure your spot at 100 Flavours",
        "hero-address": "Largo da Trindade, Príncipe Real",
        "hero-btn-reserva": "Make a Reservation",
        "hero-btn-menu": "View Menu",
        "hero-hours-1": "Mon-Sat: 12pm-12am",
        "hero-hours-2": "Sun: 6pm-9pm",

        // About
        "sobre-label": "ABOUT US",
        "sobre-title": "A Timeless House",
        "sobre-text-1": "100 Flavours was born from a dream: to create a space where high class is the priority, where eating is more than an experience — it's something exclusive...",
        "sobre-text-2": "With two floors and capacity for 80 people, the restaurant offers a welcoming and elegant atmosphere.",
        "sobre-wine": "Wine References",
        "sobre-seats": "Seats",
        "sobre-floors": "Floors",

        // Features
        "feature-1-title": "Signature Dishes",
        "feature-1-text": "Unique creations inspired by high-end gastronomy with Mediterranean and European influences. Our chef brings journeys, experiences and stories to every plate.",
        "feature-2-title": "Wine List",
        "feature-2-text": "Over 200 national and international references, carefully selected by our sommelier to pair with our cuisine.",
        "feature-3-title": "Cocktails & Bar",
        "feature-3-text": "A cocktail list that reflects the essence of 100 Flavours, with natural ingredients and exclusive creations.",

        // Quote
        "quote-text": "100 Flavours is not just a restaurant. It's family that eats and feels, that drinks and desires, that looks at the world with hunger to savour it.",
        "quote-link": "Explore the Menu",

        // Menu Preview
        "menu-label": "HIGHLIGHTS",
        "menu-title": "What You Can't Miss",
        "menu-item-1": "House Specialties",
        "menu-item-1-text": "Unique creations from our chef, made with passion and innovation.",
        "menu-item-2": "Daily Dishes",
        "menu-item-2-text": "Seasonal products, fresh proteins every day.",
        "menu-item-3": "Drinks & Sides",
        "menu-item-3-text": "Wines, potatoes, vegetables and sauces to share.",
        "menu-item-4": "Desserts",
        "menu-item-4-text": "The inevitable happy ending — delightfully sweet!",
        "menu-link": "View Full Menu",

        // Info Section
        "info-title-1": "Opening Hours",
        "info-text-1a": "Monday to Thursday: 12pm - 10pm",
        "info-text-1b": "Friday to Saturday: 12pm - 12am",
        "info-text-1c": "Sunday: 12pm - 4pm",
        "info-title-2": "Location",
        "info-text-2a": "Largo da Trindade",
        "info-text-2b": "Príncipe Real, Lisbon",
        "info-text-2c": "Metro: Rato",
        "info-title-3": "Reservations",
        "info-text-3a": "Online reservations",
        "info-text-3b": "Private events",
        "info-text-3c": "Group dinners",

        // Footer
        "footer-desc": "Exclusive cuisine in the heart of Lisbon.",
        "footer-contact": "Contact",
        "footer-follow": "Follow us",
        "footer-copyright": "© 2025 100 Flavours. All rights reserved."
    }
};

// ============================================
// FUNÇÃO PRINCIPAL DE TRADUÇÃO
// ============================================

function mudarIdioma(idioma) {
    // Validação
    if (!i18n[idioma]) {
        console.warn(`Idioma "${idioma}" não suportado`);
        return;
    }

    // Persistir escolha
    localStorage.setItem('idioma', idioma);

    // Aplicar traduções a elementos com data-i18n
    const textos = i18n[idioma];

    document.querySelectorAll('[data-i18n]').forEach(elemento => {
        const chave = elemento.getAttribute('data-i18n');
        if (textos[chave]) {
            elemento.textContent = textos[chave];
        }
    });

    // Atualizar atributo lang do HTML
    document.documentElement.lang = idioma;

    // Atualizar botões ativos
    document.querySelectorAll('[data-lang-btn]').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.langBtn === idioma);
    });
}

// ============================================
// INICIALIZAÇÃO AUTOMÁTICA
// ============================================

function obterIdiomaInicial() {
    // Prioridade: localStorage > navegador > padrão
    const idiomaSalvo = localStorage.getItem('idioma');
    if (idiomaSalvo && i18n[idiomaSalvo]) {
        return idiomaSalvo;
    }

    // Detectar idioma do navegador
    const idiomaNav = (navigator.language || 'pt').split('-')[0];
    if (i18n[idiomaNav]) {
        return idiomaNav;
    }

    return 'pt'; // Padrão fallback
}

// Executar na carga da página
document.addEventListener('DOMContentLoaded', () => {
    const idiomaInicial = obterIdiomaInicial();
    mudarIdioma(idiomaInicial);
});

// Exportar função globalmente
window.mudarIdioma = mudarIdioma;