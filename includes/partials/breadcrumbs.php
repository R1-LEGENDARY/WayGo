<?php
// Expects $breadcrumbs to already be set as an array of:
// array('label' => 'Browse', 'url' => '../vehicles/browse.php')
// The last item should have 'url' => null (current page, not a link).
if (!empty($breadcrumbs)) { ?>
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <ol>
            <?php foreach ($breadcrumbs as $index => $crumb) { ?>
                <li>
                    <?php if (!empty($crumb['url'])) { ?>
                        <a href="<?= e($crumb['url']) ?>"><?= e($crumb['label']) ?></a>
                    <?php } else { ?>
                        <span aria-current="page"><?= e($crumb['label']) ?></span>
                    <?php } ?>
                </li>
                <?php if ($index < count($breadcrumbs) - 1) { ?>
                    <li class="breadcrumb-separator">&raquo;</li>
                <?php } ?>
            <?php } ?>
        </ol>
    </nav>
<?php } ?>