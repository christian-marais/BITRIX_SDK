<?php

namespace NS2B\SDK\MODULES\CRM\COMPANY\STORAGE\ROUTES\WEB;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiRouteProvider; 
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use NS2B\SDK\MODULES\CRM\COMPANY\INSEE\CompanyComponent;

class WebRouteProvider
{
    private $routes;
    private $baseRoute= '/company/';
    private $component;
    private $company;

    public function __construct()
    {   
        _error_log("Starting router...."); 
        $this->routes = new RouteCollection();
        _error_log("processing routing...");
        $this->defineRoutes();
        _error_log("closing router...");
    }

    private function defineRoutes()
    {
        // Route api pour ajouter un contact dans bitrix
        $this->routes->add('company_upload_file', new Route(
            $this->baseRoute.'{id}/storage/upload/{code}/',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\STORAGE\ROUTES\WEB\WebController::uploadCompanyFile',
                'methods' => ['GET'],
                'company' => $this->company,
            ],
            [
                'id' => '\d+',
                'code' => '[a-z0-9]+'
            ]
        ));
        
        // Ajouter les routes API
        $apiRouteProvider = new ApiRouteProvider();
        $this->routes->addCollection($apiRouteProvider->getRoutes());
    }

    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    public function launch(Request $request): Response
    {
        // Créer le contexte de la requête
        $context = new RequestContext();
        $context->fromRequest($request);

        // Créer le matcher d'URL
        $matcher = new UrlMatcher($this->routes, $context);

        try {
            // Tenter de faire correspondre la route actuelle
            $parameters = $matcher->match($request->getPathInfo());
            if (!isset($parameters['_controller'])) {
                throw new ResourceNotFoundException();
            }

            // Appeler le contrôleur
            list($controllerClass, $method) = explode('::', $parameters['_controller']);
            $controller = new $controllerClass();
            $response = $controller->$method($request, ...array_filter($parameters, function($key) {
                return !str_starts_with($key, '_');
            }, ARRAY_FILTER_USE_KEY));
            
            return $response;
            
            exit;
        } catch (ResourceNotFoundException $e) {
            throw $e; // Remonter l'exception pour être gérée par index.php
        } catch (\Exception $e) {
            return new Response('Erreur : ' . $e->getMessage(), 500);
        }
    }
}
