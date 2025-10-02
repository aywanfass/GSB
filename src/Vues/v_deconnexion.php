<?php
/**
 * Vue Déconnexion (Bootstrap 5.3)
 */
?>
<div class="alert alert-info" role="alert">
    <p class="mb-0">Vous avez bien été déconnecté !
        <a href="index.php" class="alert-link">Cliquez ici</a>
        pour revenir à la page de connexion.</p>
</div>
<?php
header("Refresh: 3;URL=index.php");