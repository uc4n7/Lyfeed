<?php
/** Expects: $page, $pages */
if (($pages ?? 1) > 1):
    $start = max(1, $page - 2);
    $end   = min($pages, $page + 2);
?>
<nav aria-label="Feed pages" class="mt-4">
    <ul class="pagination justify-content-center flex-wrap">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e(url('feed/index', ['page' => $page - 1])) ?>">Previous</a>
        </li>
        <?php for ($i = $start; $i <= $end; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= e(url('feed/index', ['page' => $i])) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e(url('feed/index', ['page' => $page + 1])) ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
