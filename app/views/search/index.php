<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-7">
        <h1 class="h4 mb-3"><i class="bi bi-search me-2"></i>Search</h1>

        <ul class="nav nav-pills mb-3">
            <li class="nav-item">
                <a class="nav-link <?= $type === 'users' ? 'active' : '' ?>"
                   href="<?= e(url('search/index', ['q' => $q, 'type' => 'users'])) ?>"><i class="bi bi-people me-1"></i>People</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $type === 'posts' ? 'active' : '' ?>"
                   href="<?= e(url('search/index', ['q' => $q, 'type' => 'posts'])) ?>"><i class="bi bi-chat-square-text me-1"></i>Posts</a>
            </li>
        </ul>

        <?php if ($q === ''): ?>
            <p class="text-muted">Type a name, username, or keyword in the search bar above.</p>

        <?php elseif (!$results): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-emoji-frown display-4"></i>
                <p class="mt-2">No <?= $type === 'users' ? 'people' : 'posts' ?> found for &ldquo;<?= e($q) ?>&rdquo;.</p>
            </div>

        <?php elseif ($type === 'users'): ?>
            <p class="text-muted small"><?= count($results) ?> result(s) for &ldquo;<?= e($q) ?>&rdquo;</p>
            <div class="list-group shadow-sm">
                <?php foreach ($results as $u): ?>
                    <a href="<?= e(url('profile/show', ['id' => $u['id']])) ?>"
                       class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                        <img src="<?= e(avatar_url($u['profile_image'])) ?>" alt="" class="avatar">
                        <div>
                            <div class="fw-semibold"><?= e($u['full_name']) ?></div>
                            <div class="text-muted small">@<?= e($u['username']) ?></div>
                            <?php if ($u['bio']): ?>
                                <div class="small text-truncate"><?= e(mb_strimwidth($u['bio'], 0, 90, '...')) ?></div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <p class="text-muted small"><?= count($results) ?> result(s) for &ldquo;<?= e($q) ?>&rdquo;</p>
            <?php foreach ($results as $post): ?>
                <?php require BASE_PATH . '/app/views/partials/post.php'; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
