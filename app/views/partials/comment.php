<?php
/** Single comment row. Expects: $c (comment array) */
$commentMine = (int)$c['user_id'] === current_user_id();
?>
<div class="fb-comment-item d-flex gap-2 mb-2">
    <a href="<?= e(url('profile/show', ['id' => $c['user_id']])) ?>" class="flex-shrink-0">
        <img src="<?= e(avatar_url($c['profile_image'])) ?>" alt="" class="avatar avatar-xs">
    </a>
    <div class="min-w-0 flex-grow-1">
        <div class="fb-comment-bubble">
            <div class="d-flex align-items-center justify-content-between">
                <a class="fb-comment-author" href="<?= e(url('profile/show', ['id' => $c['user_id']])) ?>"><?= e($c['full_name']) ?></a>
                <?php if ($commentMine): ?>
                    <div class="fb-comment-tools ms-2">
                        <a class="fb-comment-tool-btn" title="Edit comment" href="<?= e(url('comment/edit', ['id' => $c['id']])) ?>"><i class="bi bi-pencil"></i></a>
                        <form action="<?= e(url('comment/delete')) ?>" method="post" data-confirm="Delete this comment?" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                            <button type="submit" class="fb-comment-tool-btn text-danger" title="Delete comment"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            <div class="fb-comment-body"><?= nl2br(e($c['content'])) ?></div>
        </div>
        <div class="fb-comment-meta ps-2 pt-1">
            <time class="js-time-ago" datetime="<?= date('c', strtotime($c['created_at'])) ?>" title="<?= date('M j, Y g:i A', strtotime($c['created_at'])) ?>"><?= e(time_ago($c['created_at'])) ?></time>
        </div>
    </div>
</div>
