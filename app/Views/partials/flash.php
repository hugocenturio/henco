<?php
$messages = flash_pull();

// Legacy success_message / error_message keys — keep working for any code
// that still writes directly into them.
foreach (['success' => 'success_message', 'error' => 'error_message'] as $type => $legacy) {
    if (!empty($_SESSION[$legacy])) {
        $messages[$type][] = $_SESSION[$legacy];
        unset($_SESSION[$legacy]);
    }
}

if (empty($messages)) return;
?>
<div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index:1080;">
    <?php foreach ($messages as $type => $list):
        $cls = $type === 'success' ? 'text-bg-success'
             : ($type === 'error'  ? 'text-bg-danger'
             : ($type === 'warning' ? 'text-bg-warning' : 'text-bg-info'));
        $icon = $type === 'success' ? 'fa-circle-check'
              : ($type === 'error'  ? 'fa-triangle-exclamation'
              : ($type === 'warning' ? 'fa-circle-exclamation' : 'fa-circle-info'));
        foreach ((array) $list as $msg): ?>
            <div class="toast align-items-center <?= $cls ?> border-0 mb-2 show"
                 role="<?= $type === 'error' ? 'alert' : 'status' ?>"
                 aria-live="<?= $type === 'error' ? 'assertive' : 'polite' ?>" aria-atomic="true"
                 data-bs-autohide="true" data-bs-delay="<?= $type === 'error' ? 6000 : 4000 ?>">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fa-solid <?= $icon ?> me-2"></i><?= e($msg) ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
    <?php endforeach; endforeach; ?>
</div>
<script>
(function () {
    function init() {
        document.querySelectorAll('.toast.show').forEach(el => {
            try { new bootstrap.Toast(el).show(); } catch (_) {}
        });
    }
    if (document.readyState !== 'loading') init();
    else document.addEventListener('DOMContentLoaded', init);
})();
</script>
