document.addEventListener('DOMContentLoaded', function () {
    const preloader = document.getElementById('preloader');

    document.addEventListener('click', function (event) {
        // Verify if element is a button
        if (event.target.tagName === 'BUTTON' || event.target.closest('button')) {
            const button = event.target.closest('button');

            // Verify if button is open or close modal
            const isModalButton = button.hasAttribute('data-bs-toggle') && button.getAttribute('data-bs-toggle') === 'modal';
            const isDismissButton = button.hasAttribute('data-bs-dismiss') && button.getAttribute('data-bs-dismiss') === 'modal';
            const isProcess = button.hasAttribute('data-bs-dismiss') && button.getAttribute('data-bs-dismiss') === 'process'; 
                

            // Exclude modal buttons
            if (!isModalButton && !isDismissButton && !isProcess) {
                // Mostra o preloader
                preloader.style.display = 'flex';
            }
        }
    });
});
