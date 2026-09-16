<?php if (!empty($_SESSION['success'])) { ?>
    <div class="toast toast-success" role="status" aria-live="polite">
        <span class="toast-icon" aria-hidden="true">&#10003;</span>
        <span class="toast-message"><?= e($_SESSION['success']) ?></span>
        <button type="button" class="toast-close" aria-label="Dismiss notification">&times;</button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php } ?>
<script src="../js/toast.js"></script>
