</main>

<?php if (!empty($navUser)): ?>
    <?php
        $route     = $_GET['route'] ?? 'feed/index';
        $onHome    = str_starts_with($route, 'feed');
        $onSearch  = str_starts_with($route, 'search');
        $onProfile = str_starts_with($route, 'profile')
                     && (!isset($_GET['id']) || (int)$_GET['id'] === (int)$navUser['id']);
    ?>
    <!-- Phone tab bar -->
    <nav class="bottom-nav d-md-none" aria-label="Quick navigation">
        <a href="<?= e(url('feed/index')) ?>" class="<?= $onHome ? 'active' : '' ?>" aria-label="Home">
            <i class="bi <?= $onHome ? 'bi-house-heart-fill' : 'bi-house-heart' ?>"></i>
        </a>
        <a href="<?= e(url('search/index')) ?>" class="<?= $onSearch ? 'active' : '' ?>" aria-label="Search">
            <i class="bi bi-search"></i>
        </a>
        <a href="<?= e(url('feed/index')) ?>#composer" class="create-btn" aria-label="Create a post">
            <i class="bi bi-plus-lg"></i>
        </a>
        <a href="<?= e(url('profile/show', ['id' => $navUser['id']])) ?>" class="<?= $onProfile ? 'active' : '' ?>" aria-label="My profile">
            <i class="bi <?= $onProfile ? 'bi-person-fill' : 'bi-person' ?>"></i>
        </a>
    </nav>
<?php else: ?>
    <footer class="text-center text-muted small py-4">
        &copy; <?= date('Y') ?> <?= e(config('app.name')) ?> &middot; LyfeFeed Group 5 Web Development
    </footer>
<?php endif; ?>

<!-- Facebook Image Theater Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen m-0">
        <div class="modal-content border-0 rounded-0" style="background: #000;">
            <div class="modal-body p-0 d-flex flex-column flex-lg-row">
                <!-- Close Button for Mobile -->
                <button type="button" class="btn-close btn-close-white position-absolute top-0 start-0 m-3 d-lg-none" data-bs-dismiss="modal" aria-label="Close" style="z-index: 1055;"></button>
                
                <!-- Left: Image Viewer (Black) -->
                <div class="flex-grow-1 d-flex align-items-center justify-content-center position-relative" style="background: #000; min-height: 50vh;">
                    <!-- Close Button for Desktop -->
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 start-0 m-4 d-none d-lg-block" data-bs-dismiss="modal" aria-label="Close" style="z-index: 1055; width: 32px; height: 32px; background-color: rgba(255,255,255,0.15); border-radius: 50%; padding: 12px;"></button>
                    
                    <img id="modalImage" src="" alt="Full screen image" class="img-fluid" style="max-height: 100vh; max-width: 100vw; object-fit: contain;">
                </div>
                
                <!-- Right: Post Details (Sidebar) -->
                <div class="modal-sidebar-panel d-none d-lg-block border-start" style="width: 100%; max-width: 420px; height: 100vh; overflow-y: auto;" id="modalSidebar">
                    <!-- The cloned post will be injected here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- App GUI Alert Modal -->
<div class="modal fade" id="appAlertModal" tabindex="-1" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content rounded-4 border-0 shadow-lg text-center p-4">
            <div class="modal-body p-0">
                <div class="alert-modal-icon-wrap mx-auto mb-3 warning" id="appAlertIconWrap">
                    <i class="bi bi-exclamation-triangle-fill fs-2" id="appAlertIcon"></i>
                </div>
                <h2 class="h5 fw-bold mb-2" id="appAlertTitle">Notice</h2>
                <p class="text-muted small mb-4" id="appAlertMessage">Message text here.</p>
                <button type="button" class="btn btn-primary w-100 py-2 rounded-pill fw-semibold" data-bs-dismiss="modal" id="appAlertOkBtn">
                    Got it
                </button>
            </div>
        </div>
    </div>
</div>

<!-- App GUI Confirmation Modal -->
<div class="modal fade" id="appConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content rounded-4 border-0 shadow-lg text-center p-4">
            <div class="modal-body p-0">
                <div class="alert-modal-icon-wrap mx-auto mb-3 danger">
                    <i class="bi bi-trash3-fill fs-2"></i>
                </div>
                <h2 class="h5 fw-bold mb-2" id="appConfirmTitle">Are you sure?</h2>
                <p class="text-muted small mb-4" id="appConfirmMessage">This action cannot be undone.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary w-50 py-2 rounded-pill fw-semibold" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger w-50 py-2 rounded-pill fw-semibold" id="appConfirmProceedBtn">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/app.js')) ?>?v=<?= filemtime(BASE_PATH . '/public/assets/js/app.js') ?>"></script>
</body>
</html>
