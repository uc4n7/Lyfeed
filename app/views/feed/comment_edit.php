<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3"><i class="bi bi-chat-left-text me-2"></i>Edit comment</h1>

                <form action="<?= e(url('comment/update')) ?>" method="post" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$comment['id'] ?>">

                    <div class="mb-3">
                        <label for="content" class="form-label">Comment</label>
                        <textarea id="content" name="content" class="form-control" rows="3" maxlength="1000"
                            required><?= e($comment['content']) ?></textarea>
                        <div class="invalid-feedback">Comment cannot be empty.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                        <a href="<?= e(url('feed/index')) ?>#post-<?= (int)$comment['post_id'] ?>"
                            class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>