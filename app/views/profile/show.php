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
                    <button type="button" class="stat-pill border-0" data-bs-target="#tab-posts" role="button">
                        <i class="bi bi-grid-fill" style="color: var(--brand-1);"></i><strong><?= (int)$postCount ?></strong> posts
                    </button>
                    <button type="button" class="stat-pill border-0" data-bs-target="#tab-followers" role="button">
                        <i class="bi bi-people-fill text-primary"></i><strong><?= (int)$followerCount ?></strong> followers
                    </button>
                    <button type="button" class="stat-pill border-0" data-bs-target="#tab-following" role="button">
                        <i class="bi bi-person-check-fill text-success"></i><strong><?= (int)$followingCount ?></strong> following
                    </button>
                    <span class="stat-pill"><i class="bi bi-calendar-heart-fill" style="color: var(--brand-2);"></i>Joined <?= e(date('M Y', strtotime($profile['created_at']))) ?></span>
                </div>

                <?php if ($isOwner): ?>
                    <a href="<?= e(url('profile/edit')) ?>" class="btn btn-outline-primary px-4">
                        <i class="bi bi-pencil me-1"></i>Edit profile
                    </a>
                <?php else: ?>
                    <form action="<?= e(url('follow/toggle')) ?>" method="post" class="d-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="user_id" value="<?= (int)$profile['id'] ?>">
                        <input type="hidden" name="tab" value="<?= e($activeTab) ?>">
                        <?php if ($isFollowing): ?>
                            <button type="submit" class="btn btn-outline-danger px-4 rounded-pill">
                                <i class="bi bi-person-dash-fill me-1"></i>Unfollow
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-primary px-4 rounded-pill">
                                <i class="bi bi-person-plus-fill me-1"></i>Follow
                            </button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills justify-content-center mb-4" id="profileTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'posts' ? 'active' : '' ?>" id="posts-tab" data-bs-toggle="pill" data-bs-target="#tab-posts" type="button" role="tab" aria-controls="tab-posts" aria-selected="<?= $activeTab === 'posts' ? 'true' : 'false' ?>">
                    <i class="bi bi-grid-fill me-1"></i>Posts <span class="badge bg-secondary-subtle text-secondary ms-1"><?= (int)$postCount ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'followers' ? 'active' : '' ?>" id="followers-tab" data-bs-toggle="pill" data-bs-target="#tab-followers" type="button" role="tab" aria-controls="tab-followers" aria-selected="<?= $activeTab === 'followers' ? 'true' : 'false' ?>">
                    <i class="bi bi-people-fill me-1"></i>Followers <span class="badge bg-secondary-subtle text-secondary ms-1"><?= (int)$followerCount ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'following' ? 'active' : '' ?>" id="following-tab" data-bs-toggle="pill" data-bs-target="#tab-following" type="button" role="tab" aria-controls="tab-following" aria-selected="<?= $activeTab === 'following' ? 'true' : 'false' ?>">
                    <i class="bi bi-person-check-fill me-1"></i>Following <span class="badge bg-secondary-subtle text-secondary ms-1"><?= (int)$followingCount ?></span>
                </button>
            </li>
        </ul>

        <!-- Tab Panes -->
        <div class="tab-content" id="profileTabContent">
            <!-- Posts Pane -->
            <div class="tab-pane fade <?= $activeTab === 'posts' ? 'show active' : '' ?>" id="tab-posts" role="tabpanel" aria-labelledby="posts-tab" tabindex="0">
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

            <!-- Followers Pane -->
            <div class="tab-pane fade <?= $activeTab === 'followers' ? 'show active' : '' ?>" id="tab-followers" role="tabpanel" aria-labelledby="followers-tab" tabindex="0">
                <h2 class="h5 section-title mb-3 px-1">Followers</h2>
                <?php if (!$followers): ?>
                    <div class="card text-center text-muted py-5">
                        <i class="bi bi-people display-4 text-muted"></i>
                        <p class="mt-2 mb-0"><?= $isOwner ? "You don't have any followers yet." : e($profile['full_name']) . " doesn't have any followers yet." ?></p>
                    </div>
                <?php else: ?>
                    <div class="list-group shadow-sm">
                        <?php foreach ($followers as $f): ?>
                            <div class="list-group-item d-flex align-items-center justify-content-between gap-3 p-3">
                                <a href="<?= e(url('profile/show', ['id' => $f['id']])) ?>" class="d-flex align-items-center gap-3 text-decoration-none text-body flex-grow-1 min-w-0">
                                    <img src="<?= e(avatar_url($f['profile_image'])) ?>" alt="" class="avatar flex-shrink-0">
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-truncate"><?= e($f['full_name']) ?></div>
                                        <div class="text-muted small">@<?= e($f['username']) ?></div>
                                        <?php if (!empty($f['bio'])): ?>
                                            <div class="small text-muted text-truncate mt-1"><?= e(mb_strimwidth($f['bio'], 0, 90, '...')) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                <?php if (current_user_id() !== (int)$f['id']): ?>
                                    <form action="<?= e(url('follow/toggle')) ?>" method="post" class="ms-2 flex-shrink-0">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="user_id" value="<?= (int)$f['id'] ?>">
                                        <input type="hidden" name="tab" value="followers">
                                        <?php if (!empty($f['is_following'])): ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                <i class="bi bi-person-dash me-1"></i>Unfollow
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bi bi-person-plus-fill me-1"></i>Follow
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Following Pane -->
            <div class="tab-pane fade <?= $activeTab === 'following' ? 'show active' : '' ?>" id="tab-following" role="tabpanel" aria-labelledby="following-tab" tabindex="0">
                <h2 class="h5 section-title mb-3 px-1">Following</h2>
                <?php if (!$following): ?>
                    <div class="card text-center text-muted py-5">
                        <i class="bi bi-person-plus display-4 text-muted"></i>
                        <p class="mt-2 mb-0"><?= $isOwner ? "You are not following anyone yet." : e($profile['full_name']) . " is not following anyone yet." ?></p>
                    </div>
                <?php else: ?>
                    <div class="list-group shadow-sm">
                        <?php foreach ($following as $f): ?>
                            <div class="list-group-item d-flex align-items-center justify-content-between gap-3 p-3">
                                <a href="<?= e(url('profile/show', ['id' => $f['id']])) ?>" class="d-flex align-items-center gap-3 text-decoration-none text-body flex-grow-1 min-w-0">
                                    <img src="<?= e(avatar_url($f['profile_image'])) ?>" alt="" class="avatar flex-shrink-0">
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-truncate"><?= e($f['full_name']) ?></div>
                                        <div class="text-muted small">@<?= e($f['username']) ?></div>
                                        <?php if (!empty($f['bio'])): ?>
                                            <div class="small text-muted text-truncate mt-1"><?= e(mb_strimwidth($f['bio'], 0, 90, '...')) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                <?php if (current_user_id() !== (int)$f['id']): ?>
                                    <form action="<?= e(url('follow/toggle')) ?>" method="post" class="ms-2 flex-shrink-0">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="user_id" value="<?= (int)$f['id'] ?>">
                                        <input type="hidden" name="tab" value="following">
                                        <?php if (!empty($f['is_following'])): ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                <i class="bi bi-person-dash me-1"></i>Unfollow
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bi bi-person-plus-fill me-1"></i>Follow
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.stat-pill[data-bs-target]').forEach(function(pill) {
        pill.addEventListener('click', function() {
            var targetSelector = this.getAttribute('data-bs-target');
            var tabBtn = document.querySelector('[data-bs-target="' + targetSelector + '"][data-bs-toggle="pill"]');
            if (tabBtn && window.bootstrap && window.bootstrap.Tab) {
                bootstrap.Tab.getOrCreateInstance(tabBtn).show();
            }
        });
    });
});
</script>
