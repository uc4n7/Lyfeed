<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3"><i class="bi bi-pencil-square me-2"></i>Edit post</h1>

                <form action="<?= e(url('post/update')) ?>" method="post" enctype="multipart/form-data"
                      class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">

                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea id="content" name="content" class="form-control" rows="5" maxlength="2000"
                                  required><?= e($post['content']) ?></textarea>
                        <div class="invalid-feedback">Post content is required.</div>
                    </div>

                    <?php if ($post['image']): ?>
                        <div class="mb-3">
                            <p class="form-label mb-1">Current image</p>
                            <img src="<?= e(upload_url($post['image'])) ?>" alt="Current post image" class="img-fluid rounded-4 mb-2" style="max-height: 320px;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                                <label class="form-check-label" for="remove_image">Remove this image</label>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="image" class="form-label"><?= $post['image'] ? 'Replace image' : 'Add image' ?>
                            <span class="text-muted">(optional)</span></label>
                        <input type="file" class="form-control" id="image" name="image"
                               accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                        <a href="<?= e(url('feed/index')) ?>#post-<?= (int)$post['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
