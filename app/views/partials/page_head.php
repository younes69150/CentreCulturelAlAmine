<header class="page-head">
    <div class="container page-head-inner">
        <div>
            <?php if (!empty($eyebrow)): ?><p class="eyebrow"><?= e($eyebrow) ?></p><?php endif; ?>
            <h1><?= e($heading) ?></h1>
            <?php if (!empty($sub)): ?><p class="page-sub"><?= e($sub) ?></p><?php endif; ?>
        </div>
        <?php if (!empty($action)): ?><a class="btn btn-gold" href="<?= url($action[0]) ?>"><?= e($action[1]) ?></a><?php endif; ?>
    </div>
</header>
