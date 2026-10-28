<?php
/**
 * Minimalist single post card.
 * Expects: $post (array), $comments (array grouped by post_id)
 */
$postComments = $comments[$post['id']] ?? [];
$commentCount = count($postComments);
$isMine       = (int)$post['user_id'] === current_user_id();
$hasImage     = !empty($post['image']);
$bigText      = !$hasImage && mb_strlen($post['content']) <= 110 && strpos($post['content'], "\n") === false;
$likeCount    = (int)$post['like_count'];
?>
<article class="card fb-card fb-post scroll-offset mb-3" id="post-<?= (int)$post['id'] ?>">

    <!-- Facebook Post Header -->
    <header class="fb-post-header">
        <a href="<?= e(url('profile/show', ['id' => $post['user_id']])) ?>" class="avatar-link">
            <img src="<?= e(avatar_url($post['profile_image'])) ?>" alt="" class="avatar avatar-sm">
        </a>
        <div class="flex-grow-1 min-w-0 ms-2">
            <a class="fb-post-author text-truncate d-block" href="<?= e(url('profile/show', ['id' => $post['user_id']])) ?>"><?= e($post['full_name']) ?></a>
            <div class="fb-post-meta">
                <time class="js-time-ago" datetime="<?= date('c', strtotime($post['created_at'])) ?>" title="<?= date('M j, Y g:i A', strtotime($post['created_at'])) ?>"><?= e(time_ago($post['created_at'])) ?></time>
                <span class="fb-meta-dot">&middot;</span>
                <i class="bi bi-globe-americas fb-globe-icon" title="Public"></i>
            </div>
        </div>

        <?php if ($isMine): ?>
            <div class="dropdown">
                <button class="fb-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Post options">
                    <i class="bi bi-three-dots"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item" href="<?= e(url('post/edit', ['id' => $post['id']])) ?>">
                        <i class="bi bi-pencil me-2"></i>Edit post</a></li>
                    <li>
                        <form action="<?= e(url('post/delete')) ?>" method="post" data-confirm="Delete this post and all its comments?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete post</button>
                        </form>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </header>

    <!-- Post Text Content -->
    <div class="fb-post-text <?= $bigText ? 'big' : '' ?>"><?= nl2br(e($post['content'])) ?></div>

    <!-- Post Media / Image -->
    <?php if ($hasImage): ?>
        <div class="fb-post-media" data-like-target>
            <img src="<?= e(upload_url($post['image'])) ?>" alt="Photo shared by <?= e($post['full_name']) ?>" loading="lazy" class="post-img-zoom" style="cursor: zoom-in;"
                 data-author-name="<?= e($post['full_name']) ?>"
                 data-author-img="<?= e(avatar_url($post['profile_image'])) ?>"
                 data-post-text="<?= e(mb_strimwidth($post['content'], 0, 150, '...')) ?>">
            <span class="heart-burst" aria-hidden="true"><i class="bi bi-heart-fill"></i></span>
        </div>
    <?php endif; ?>

    <!-- Facebook Reactions & Comments Stats Bar -->
    <div class="fb-post-stats">
        <div class="fb-stats-left">
            <span class="like-count"><?= $likeCount ?></span> <span class="like-word ms-1"><?= $likeCount === 1 ? 'like' : 'likes' ?></span>
        </div>
        <div class="fb-stats-right">
            <span class="comment-count"><?= $commentCount ?> <?= $commentCount === 1 ? 'comment' : 'comments' ?></span>
        </div>
    </div>

    <hr class="fb-divider my-0">

    <!-- Facebook 3-Action Buttons Row (Like, Comment, Share) -->
    <div class="fb-actions-row">
        <form action="<?= e(url('like/toggle')) ?>" method="post" class="like-form fb-action-form">
            <?= csrf_field() ?>
            <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
            <button type="submit" class="fb-action-btn like-btn <?= $post['liked_by_me'] ? 'liked' : '' ?>"
                    aria-pressed="<?= $post['liked_by_me'] ? 'true' : 'false' ?>">
                <i class="bi <?= $post['liked_by_me'] ? 'bi-heart-fill' : 'bi-heart' ?> me-1 fs-5"></i>
                <span>Like</span>
            </button>
        </form>

        <button class="fb-action-btn comment-toggle-btn" type="button" data-bs-toggle="collapse"
                data-bs-target="#comments-<?= (int)$post['id'] ?>" aria-label="Comments"
                aria-expanded="false">
            <i class="bi bi-chat-square me-1 fs-5"></i>
            <span>Comment</span>
        </button>

        <button class="fb-action-btn share-post-btn" type="button"
                data-post-url="<?= e(url('feed/index')) ?>#post-<?= (int)$post['id'] ?>" title="Share post">
            <i class="bi bi-share me-1 fs-5"></i>
            <span>Share</span>
        </button>
    </div>

    <!-- Facebook Comments Drawer -->
    <div class="collapse" id="comments-<?= (int)$post['id'] ?>">
        <div class="fb-comments-section">
            <hr class="fb-divider my-0 mb-3">
            <?php if (!empty($postComments)): ?>
                <div class="fb-comments-list">
                    <?php foreach ($postComments as $c): ?>
                        <?php require BASE_PATH . '/app/views/partials/comment.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Facebook Comment Composer Form -->
            <form action="<?= e(url('comment/store')) ?>" method="post" class="fb-comment-form needs-validation" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                <img src="<?= e(avatar_url($me['profile_image'] ?? null)) ?>" alt="" class="avatar avatar-xs flex-shrink-0">
                <div class="fb-comment-input-box flex-grow-1">
                    <input type="text" name="content" placeholder="Write a comment..." maxlength="1000" required aria-label="Write a comment" autocomplete="off">
                    <button type="submit" aria-label="Send comment" title="Send comment"><i class="bi bi-send-fill"></i></button>
                </div>
            </form>
        </div>
    </div>
</article>
