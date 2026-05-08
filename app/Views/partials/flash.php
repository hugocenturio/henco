<?php
$messages = flash_pull();
foreach ($messages as $type => $list):
    $cls = $type === 'success' ? 'alert-success' : ($type === 'error' ? 'alert-danger' : 'alert-info');
    foreach ((array) $list as $msg):
?>
<div class="alert <?= $cls ?> fade show position-fixed top-0 start-50 translate-middle-x mt-3 shadow"
     style="z-index:1055" role="alert"
     onclick="this.classList.remove('show'); this.classList.add('fade');">
    <?= e($msg) ?>
</div>
<?php endforeach; endforeach; ?>
