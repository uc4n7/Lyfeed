<?php
// One comment item with support for nested replies.
$commentMine = (int)$c['user_id'] === current_user_id();
$currentAvatar = $me['profile_image'] ?? ($navUser['profile_image'] ?? null);
?>
<div class="fb-comment-item d-flex gap-2 mb-2" id="comment-<?= (int)$c['id'] ?>">
    <a href="<?= e(url('profile/show', ['id' => $c['user_id']])) ?>" class="flex-shrink-0">
        <img src="<?= e(avatar_url($c['profile_image'])) ?>" alt="" class="avatar avatar-xs">
    </a>
    <div class="min-w-0 flex-grow-1 fb-comment-content-wrap">
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
        <div class="fb-comment-meta ps-2 pt-1 d-flex align-items-center gap-2">
            <time class="js-time-ago" datetime="<?= date('c', strtotime($c['created_at'])) ?>" title="<?= date('M j, Y g:i A', strtotime($c['created_at'])) ?>"><?= e(time_ago($c['created_at'])) ?></time>
            <span class="text-muted">&middot;</span>
            <button type="button" class="btn btn-link btn-sm p-0 text-muted fw-semibold text-decoration-none reply-toggle-btn"
                    data-bs-toggle="collapse" data-bs-target="#reply-box-<?= (int)$c['id'] ?>"
                    aria-expanded="false" aria-controls="reply-box-<?= (int)$c['id'] ?>"
                    style="font-size: 0.78rem;">
                <i class="bi bi-reply-fill me-1"></i>Reply
            </button>
        </div>

        <!-- Inline Reply Box -->
        <div class="collapse mt-2" id="reply-box-<?= (int)$c['id'] ?>">
            <form action="<?= e(url('comment/store')) ?>" method="post" class="fb-comment-form d-flex gap-2 align-items-center">
                <?= csrf_field() ?>
                <input type="hidden" name="post_id" value="<?= (int)$c['post_id'] ?>">
                <input type="hidden" name="parent_id" value="<?= (int)$c['id'] ?>">
                <img src="<?= e(avatar_url($currentAvatar)) ?>" alt="" class="avatar avatar-xs flex-shrink-0" style="width: 26px; height: 26px;">
                <div class="fb-comment-input-box flex-grow-1" style="font-size: 0.85rem;">
                    <input type="text" name="content" placeholder="Reply to <?= e($c['full_name']) ?>..." maxlength="1000" required aria-label="Reply to comment" autocomplete="off">
                    <button type="submit" aria-label="Send reply" title="Send reply"><i class="bi bi-send-fill"></i></button>
                </div>
            </form>
        </div>

        <!-- Nested Replies List -->
        <div class="fb-replies-list ms-3 ps-2 border-start mt-2 <?= empty($c['replies']) ? 'd-none' : '' ?>">
            <?php if (!empty($c['replies'])): ?>
                <?php foreach ($c['replies'] as $reply): ?>
                    <?php $replyMine = (int)$reply['user_id'] === current_user_id(); ?>
                    <div class="fb-comment-item fb-reply-item d-flex gap-2 mb-2" id="comment-<?= (int)$reply['id'] ?>">
                        <a href="<?= e(url('profile/show', ['id' => $reply['user_id']])) ?>" class="flex-shrink-0">
                            <img src="<?= e(avatar_url($reply['profile_image'])) ?>" alt="" class="avatar avatar-xs" style="width: 26px; height: 26px;">
                        </a>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fb-comment-bubble py-1 px-2" style="font-size: 0.85rem;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <a class="fb-comment-author" href="<?= e(url('profile/show', ['id' => $reply['user_id']])) ?>"><?= e($reply['full_name']) ?></a>
                                        <?php if (!empty($reply['parent_username'])): ?>
                                            <span class="badge bg-primary-subtle text-primary ms-1" style="font-size: 0.7rem; font-weight: 500;">@<?= e($reply['parent_username']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($replyMine): ?>
                                        <div class="fb-comment-tools ms-2">
                                            <a class="fb-comment-tool-btn" title="Edit reply" href="<?= e(url('comment/edit', ['id' => $reply['id']])) ?>"><i class="bi bi-pencil"></i></a>
                                            <form action="<?= e(url('comment/delete')) ?>" method="post" data-confirm="Delete this reply?" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= (int)$reply['id'] ?>">
                                                <button type="submit" class="fb-comment-tool-btn text-danger" title="Delete reply"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="fb-comment-body"><?= nl2br(e($reply['content'])) ?></div>
                            </div>
                            <div class="fb-comment-meta ps-2 pt-1 d-flex align-items-center gap-2">
                                <time class="js-time-ago" datetime="<?= date('c', strtotime($reply['created_at'])) ?>" title="<?= date('M j, Y g:i A', strtotime($reply['created_at'])) ?>"><?= e(time_ago($reply['created_at'])) ?></time>
                                <span class="text-muted">&middot;</span>
                                <button type="button" class="btn btn-link btn-sm p-0 text-muted fw-semibold text-decoration-none reply-toggle-btn"
                                        data-bs-toggle="collapse" data-bs-target="#reply-box-<?= (int)$c['id'] ?>"
                                        onclick="var inp=document.querySelector('#reply-box-<?= (int)$c['id'] ?> input[name=\'content\']'); if(inp){ inp.value='@<?= e($reply['username']) ?> '; inp.focus(); }"
                                        style="font-size: 0.75rem;">
                                    <i class="bi bi-reply-fill me-1"></i>Reply
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
