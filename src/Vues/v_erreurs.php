<?php
/**
 * Vue Erreurs (Bootstrap 5.3)
 */
?>
<div class="alert alert-danger" role="alert">
    <?php foreach ($_REQUEST['erreurs'] as $erreur) {
        echo '<p class="mb-0">' . htmlspecialchars($erreur) . '</p>';
    } ?>
</div>