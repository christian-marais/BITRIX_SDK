<?php

namespace NS2B\SDK\MODULES\BASE;

use NS2B\SDK\DATABASE\DatabaseSQLite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use NS2B\SDK\MODULES\BASE\Base;
use DOMDocument;

class WebhookManager extends Base
{
    protected $database;
    private $webhookCollection;
    private $template;
    private $includes = [];
    private const CIPHER_METHOD = 'aes-256-cbc';
    private const KEY_LENGTH = 32; // 256 bits
    private const IV_LENGTH = 16;  // 128 bits

    public function __construct(DatabaseSQLite $database)
    {
        $this->database = $database;
        $this->entity = 'webhooks';
        $this->template = __DIR__ . '/404.php';
    }
    

    protected function getCollection(){
        return $this->webhookCollection;
    }

    /**
     * Définit le template principal
     */
    public function setTemplate(?string $template): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Ajoute du contenu HTML pour un ID spécifique
     */
    protected function addInclude(string $targetId, string $html): self
    {
        $this->includes[$targetId] = $html;
        return $this;
    }

    /**
     * Génère le HTML à partir d'un template et de variables
     */
    protected function generateHtml(string $template, array $arResult = [], array $contents= []): string
    {
        if (!file_exists($template)) {
            throw new \RuntimeException("Le template '$template' n'existe pas");
        }

        ob_start();
        extract($arResult);
        include $template;
        return ob_get_clean();
    }

    /**
     * Génère et stocke le HTML à partir d'un template avec des variables
     */
    public function processTemplate(string $template, array $arResult): self
    {
        if (!isset($arResult['attribut_id'])) {
            throw new \InvalidArgumentException("La clé 'attribut_id' est requise dans arResult");
        }

        $html = $this->generateHtml($template, $arResult);
        $this->addInclude($arResult['attribut_id'], $html);
        
        return $this;
    }

    public function render(){
        return $this->generateHtml($this->template, contents:$this->includes);
    }
    /**
     * Récupère le dernier webhook enregistré.
     */
    public function getWebhook(): ?string
    {
        $result = $this->database->selects($this->entity);
        $webhook = $result[0]['WEBHOOK'] ?? null;
        if ($webhook) {
            $url = parse_url($this->decryptWebhook($webhook));
            $webhook = $url['scheme'] . '://' . $url['host'] . preg_replace('/[0-9]/', '*', $url['path']??''); 
        }
        return $webhook;
    }

    /**
     * Récupère le webhook au format JSON pour les requêtes AJAX.
     */
    protected function ajaxGetWebhook(Request $request): JsonResponse
    {
        if ($request->isMethod('GET')) {
            $result = $this->getWebhook();
            return new JsonResponse([
                'success' => true,
                'webhook' => $result
            ], Response::HTTP_OK);
        }
        
        return new JsonResponse([
            'success' => false,
            'error' => 'Méthode non autorisée'
        ], Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Supprime tous les webhooks.
     */
    public function deleteWebhook(): self
    {
        $this->database->deleteAll($this->entity);
        return $this;
    }

    /**
     * Gère les différentes actions liées aux webhooks.
     */
    public function askWebhook(): mixed
    {   
        $request = Request::createFromGlobals();
       switch ($request->query->get('action')) {
            case 'savewebhook':
                $this->save($request);
                break;
                
            case 'getwebhook':
                return$this->ajaxGetWebhook($request);
                break;

            case 'displaywebhook':
                $this->renderWebhookPopup();
                break;

            default:
                if (!$this->getWebhook()) {
                    $this->renderWebhookPopup();
                }
                break;
        }

        return $this;
    }

    public function show(){
        $this->renderWebhookPopup();
        return $this->render();
    }

        /**
     * Sauvegarde un webhook dans la base de données.
     */
    public function save(Request $request): array
    {   
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $webhook = filter_var($data["data"]['webhook'] ?? '', FILTER_VALIDATE_URL);
            
            if ($webhook) {
                $message = [
                    'status' => 'success',
                    'message' => 'Webhook '.$webhook.'received successfully '
                ];
                $this->database->deleteAll($this->entity);
                if($this->database->insert(
                    $this->entity, 
                    [
                    'WEBHOOK' => $this->encryptWebhook($webhook)
                    ]
                )){
                    $message = [
                        'status' => 'success',
                        'message' => 'Webhook '.$webhook.'saved successfully '
                    ];
                }
                
            }else{
                $message = [
                    'status' => 'error',
                    'message' => 'Webhook not valid. Data :'.$data["data"]['webhook']
                ];
            }
        }else{
            $message = [
                'status' => 'error',
                'message' => 'Method not allowed'
            ];
        }

        return $message;
    }

    

    /**
     * Affiche la page avec la popup de webhook
     */
    public function renderWebhookPopup($arResult=[]): self
    {
        $this->processTemplate(__DIR__ . '/snippets/popupTemplate.php', [
            'attribut_id' => 'main-content',
            'webhookUrl' => $this->getWebhook()??'',
            'arResult' => $arResult
        ]);
        return $this;
    }

    /**
     * Affiche la page d'accueil
     */
    public function renderHome(): self
    {
        $this->template = dirname(__DIR__, 2) . '/modules/crm.company.insee/templates/templateblank.php';
        return $this;
    }

    /**
     * Crypte une URL de webhook
     * @param string $webhook L'URL du webhook à crypter
     * @return string Le webhook crypté en base64
     */
    public function encryptWebhook(string $webhook): string 
    {
        // Générer une clé sécurisée et un vecteur d'initialisation
        $key = openssl_random_pseudo_bytes(self::KEY_LENGTH);
        $iv = openssl_random_pseudo_bytes(self::IV_LENGTH);
        
        // Crypter le webhook
        $encrypted = openssl_encrypt(
            $webhook,
            self::CIPHER_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        if ($encrypted === false) {
            throw new \RuntimeException("Erreur lors du cryptage du webhook");
        }
        
        // Combiner IV + clé + données cryptées
        $combined = $iv . $key . $encrypted;
        
        // Encoder en base64 pour stockage sécurisé
        return base64_encode($combined);
    }

    /**
     * Décrypte une URL de webhook
     * @param string $encryptedWebhook Le webhook crypté en base64
     * @return string L'URL du webhook décryptée
     */
    public function decryptWebhook(string $encryptedWebhook): string 
    {
        // Décoder le webhook crypté
        $combined = base64_decode($encryptedWebhook);
        
        if ($combined === false) {
            throw new \RuntimeException("Données de webhook invalides");
        }
        
        // Extraire IV, clé et données cryptées
        $iv = substr($combined, 0, self::IV_LENGTH);
        $key = substr($combined, self::IV_LENGTH, self::KEY_LENGTH);
        $encrypted = substr($combined, self::IV_LENGTH + self::KEY_LENGTH);
        
        // Décrypter le webhook
        $decrypted = openssl_decrypt(
            $encrypted,
            self::CIPHER_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        if ($decrypted === false) {
            $this->deleteWebhook();
            throw new \RuntimeException("Erreur lors du décryptage du webhook");
        }
        
        return $decrypted;
    }

}
