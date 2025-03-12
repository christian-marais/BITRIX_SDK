<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <title>Rechercher une entreprise</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .card {
            max-width: 400px;
            margin: auto;
            background-image: url("https://images.pexels.com/photos/1166644/pexels-photo-1166644.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1");
            background-size: cover;
            background-position: center;
            box-shadow: 21px 24px 27px #bdbdbd !important;
            border:1px gray dashed !important;
        }
        .card-body {
            height: 350px; /* Fixed height */
        }
        .card-content {
            height:65%; /* Fixed height */
            overflow-y:auto;
            border-radius: 5px;
            scrollbar-width: none; /* Hide scrollbar for WebKit browsers */
        }
        .card-title {
            font-size: 1.25rem; /* Reduced font size */
        }
        .card-text {
            font-size: 0.875rem; /* Reduced font size */
            
        }
    </style>
</head>
<body class="mb-3"style="display:flex; flex-direction:column; justify-content:center; align-items:center; height:90%; ">
    <h2 class="text-center" style="margin-top: 2rem;">Annuaire d'Enterprises</h2>
    <div class="row w-25">
        <div class="input-group mb-3 w-100 py-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Rechercher par mot-clé, siret, siren, nom ..." aria-label="Recherche" aria-describedby="button-addon2">
            <button class="btn btn-primary" type="button" id="button-addon2">Soumettre</button>
        </div>
    </div>
    <div id="results" class="row w-75 align-items-baseline"></div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('searchInput').addEventListener('input', function() {
            const keyword = this.value;
            if (keyword.length > 2) { // Minimum length for search
                console.log(`https://recherche-entreprises.api.gouv.fr/search?q=${keyword}&page=1&per_page=5`)
                fetch(`https://recherche-entreprises.api.gouv.fr/search?q=${keyword}&page=1&per_page=5`)
                .then(
                        response =>{
                            return response.json();
                        })
                .then(data => {
                    const resultsDiv = document.getElementById('results');
                    resultsDiv.innerHTML = '';
                    if (data.results.length > 0) {
                        data.results.forEach(company => {
                            let card = `<div class='col-md-3'>
                                <div class='card shadow-sm border-light rounded-3 mb-4'>
                                    <div class='card-body'>
                                        <h5 class='card-title fw-bold text-center'>${company.nom_complet}</h5>
                                        <p class='card-text'><span class='fw-bold badge text-bg-secondary'>SIREN: </span> <span class='text-muted'>${company.siren}</span></p>
                                        <p class='card-text'><span class='fw-bold badge text-bg-secondary'>NAF: </span> <span class='text-muted'>${company.siege.activite_principale}</span></p>
                                        <div class='card-content'>
                                            <div class='list-group'>
                                                ${company.matching_etablissements.map((etablissement, i) => `<div class='list-group-item' style="max-height:250px">
                                                    <p>Etablissement ${i + 1}</p>
                                                    <p class='mb-1 d-flex '>Siret:<span class='text-muted'>${etablissement.siret}</span></p>
                                                    <p class='mb-1'>Adresse : <span class='text-muted'>${etablissement.adresse}</span></p>
                                                    <button type="button" class="btn btn-primary justify-content-end">Voir</button>
                                                
                                                </div>`).join('')}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                            resultsDiv.innerHTML += card;
                        });
                    }else{
                        resultsDiv.innerHTML = 
                        `<div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Aucune entreprise trouvée</h5>
                                </div>
                            </div>
                        </div>`;
                    }
                });
            }
        });
    });
</script>
</body>
</html>
