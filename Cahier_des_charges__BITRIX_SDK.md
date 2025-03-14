# Cahier des Charges pour IA - Projet BITRIX SDK

## Métadonnées du Projet
```yaml
nom_projet: BITRIX_SDK
version: 1.0.0
type: SDK PHP
framework_principal: Bitrix24
compatibilité_php: ">=8.0"
namespace_base: NS2B\SDK
```

## 1. Structure du Projet
```yaml
structure:
  racine: BITRIX_SDK/
  dossiers_principaux:
    - public/
    - src/
      - modules/
      - database/
    - vendor/
  fichiers_configuration:
    - composer.json
    - composer.lock
    - .gitignore
```

## 2. Dépendances Requises
```json
{
  "require": {
    "php": ">=8.0",
    "bitrix24/b24phpsdk": "^1.2",
    "symfony/http-foundation": "^7.2"
  }
}
```

## 3. Architecture des Modules

### 3.1 Module Base
```yaml
module:
  nom: base
  namespace: NS2B\SDK\MODULES\base
  fichiers_principaux:
    - WebhookManager.php
    - CrmActivity.php
    - CrmCompany.php
    - base.php
  interfaces_requises:
    - WebhookInterface
    - CrmEntityInterface
  classes_abstraites:
    - BaseModule
    - BaseController
    - BaseModel
```

### 3.2 Module CRM Company INSEE
```yaml
module:
  nom: crm.company.insee
  namespace: NS2B\SDK\MODULES\crm.company.insee
  fonctionnalités:
    - recherche_entreprise:
        endpoint: /search
        méthode: GET
        paramètres:
          - q: string
          - page: int
          - per_page: int
    - validation_siret:
        endpoint: /validate
        méthode: POST
        paramètres:
          - siret: string
    - synchronisation:
        endpoint: /sync
        méthode: POST
        paramètres:
          - company_id: int
```

### 3.3 Module CRM Event List
```yaml
module:
  nom: crm.event.list
  namespace: NS2B\SDK\MODULES\crm.event.list
  fonctionnalités:
    - liste_événements:
        endpoint: /events
        méthode: GET
        paramètres:
          - type: string
          - date_start: datetime
          - date_end: datetime
    - création_événement:
        endpoint: /events/create
        méthode: POST
        paramètres:
          - title: string
          - description: string
          - date: datetime
```

### 3.4 Module CRM Mail List
```yaml
module:
  nom: crm.mail.list
  namespace: NS2B\SDK\MODULES\crm.mail.list
  fonctionnalités:
    - gestion_templates:
        endpoint: /templates
        méthode: GET/POST
        paramètres:
          - name: string
          - content: string
          - variables: array
```

## 4. Instructions de Développement

### 4.1 Standards de Code
```yaml
standards:
  - PSR-1: Basic Coding Standard
  - PSR-2: Coding Style Guide
  - PSR-4: Autoloading Standard
  - PSR-12: Extended Coding Style
```

### 4.2 Pattern de Classes
```php
// Exemple de structure de classe pour les modules
namespace NS2B\SDK\MODULES;

abstract class BaseModule {
    protected $client;
    protected $config;
    
    public function __construct(array $config) {
        $this->config = $config;
        $this->initialize();
    }
    
    abstract protected function initialize(): void;
}

// Interface pour les entités CRM
interface CrmEntityInterface {
    public function create(array $data): int;
    public function read(int $id): array;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
```

### 4.3 Gestion des Erreurs
```yaml
exceptions:
  hiérarchie:
    - SDKException:
        - ValidationException
        - ConfigurationException
        - ApiException:
            - AuthenticationException
            - RateLimitException
```

## 5. Templates et Interfaces

### 5.1 Template de Base
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>{MODULE_NAME}</title>
</head>
<body>
    <div class="container">
        {CONTENT}
    </div>
</body>
</html>
```

### 5.2 Structure API Response
```yaml
api_response:
  format: JSON
  structure:
    success: boolean
    data: object|array|null
    error:
      code: integer
      message: string
      details: object|null
```

## 6. Tests Requis

### 6.1 Tests Unitaires
```yaml
tests_unitaires:
  couverture_minimale: 80%
  dossiers_à_tester:
    - src/modules/*/
    - src/database/
  types_tests:
    - Validation des entrées
    - Gestion des erreurs
    - Intégration API
    - Cache
```

### 6.2 Tests d'Intégration
```yaml
tests_integration:
  scenarios:
    - création_entreprise:
        - recherche_siret
        - validation_données
        - création_bitrix
        - vérification_synchronisation
    - gestion_événements:
        - création
        - modification
        - suppression
        - notification
```

## 7. Documentation à Générer

### 7.1 Structure Documentation
```yaml
documentation:
  format: Markdown
  sections:
    - installation
    - configuration
    - utilisation_modules
    - api_reference
    - exemples_code
    - troubleshooting
```

## 8. Critères de Validation

### 8.1 Qualité de Code
```yaml
qualité_code:
  outils:
    - phpstan: niveau 8
    - phpcs: PSR-12
    - phpmd: règles_standard
  métriques:
    - complexité_cyclomatique: max 10
    - taille_méthode: max 20 lignes
    - taille_classe: max 200 lignes
```

### 8.2 Performance
```yaml
performance:
  temps_réponse:
    api: max 500ms
    recherche: max 1s
  mémoire:
    limite: 128M
  cache:
    durée: 1h
    type: Redis
```

## 9. Instructions de Déploiement
```yaml
déploiement:
  étapes:
    1: Vérification des dépendances
    2: Installation via Composer
    3: Configuration environnement
    4: Migration base de données
    5: Génération documentation
    6: Tests automatisés
    7: Vérification sécurité
```

## 10. Maintenance
```yaml
maintenance:
  logs:
    format: JSON
    rotation: quotidienne
    rétention: 30 jours
  monitoring:
    métriques:
      - temps_réponse
      - utilisation_mémoire
      - erreurs_api
    alertes:
      - seuil_erreurs: >5%
      - temps_réponse: >2s
```

## Notes pour l'IA
1. Respecter strictement la structure des namespaces
2. Implémenter tous les interfaces définis
3. Générer les tests unitaires pour chaque classe
4. Documenter chaque méthode publique
5. Utiliser les types stricts PHP
6. Implémenter la gestion des erreurs à chaque niveau
7. Assurer la compatibilité avec l'API Bitrix24
8. Maintenir une couverture de tests >80%
9. Suivre les principes SOLID
10. Implémenter le pattern Repository pour l'accès aux données
