<?php

namespace NS2B\SDK\MODULES\BASE;

use NS2B\SDK\DATABASE\DatabaseSQLite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use NS2B\SDK\MODULES\BASE\Base;

class WebhookManager extends Base
{
    protected $database;
    private $webhookCollection;
    private $template;
    private $includes = [];

    public function __construct(DatabaseSQLite $database)
    {
        $this->database = $database;
        $this->entity = 'webhooks';
        $this->template = null;
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
    protected function generateHtml(string $template, array $arResult = []): string
    {
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

    /**
     * Méthode générique pour injecter du HTML dans des éléments spécifiques
     */
    public function render(): void
    {
        if (!$this->template) {
            $this->template = __DIR__ . '/404.php';
        }

        // Charger le template principal
        $pageContent = $this->generateHtml($this->template);

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = true;
        
        // Charger le HTML avec gestion des caractères spéciaux
        @$dom->loadHTML($pageContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Injecter tous les includes dans leurs éléments cibles respectifs
        foreach ($this->includes as $id => $content) {
            $fragment = $dom->createDocumentFragment();
            @$fragment->appendXML($content);
            
            $target = $dom->getElementById($id);
            if ($target) {
                // Vider le contenu existant
                while ($target->firstChild) {
                    $target->removeChild($target->firstChild);
                }
                $target->appendChild($fragment);
            }
        }
        
        echo $dom->saveHTML();
        exit;
    }

    /**
     * Sauvegarde un webhook dans la base de données.
     */
    public function saveWebhook(): self|JsonResponse
    {
        $request = Request::createFromGlobals();
        
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $webhook = filter_var($data['webhook'] ?? '', FILTER_VALIDATE_URL);
            
            if ($webhook) {
                $this->database->insert($this->entity, [
                    'WEBHOOK' => $webhook
                ]);
                
                return new JsonResponse([
                    'success' => true,
                    'webhook' => $webhook
                ], Response::HTTP_OK);
            }
            
            return new JsonResponse([
                'success' => false,
                'error' => 'URL invalide'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        return $this;
    }

    /**
     * Récupère le dernier webhook enregistré.
     */
    public function getWebhook(): ?string
    {
        $result = $this->database->selects($this->entity);
        $webhook = $result[0]['WEBHOOK'] ?? null;
        if ($webhook) {
            $url = parse_url($webhook);
            $webhook = $url['scheme'] . '://' . $url['host'] . preg_replace('/\/[0-9]\/+/i', '*', $url['path']);
        }
        return $webhook;
    }

    /**
     * Récupère le webhook au format JSON pour les requêtes AJAX.
     */
    protected function ajaxGetWebhook(): JsonResponse
    {
        $request = Request::createFromGlobals();
        
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
    public function askWebhook(): self|Response
    {   
        $request = Request::createFromGlobals();
        switch ($request->query->get('action')) {
            case 'saveWebhook':
                return $this->saveWebhook();
                
            case 'getWebhook':
                return $this->ajaxGetWebhook();

            case 'displayWebhook':
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

    /**
     * Affiche la page avec la popup de webhook
     */
    public function renderWebhookPopup(): self
    {
        $this->processTemplate(__DIR__ . '/snippets/popupTemplate.php', [
            'attribut_id' => 'main-content'
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
}
