<?php
$banners_result = mysqli_query($connection, "SELECT banners.*, vehicles.id AS linked_vehicle_id
    FROM banners
    LEFT JOIN vehicles ON vehicles.id = banners.vehicle_id
    WHERE banners.is_active = 1
    ORDER BY banners.sort_order ASC, banners.created_at DESC");
$banners = mysqli_fetch_all($banners_result, MYSQLI_ASSOC);

if (!empty($banners)) { ?>
    <section class="banner-carousel" id="banner-carousel">
        <div class="banner-carousel-viewport">
            <div class="banner-track" id="banner-track">
                <?php foreach ($banners as $banner) { ?>
                    <a class="banner-slide"
                       href="<?= !empty($banner['linked_vehicle_id']) ? '../vehicles/show.php?vehicle_id=' . (int) $banner['linked_vehicle_id'] : '../vehicles/browse.php' ?>">
                        <img src="../<?= e($banner['image_path']) ?>" alt="<?= e($banner['title']) ?>">
                        <?php if (!empty($banner['title'])) { ?>
                            <span class="banner-caption"><?= e($banner['title']) ?></span>
                        <?php } ?>
                    </a>
                <?php } ?>
            </div>

            <?php if (count($banners) > 1) { ?>
                <button type="button" class="banner-arrow banner-prev" id="banner-prev" aria-label="Previous banner">&#8249;</button>
                <button type="button" class="banner-arrow banner-next" id="banner-next" aria-label="Next banner">&#8250;</button>
            <?php } ?>
        </div>

        <?php if (count($banners) > 1) { ?>
            <div class="banner-dots" id="banner-dots">
                <?php foreach ($banners as $index => $banner) { ?>
                    <button type="button" class="banner-dot <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>" aria-label="Go to banner <?= $index + 1 ?>"></button>
                <?php } ?>
            </div>
        <?php } ?>
    </section>

    <?php if (count($banners) > 1) { ?>
        <script src="../js/banner-carousel.js"></script>
    <?php } ?>
<?php } ?>