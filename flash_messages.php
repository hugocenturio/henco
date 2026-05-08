<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success fade show position-fixed top-0 start-50 translate-middle-x mt-3 shadow" style="z-index: 1055;" role="alert" onclick="this.classList.remove('show'); this.classList.add('fade');">
        <?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); ?>
        
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger fade show position-fixed top-0 start-50 translate-middle-x mt-3 shadow" style="z-index: 1055;" role="alert" onclick="this.classList.remove('show'); this.classList.add('fade');">
        <?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); ?>
        
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

