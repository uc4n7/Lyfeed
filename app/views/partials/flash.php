<?php
$flashes = get_flash();
if (!empty($flashes)):
?>
<div class="flash-container" aria-live="polite">
    <?php foreach ($flashes as $f): ?>
        <?php
            $type = $f['type'];
            $icon = match($type) {
                'success' => 'bi-check-circle-fill',
                'danger', 'error' => 'bi-exclamation-triangle-fill',
                'warning' => 'bi-exclamation-circle-fill',
                default => 'bi-info-circle-fill',
            };
        ?>
        <div class="flash-toast flash-toast-<?= e($type) ?>" role="alert" data-auto-dismiss="3500">
            <div class="flash-toast-content">
                <i class="bi <?= e($icon) ?> flash-toast-icon"></i>
                <div class="flash-toast-message"><?= e($f['message']) ?></div>
                <button type="button" class="flash-toast-close" aria-label="Close" title="Dismiss">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="flash-toast-progress">
                <div class="flash-toast-progress-bar"></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
