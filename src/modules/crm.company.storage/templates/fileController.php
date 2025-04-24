<?php
// upload_controller.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $uploads_dir = 'uploads/';
        $tmp_name = $_FILES['file']['tmp_name'];
        $name = basename($_FILES['file']['name']);
        move_uploaded_file($tmp_name, "$uploads_dir/$name");
        echo "Fichier téléchargé avec succès : $name";
    } else {
        echo "Erreur lors du téléchargement du fichier.";
    }
} else {
    echo "Méthode de requête non valide.";
}
?>