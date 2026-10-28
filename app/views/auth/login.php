<div class="auth-wrap">
    <div class="card auth-card">
        <section class="auth-hero">
            <h2>Share the little moments.</h2>
            <p class="mb-0 opacity-75">Post updates, swap comments, and find people who make your feed worth scrolling.</p>
            <ul class="perks">
                <li><i class="bi bi-images"></i>Photo &amp; text posts</li>
                <li><i class="bi bi-heart-fill"></i>Likes and comments</li>
                <li><i class="bi bi-compass"></i>Discover new people</li>
            </ul>
        </section>

        <section class="auth-form">
            <h1 class="h3 fw-bold mb-1">Welcome back</h1>
            <p class="text-muted mb-4">Log in to see what's new.</p>

            <form action="<?= e(url('auth/login')) ?>" method="post" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required
                           maxlength="30" autocomplete="username" value="<?= old('username') ?>">
                    <div class="invalid-feedback">Please enter your username.</div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required
                           maxlength="128" autocomplete="current-password">
                    <div class="invalid-feedback">Please enter your password.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">Log in</button>
            </form>

            <p class="text-center mt-4 mb-0">
                New here? <a class="fw-semibold" href="<?= e(url('auth/register')) ?>">Create an account</a>
            </p>
        </section>
    </div>
</div>
