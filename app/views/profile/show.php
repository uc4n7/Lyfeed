<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-6">

        <div class="card profile-card mb-4">
            <div class="profile-cover c<?= (int)$profile['id'] % 4 ?>" <?= !empty($profile['cover_image']) ? 'style="background-image: url(\'' . e(upload_url($profile['cover_image'])) . '\'); background-size: cover; background-position: center ' . (int)($profile['cover_position'] ?? 50) . '%;"' : '' ?>>
                <?php if ($isOwner): ?>
                    <a href="<?= e(url('profile/edit')) ?>" class="btn btn-sm btn-light position-absolute bottom-0 end-0 m-3 shadow-sm rounded-pill py-1 px-3" style="z-index: 2; font-size: 0.82rem;">
                        <i class="bi bi-camera-fill me-1"></i>Edit cover
                    </a>
                <?php endif; ?>
            </div>
            <div class="profile-body">
                <span class="avatar-ring">
                    <img src="<?= e(avatar_url($profile['profile_image'])) ?>" alt="Profile photo of <?= e($profile['full_name']) ?>"
                         class="avatar avatar-xl">
                </span>

                <h1 class="h3 fw-bold mt-3 mb-0"><?= e($profile['full_name']) ?></h1>
                <p class="text-muted mb-3">@<?= e($profile['username']) ?></p>

                <p class="mx-auto mb-3" style="max-width: 460px;">
                    <?= $profile['bio'] ? nl2br(e($profile['bio'])) : '<span class="text-muted fst-italic">No bio yet.</span>' ?>
                </p>

                <div class="d-flex justify-content-center flex-wrap gap-2 mb-3">
                    <span class="stat-pill"><i class="bi bi-grid-fill" style="color: var(--brand-1);"></i><strong><?= (int)$postCount ?></strong> posts</span>
                    <span class="stat-pill"><i class="bi bi-calendar-heart-fill" style="color: var(--brand-2);"></i>Joined <?= e(date('M Y', strtotime($profile['created_at']))) ?></span>
                </div>

                <?php if ($isOwner): ?>
                    <a href="<?= e(url('profile/edit')) ?>" class="btn btn-outline-primary px-4">
                        <i class="bi bi-pencil me-1"></i>Edit profile
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <h2 class="h5 section-title mb-3 px-1">Posts</h2>

        <?php if (!$posts): ?>
            <div class="card text-center text-muted py-5">
                <i class="bi bi-camera display-4" style="color: var(--brand-2);"></i>
                <p class="mt-2 mb-0">No posts yet.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($posts as $post): ?>
            <?php require BASE_PATH . '/app/views/partials/post.php'; ?>
        <?php endforeach; ?>
    </div>
</div>
