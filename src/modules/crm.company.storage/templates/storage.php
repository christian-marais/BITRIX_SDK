<!-- file_upload.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Déposer un Fichier</title>
</head>
<body>
    <div class="container mt-5">
        <h2>Déposer un Fichier</h2>
        <form action="upload_controller.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="file" class="form-label">Choisissez un fichier</label>
                <input type="file" class="form-control" id="file" name="file" required>
            </div>
            <button type="submit" class="btn btn-primary">Déposer</button>
        </form>
    </div>
</body>
</html>