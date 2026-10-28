<div class="row g-4 justify-content-center fb-layout">

    <!-- Left column: Sleek Modern Navigation Sidebar -->
    <aside class="col-lg-4 col-xl-3 d-none d-lg-block">
        <div class="side-sticky">
            <div class="sidebar-card">
                <!-- Cover Banner -->
                <div class="sidebar-cover" <?= !empty($me['cover_image']) ? 'style="background-image: url(\'' . e(upload_url($me['cover_image'])) . '\'); background-position: center ' . (int)($me['cover_position'] ?? 50) . '%;"' : '' ?>></div>

                <!-- User Profile Identity -->
                <div class="sidebar-profile-body">
                    <a href="<?= e(url('profile/show', ['id' => $me['id']])) ?>" class="sidebar-avatar-link" title="View profile">
                        <span class="avatar-ring">
                            <img src="<?= e(avatar_url($me['profile_image'])) ?>" alt="<?= e($me['full_name']) ?>" class="avatar">
                        </span>
                    </a>
                    <a href="<?= e(url('profile/show', ['id' => $me['id']])) ?>" class="d-block text-decoration-none">
                        <div class="sidebar-name text-truncate"><?= e($me['full_name']) ?></div>
                        <div class="sidebar-username">@<?= e($me['username']) ?></div>
                    </a>
                </div>

                <hr class="sidebar-divider">

                <!-- Navigation Links -->
                <div class="sidebar-nav-wrap">
                    <ul class="sidebar-nav">
                        <li>
                            <a href="<?= e(url('feed/index')) ?>" class="sidebar-link active">
                                <span class="sidebar-icon icon-feed"><i class="bi bi-newspaper"></i></span>
                                <span class="sidebar-text">Feeds</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= e(url('search/index', ['type' => 'users'])) ?>" class="sidebar-link">
                                <span class="sidebar-icon icon-friends"><i class="bi bi-people-fill"></i></span>
                                <span class="sidebar-text">Friends</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= e(url('profile/show', ['id' => $me['id']])) ?>" class="sidebar-link">
                                <span class="sidebar-icon icon-profile"><i class="bi bi-person-badge-fill"></i></span>
                                <span class="sidebar-text">My Profile</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= e(url('profile/edit')) ?>" class="sidebar-link">
                                <span class="sidebar-icon icon-settings"><i class="bi bi-gear-fill"></i></span>
                                <span class="sidebar-text">Settings</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Sidebar Footer Quick Actions -->
                <div class="sidebar-footer">
                    <a href="<?= e(url('profile/edit')) ?>" class="small text-muted text-decoration-none d-flex align-items-center gap-1">
                        <i class="bi bi-sliders"></i> Edit Profile
                    </a>
                    <form action="<?= e(url('auth/logout')) ?>" method="post" class="d-inline m-0">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-link btn-sm text-danger text-decoration-none p-0 border-0" title="Log out" style="font-size: 0.82rem;">
                            <i class="bi bi-box-arrow-right"></i> Log out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Center column: Newsfeed (Centered & optimal width) -->
    <section class="col-12 col-lg-8 col-xl-6 feed-center-col">

        <!-- Discover / People (shown here on smaller screens where right sidebar hides) -->
        <?php if ($people): ?>
            <div class="discover-minimal mb-3 d-xl-none" aria-label="Discover people">
                <div class="discover-minimal-track">
                    <?php foreach ($people as $person): ?>
                        <a href="<?= e(url('profile/show', ['id' => $person['id']])) ?>" class="discover-user">
                            <span class="avatar-ring-minimal">
                                <img src="<?= e(avatar_url($person['profile_image'])) ?>" alt="" class="avatar avatar-sm">
                            </span>
                            <span class="discover-name"><?= e(explode(' ', $person['full_name'])[0]) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Facebook Composer Card -->
        <div class="card fb-card fb-composer mb-3 scroll-offset" id="composer">
            <div class="card-body p-3">
                <form action="<?= e(url('post/store')) ?>" method="post" enctype="multipart/form-data"
                      class="needs-validation" novalidate id="composerForm">
                    <?= csrf_field() ?>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <img src="<?= e(avatar_url($me['profile_image'])) ?>" alt="" class="avatar avatar-sm flex-shrink-0">
                        <div class="flex-grow-1 min-w-0">
                            <textarea name="content" id="composerTextarea" class="fb-composer-input form-control" rows="2" maxlength="2000" required
                                      placeholder="What's on your mind, <?= e(explode(' ', $me['full_name'])[0]) ?>?"
                                      aria-label="What's on your mind?"></textarea>
                            <div class="invalid-feedback">Write something before posting.</div>
                        </div>
                    </div>

                    <div id="previewContainer" class="position-relative d-none my-2">
                        <img id="postPreview" class="img-fluid rounded-3" style="max-height: 260px; width: 100%; object-fit: cover;" alt="Image preview">
                        <button type="button" id="clearPreviewBtn" class="btn btn-sm btn-dark position-absolute top-0 end-0 m-2 rounded-circle" style="width:26px;height:26px;padding:0;line-height:1;" aria-label="Remove image" title="Remove image">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>

                    <hr class="fb-divider my-2">

                    <!-- Facebook 3-Action Buttons Row -->
                    <div class="d-flex align-items-center justify-content-between pt-1">
                        <div class="d-flex gap-2">
                            <label class="fb-composer-btn text-success" title="Add a photo or video">
                                <i class="bi bi-images fs-5"></i>
                                <span class="d-none d-sm-inline">Photo</span>
                                <input type="file" name="image" id="composerFileInput" class="d-none"
                                       accept="image/jpeg,image/png,image/gif,image/webp" data-preview="#postPreview">
                            </label>
                            <button type="button" class="fb-composer-btn text-warning" id="fbFeelingBtn" title="Feeling/activity">
                                <i class="bi bi-emoji-smile-fill fs-5"></i>
                                <span class="d-none d-sm-inline">Feeling</span>
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary fb-submit-btn px-4 rounded-pill fw-bold">
                            Post
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Newsfeed Timeline -->
        <?php if (!$posts): ?>
            <div class="card fb-card text-center text-muted py-5 px-3">
                <i class="bi bi-newspaper display-4" style="color: #1877f2;"></i>
                <p class="mt-2 mb-0">No posts in your feed yet. Be the first to share something!</p>
            </div>
        <?php endif; ?>

        <?php foreach ($posts as $post): ?>
            <?php require BASE_PATH . '/app/views/partials/post.php'; ?>
        <?php endforeach; ?>

        <?php require BASE_PATH . '/app/views/partials/pagination.php'; ?>
    </section>

    <!-- Right column: Discover / Suggested People (Balances the layout & centers feed) -->
    <aside class="col-xl-3 d-none d-xl-block">
        <div class="side-sticky">
            <?php if ($people): ?>
                <div class="card fb-card person-suggest-card p-3 mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="fw-bold small text-muted text-uppercase" style="letter-spacing: 0.5px;">Discover People</span>
                        <a href="<?= e(url('search/index', ['type' => 'users'])) ?>" class="small fw-semibold text-primary">See all</a>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach (array_slice($people, 0, 5) as $person): ?>
                            <a href="<?= e(url('profile/show', ['id' => $person['id']])) ?>" class="d-flex align-items-center gap-2 p-2 rounded-3 text-decoration-none text-reset person-suggest-item">
                                <img src="<?= e(avatar_url($person['profile_image'])) ?>" alt="" class="avatar avatar-sm flex-shrink-0">
                                <div class="min-w-0 flex-grow-1">
                                    <div class="fw-semibold text-truncate small"><?= e($person['full_name']) ?></div>
                                    <div class="text-muted small" style="font-size: 0.75rem;">@<?= e($person['username']) ?></div>
                                </div>
                                <span class="badge bg-primary-subtle text-primary rounded-pill small px-2 py-1" style="font-size: 0.72rem;">View</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="px-2 text-muted small" style="font-size: 0.75rem;">
                <p class="mb-1">&copy; <?= date('Y') ?> <?= e(config('app.name')) ?> &middot; Social Network</p>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= e(url('profile/show', ['id' => $me['id']])) ?>" class="text-muted">Privacy</a>
                    <span>&middot;</span>
                    <a href="<?= e(url('feed/index')) ?>" class="text-muted">Terms</a>
                    <span>&middot;</span>
                    <a href="<?= e(url('profile/edit')) ?>" class="text-muted">Settings</a>
                </div>
            </div>
        </div>
    </aside>

</div>
