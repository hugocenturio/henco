<?php /** @var string $message */ /** @var string $messageClass */ ?>
<div class="login-form-bg h-100">
    <div class="container h-100">
        <div class="row justify-content-center h-100">
            <div class="col-xl-6">
                <div class="error-content">
                    <div class="card mb-0">
                        <div class="card-body text-center">
                            <h1 class="error-text text-primary">Thank You!</h1>
                            <h4 class="mt-4"><i class="fa fa-thumbs-up"></i> You're in!</h4>
                            <div class="activation-container">
                                <h2>Activation Successful</h2>
                                <p class="message <?= e($messageClass) ?>"><?= e($message) ?></p>
                                <?php if ($messageClass === 'success'): ?>
                                    <a href="<?= url('/login') ?>" class="login-link">Click here to log in</a>
                                <?php endif; ?>
                            </div>
                            <div class="text-center mb-4 mt-4">
                                <a href="<?= url('/login') ?>" class="btn btn-primary">Sign in here</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
