<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3"><i class="bi bi-person-gear me-2"></i>Edit profile</h1>

                <form action="<?= e(url('profile/update')) ?>" method="post" enctype="multipart/form-data"
                      class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <div class="text-center mb-3">
                        <img id="avatarPreview" src="<?= e(avatar_url($profile['profile_image'])) ?>"
                             alt="Current profile photo" class="avatar avatar-xl">
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block fw-semibold">Banner background (Cover Photo)</label>
                        
                        <!-- Drag-to-reposition Preview Container -->
                        <div class="cover-reposition-box position-relative mb-2 rounded-3 overflow-hidden shadow-sm border"
                             id="coverRepositionContainer"
                             style="height: 180px; background: var(--grad); cursor: <?= !empty($profile['cover_image']) ? 'grab' : 'default' ?>;">
                            
                            <?php $currentPos = (int)($profile['cover_position'] ?? 50); ?>
                            <?php if (!empty($profile['cover_image'])): ?>
                                <img id="coverPreview" src="<?= e(upload_url($profile['cover_image'])) ?>"
                                     alt="Banner background" class="w-100 h-100 object-fit-cover user-select-none"
                                     style="object-position: center <?= $currentPos ?>%; pointer-events: none;">
                                <div class="position-absolute top-0 start-50 translate-middle-x mt-2 badge bg-dark bg-opacity-75 text-white px-3 py-2 rounded-pill shadow-sm" id="dragBadge" style="pointer-events: none; z-index: 2;">
                                    <i class="bi bi-arrows-move me-1"></i> Drag banner to reposition
                                </div>
                            <?php else: ?>
                                <img id="coverPreview" src="" alt="Banner background" class="w-100 h-100 object-fit-cover user-select-none d-none"
                                     style="object-position: center 50%; pointer-events: none;">
                                <div id="noCoverPlaceholder" class="w-100 h-100 d-flex align-items-center justify-content-center text-white-50">
                                    <span class="small fw-semibold"><i class="bi bi-image me-1"></i>No banner background</span>
                                </div>
                                <div class="position-absolute top-0 start-50 translate-middle-x mt-2 badge bg-dark bg-opacity-75 text-white px-3 py-2 rounded-pill shadow-sm d-none" id="dragBadge" style="pointer-events: none; z-index: 2;">
                                    <i class="bi bi-arrows-move me-1"></i> Drag banner to reposition
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Slider control for fine-tuning -->
                        <div class="card p-2 mb-2 bg-light border-0 <?= empty($profile['cover_image']) ? 'd-none' : '' ?>" id="coverSliderWrap">
                            <div class="d-flex align-items-center justify-content-between text-muted small mb-1">
                                <span><i class="bi bi-arrow-up me-1"></i>Top section</span>
                                <span class="fw-semibold text-primary" id="coverPosLabel"><?= $currentPos ?>%</span>
                                <span>Bottom section<i class="bi bi-arrow-down ms-1"></i></span>
                            </div>
                            <input type="range" class="form-range" id="coverPositionSlider" min="0" max="100" value="<?= $currentPos ?>">
                        </div>

                        <!-- Hidden input to submit the chosen position percentage -->
                        <input type="hidden" name="cover_position" id="cover_position" value="<?= $currentPos ?>">

                        <input type="file" class="form-control" id="cover_image" name="cover_image"
                               accept="image/jpeg,image/png,image/gif,image/webp">
                        
                        <?php if (!empty($profile['cover_image'])): ?>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="remove_cover_image" value="1" id="remove_cover_image">
                                <label class="form-check-label small" for="remove_cover_image">Remove banner background</label>
                            </div>
                        <?php endif; ?>
                        <div class="form-text">Choose a banner image and drag or slide to pick which section is shown.</div>
                    </div>

                    <div class="mb-3">
                        <label for="profile_image" class="form-label fw-semibold">Profile photo</label>
                        <input type="file" class="form-control" id="profile_image" name="profile_image"
                               accept="image/jpeg,image/png,image/gif,image/webp" data-preview="#avatarPreview">
                        <div class="form-text">JPG, PNG, GIF or WEBP, up to 2 MB.</div>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" value="<?= e($profile['username']) ?>" disabled autocomplete="off">
                        <div class="form-text">Usernames cannot be changed.</div>
                    </div>

                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required maxlength="100"
                               value="<?= e($profile['full_name']) ?>" autocomplete="off">
                        <div class="invalid-feedback">Please enter your full name.</div>
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="4" maxlength="500" autocomplete="off"><?= e($profile['bio']) ?></textarea>
                        <div class="form-text">Up to 500 characters.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                        <a href="<?= e(url('profile/show', ['id' => $profile['id']])) ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
