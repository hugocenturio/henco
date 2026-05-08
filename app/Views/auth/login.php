<?php /** @var string $message */ /** @var string $messageClass */ ?>
<style>
    .login-left { background-color:#7571f9;color:#fff;padding:4rem;display:flex;flex-direction:column;justify-content:center; }
    .login-right { padding:4rem; }
    .brand-title { font-size:3rem;font-weight:bold; }
</style>

<div class="container-fluid h-100">
    <div class="row h-100">
        <div class="col-md-6 d-none d-md-flex login-left">
            <div>
                <h1 class="brand-title text-white">Welcome to Henco!</h1>
                <p>Your trusted partner in managing field worker orders efficiently.</p>
            </div>
        </div>
        <div class="col-md-6 login-right">
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="w-100" style="max-width:400px;">
                    <h2 class="text-center mb-4">Login</h2>

                    <?php if (!empty($message)): ?>
                        <div class="alert <?= $messageClass === 'error' ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show" role="alert">
                            <?= e($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= url('/login') ?>" class="mt-3">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
