<?php if (isset($message) && $message) { ?>
<div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
<?php } ?>
<?php if (isset($messages) && is_array($messages)) { ?>
    <?php foreach ($messages as $m) { ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($m); ?></div>
    <?php } ?>
<?php } ?>
