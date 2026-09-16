<p><?= $pagination['total'] ?> vehicle<?= $pagination['total'] === 1 ? '' : 's' ?> found.</p>

<?php if (empty($vehicles)) { ?>
    <p>No vehicles match those filters.</p>
<?php } else { ?>
    <table border="1">
        <thead>
            <tr>
                <th>Image</th>
                <th>Make</th>
                <th>Model</th>
                <th>Category</th>
                <th>Transmission</th>
                <th>Daily Rate</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vehicles as $vehicle) { ?>
                <tr>
                    <td>
                        <?php if (!empty($vehicle['image_path'])) { ?>
                            <img src="../<?= e($vehicle['image_path']) ?>" alt="Photo of <?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?>" width="80">
                        <?php } else { ?>
                            No image
                        <?php } ?>
                    </td>
                    <td><?= e($vehicle['make']) ?></td>
                    <td><?= e($vehicle['model']) ?></td>
                    <td><?= e($vehicle['category']) ?></td>
                    <td><?= e($vehicle['transmission']) ?></td>
                    <td>&#8369;<?= number_format($vehicle['daily_rate'], 2) ?></td>
                    <td>
                        <?php if (isset($start_date, $end_date)) { ?>
                            <a href="../booking/create.php?vehicle_id=<?= (int) $vehicle['id'] ?>&start_date=<?= e($start_date) ?>&end_date=<?= e($end_date) ?>">Book</a> |
                        <?php } else { ?>
                            <a href="../availability/index.php?category=<?= e($filters['category']) ?>&transmission=<?= e($filters['transmission']) ?>&min_price=<?= e($filters['min_price']) ?>&max_price=<?= e($filters['max_price']) ?>">Check Dates</a> |
                        <?php } ?>
                        <a href="../booking/calendar.php?vehicle_id=<?= (int) $vehicle['id'] ?>">Calendar</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php if ($pagination['total_pages'] > 1) { ?>
        <p>
            <?php if ($pagination['page'] > 1) { ?>
                <a href="?<?= $query_string ?>&page=<?= $pagination['page'] - 1 ?>">&laquo; Previous</a>
            <?php } ?>
            Page <?= $pagination['page'] ?> of <?= $pagination['total_pages'] ?>
            <?php if ($pagination['page'] < $pagination['total_pages']) { ?>
                <a href="?<?= $query_string ?>&page=<?= $pagination['page'] + 1 ?>">Next &raquo;</a>
            <?php } ?>
        </p>
    <?php } ?>
<?php } ?>