class Translator {
    constructor(languageFile) {
        this.languageFile = languageFile;
        this.translations = {};
    }

    async loadTranslations() {
        try {
            const response = await fetch(this.languageFile);
            if (!response.ok) {
                throw new Error(`Failed to load language file: ${response.status}`);
            }
            this.translations = await response.json();
        } catch (error) {
            console.error('Error loading translation file:', error);
            this.translations = {}; // Garantir que translations seja um objeto vazio em caso de falha
        }
    }

    translatePage() {
        document.querySelectorAll('[data-translate]').forEach(element => {
            const key = element.getAttribute('data-translate');
            if (key && this.translations[key]) {
                element.textContent = this.translations[key];
            }
        });
    }

    setupDynamicConfirmations() {
        document.querySelectorAll('[data-confirm]').forEach(element => {
            const confirmKey = element.getAttribute('data-confirm');
            if (confirmKey) {
                element.addEventListener('click', event => {
                    const message = this.translations[confirmKey] || confirmKey;
                    if (!confirm(message)) {
                        event.preventDefault();
                    }
                });
            }
        });
    }

    async init() {
        await this.loadTranslations();
        this.translatePage();
        this.setupDynamicConfirmations();
    }
}

// Inicializar o tradutor quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', async function () {
    const locale = document.body.getAttribute('data-locale') || 'en-EN';
    const translator = new Translator(`locales/${locale}.json`);
    try {
        await translator.init();
    } catch (error) {
        console.error('Error initializing translator:', error);
    }
});
