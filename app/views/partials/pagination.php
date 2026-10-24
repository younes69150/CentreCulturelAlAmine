<?php if ($pager['pages'] > 1):
    $q = $_GET; ?>
    <nav class="pagination" aria-label="Pagination">
        <?php for ($p = 1; $p <= $pager['pages']; $p++): $q['page'] = $p; ?>
            <a href="?<?= e(http_build_query($q)) ?>"<?= $p === $pager['page'] ? ' aria-current="page"' : '' ?>><?= $p ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>
