<?php
require_once 'buttonsBar.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Erreur 404 - Webhook invalide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">Erreur !</h4>
            <p>Le webhook Bitrix n'est pas configuré ou est invalide. Veuillez configurer un webhook valide pour continuer.</p>
            <hr>
            <p class="mb-0">Utilisez le bouton "Sauvegarder le Webhook" dans la barre d'outils pour configurer votre webhook.</p>
        </div>
    </div>
</body>
</html>
