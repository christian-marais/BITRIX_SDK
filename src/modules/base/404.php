<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Page Non Trouvée - 404' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f5f5;
        }
        .error-container {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }
        .error-code {
            font-size: 72px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        .error-message {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div id="main-content" class="error-container">
        <div class="error-code"><?= $codeError ?? '404' ?></div>
        <h1 class="error-message"><?= $messageError ?? 'Page Non Trouvée' ?></h1>
        <p><?= $debugMessage ?? 'Désolé, la page que vous recherchez n\'existe pas ou a été déplacée.' ?></p>
        <a href="<?= $redirection ?? $_SERVER['PHP_SELF']; ?>" class="btn btn-primary"><?= $buttonValue ?? 'Retour à l\'accueil' ?></a>
    </div>
</body>
</html>
