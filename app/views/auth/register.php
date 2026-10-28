<div class="auth-wrap">
    <div class="card auth-card">
        <section class="auth-hero">
            <h2>Your corner of the feed starts here.</h2>
            <p class="mb-0 opacity-75">Make an account in under a minute and say hello to everyone.</p>
            <ul class="perks">
                <li><i class="bi bi-person-heart"></i>Your own profile page</li>
                <li><i class="bi bi-chat-dots-fill"></i>Join the conversation</li>
                <li><i class="bi bi-shield-check"></i>Passwords stored securely</li>
            </ul>
        </section>

        <section class="auth-form">
            <h1 class="h3 fw-bold mb-1">Create your account</h1>
            <p class="text-muted mb-4">It's free and takes a few seconds.</p>

            <form action="<?= e(url('auth/register')) ?>" method="post" enctype="multipart/form-data"
                  class="needs-validation" novalidate>
                <?= csrf_field() ?>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="avatar-ring"><img id="avatarPreview" src="<?= e(avatar_url(null)) ?>" class="avatar avatar-lg" alt="Profile picture preview"></span>
                    <div class="flex-grow-1">
                        <label for="profile_image" class="form-label mb-1">Profile picture <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="file" class="form-control form-control-sm" id="profile_image" name="profile_image"
                               accept="image/jpeg,image/png,image/gif,image/webp" data-preview="#avatarPreview">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="full_name" class="form-label">Full name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required
                           maxlength="100" autocomplete="name" value="<?= old('full_name') ?>">
                    <div class="invalid-feedback">Please enter your full name.</div>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group has-validation">
                        <span class="input-group-text">@</span>
                        <input type="text" class="form-control" id="username" name="username" required
                               pattern="[A-Za-z0-9_]{3,30}" maxlength="30" autocomplete="username"
                               value="<?= old('username') ?>">
                        <div class="invalid-feedback">3-30 characters: letters, numbers, underscores.</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required
                               minlength="8" maxlength="128" autocomplete="new-password">
                        <div class="invalid-feedback">At least 8 characters.</div>
                    </div>
                    <div class="col-sm-6 mb-4">
                        <label for="confirm_password" class="form-label">Confirm</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                               required minlength="8" maxlength="128" autocomplete="new-password" data-match="password">
                        <div class="invalid-feedback">Passwords must match.</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">Sign up</button>
            </form>

            <p class="text-center mt-4 mb-0">
                Already have an account? <a class="fw-semibold" href="<?= e(url('auth/login')) ?>">Log in</a>
            </p>
        </section>
    </div>
</div>
