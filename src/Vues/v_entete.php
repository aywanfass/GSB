<?php
/**
 * Entête + Layout principal avec sidebar (Bootstrap 5.3)
 */
$uc = filter_input(INPUT_GET, 'uc', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Intranet du Laboratoire Galaxy-Swiss Bourdin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
          rel="stylesheet">
    <!-- Styles personnalisés -->
    <link href="./styles/style.css" rel="stylesheet">
</head>
<body class="flex-column">
<div class="app-wrapper">
    <?php if ($estConnecte) {
        include __DIR__ . '/partials/v_sidebar.php';
    } ?>
    <main class="app-main">
        <?php if (!$estConnecte) { ?>
            <div class="text-center mb-4">
                <img src="./images/logo.jpg"
                     class="img-fluid d-block mx-auto"
                     alt="Laboratoire Galaxy-Swiss Bourdin"
                     style="max-height:120px;">
            </div>
        <?php } else { ?>
            <!-- Bouton toggle (mobile) -->
            <button class="btn btn-outline-secondary sidebar-toggle-btn" id="sidebarToggle" type="button">
                <i class="bi bi-list"></i> Menu
            </button>
        <?php } ?>