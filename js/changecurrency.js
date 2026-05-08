// Script para alterar todos os símbolos de Euro (€) para Kwanza (Kz)
document.addEventListener("DOMContentLoaded", function() {
    // Seleciona todos os elementos que contêm texto
    const elements = document.querySelectorAll("*");

    elements.forEach((element) => {
        // Verifica se o elemento tem texto com o símbolo de Euro (€)
        if (element.childNodes && element.childNodes.length) {
            element.childNodes.forEach((node) => {
                if (node.nodeType === Node.TEXT_NODE) {
                    // Substitui o símbolo de Euro (€) pelo de Kwanza (Kz)
                    node.nodeValue = node.nodeValue.replace(/€/g, "Kz");
                }
            });
        }
    });
});
