let translations = {};
let currentLocale = 'en-EN';

// Função para carregar traduções
async function loadTranslations(locale = 'en-EN') {
    try {
        const response = await fetch(`locales/${locale}.json`);
        if (!response.ok) {
            throw new Error(`Could not load translations for locale: ${locale}`);
        }
        translations = await response.json();
        currentLocale = locale;
    } catch (error) {
        console.error('Error loading translations:', error);
    }
}

// Função para obter tradução
function getTranslation(key) {
    return translations[key] || key; // Retorna o valor traduzido ou a chave original
}


// Função para aplicar traduções aos elementos com atributo data-translate
function applyTranslations() {
    document.querySelectorAll('[data-translate]').forEach(element => {
        const key = element.getAttribute('data-translate');
        if (translations[key]) {
            element.textContent = translations[key];
        }
    });
}

// Inicialização global
document.addEventListener('DOMContentLoaded', async function () {
    const locale = document.body.getAttribute('data-locale') || 'en-EN';

    // Carrega as traduções
    await loadTranslations(locale);

    // Aplica as traduções
    applyTranslations();

    // Inicializa DataTables
    initializeDataTable('.dataTable');
});
