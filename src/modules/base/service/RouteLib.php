<?php
namespace App\Modules\Crm\Company\Insee\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Exception;
class RouteLib
{
    
    private \stdClass $requestCollection;
    private $request;

    public function __construct(string $path, string $controller, array $methods)
    {   
        $this->request = Request::createFromGlobals();
        $url = parse_url($_SERVER['REQUEST_URI']??'');
        $this->requestCollection = new \stdClass();
        $this->requestCollection->route=[
            'method' => $this->request->getMethod(),
            'route' => $path,
            'get' => $this->request->query->all(),
            'post' => $this->request->request->all(),
            'domain' => $this->request->getHost(),
            'current' => true,
            'before'=>$this->request->query->get("before")??$this->request->request->get("before"),
            'url'=>$url,
            'path' => $url['path']??'',
        ];
        $this->requestCollection->messages=[];
        $this->requestCollection->errorRoutes=[];
        $this->requestCollection->routesAllowed = [];

    }

    
    public function controller(string $controller, string $action): self
    { 
        if(!empty($this->requestCollection->send)){
            $this->requestCollection->route[$this->requestCollection->send['name']] =  array_merge($this->requestCollection->send,[ 'controller' => $controller,
            'action' => $action
            ]);
        }
        return $this;
    }

    public function route(): self{
        try{
            
            if( !$this->returnRoute() ||
                !$this->routeExists() ||
                !$this->methodAllowed() ||
                !$this->routeAllowed()
                ){
                throw new Exception('Route not found');
            }
        }catch(\Exception $e){

            $arMessage= $e->getMessage();
        }
    
        
        return $this;
    }

    public function returnRoute(): self{
        foreach($this->requestCollection->routes as $route) {
            if($route['route']==$this->requestCollection->route['route']) {
                $this->requestCollection->route['exists'] = true;
                $this->requestCollection->route= array_merge($this->requestCollection->route,$route);
                break;
            }
        }
        return $this;
    }

    public function error($errorCode){
        $this->requestCollection->error=$this->requestCollection->errorRoutes[$errorCode];
        return $this;
    }

    public function errors($errorCode,$path){
        $this->requestCollection->errors[$errorCode]=$path;
        return $this;
    }


    public function Get(string $name, string $route): self
    {
        $this->requestCollection->routes[$name] = [
            'method' => 'GET',
            'route' => $route,
            'name' => $name,
        ];
        $this->requestCollection->send= $this->requestCollection->routes[$name] ;
        return $this;
    }
    public function Post(string $name, array $route): self
    {
        $this->requestCollection->route[$name] = [
            'method' => 'POST',
            'route' => $route,
            'name' => $name,
        ];
        $this->requestCollection->send= $this->requestCollection->routes[$name] ;
        return $this;
    }
    public function Patch(string $name, array $route): self
    {
        $this->requestCollection->route[$name] = [
            'method' => 'PATCH',
            'route' => $route,
            'name' => $name,
        ];
        $this->requestCollection->send= $this->requestCollection->routes[$name] ;
        return $this;
    }
    public function Delete(string $name, array $route): self
    {
        $this->requestCollection->routes[$name] = [
            'method' => 'DELETE',
            'route' => $route,
            'name' => $name,
        ];
        $this->requestCollection->send= $this->requestCollection->routes[$name] ;
        return $this;
    }


    public function getErrors(): array
    {
        return $this->requestCollection->errors;
    }

    private function bool($bool, $message){
        try{
            if(!$bool){
                throw new Exception($message);
            }
        }catch(\Exception $e){
            $this->requestCollection->message[] = 'Error:'.$e->getMessage();
            return false;
        }
        return $bool;
    }

    public function methodAllowed(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === $this->requestCollection->route['method'];
    }
    public function routeAllowed(): bool
    {   
        return array_key_exists($this->requestCollection->route['name'], $this->requestCollection->routesAllowed);
    }

    public function routeExists(): bool
    {   
        return in_array($this->requestCollection->route['name'], array_values($this->requestCollection->routes));
    }

    public function allows( bool $allowed=true): self{
        if($allowed){
            $this->requestCollection->routesAllowed[] = $this->requestCollection->send['name'];
        }
        return $this;
    }

    public function allowsGet(array $get): self{
        $this->requestCollection->routes[$this->requestCollection->route['name']]= array_merge($this->requestCollection->routes[$this->requestCollection->route['name']]??[], 
        [
            'allowedGet'=>$get
        ]);
        return $this;
    }

    public function allowsPost(array $post): self{
        $this->requestCollection->routes[$this->requestCollection->route['name']]= array_merge($this->requestCollection->routes[$this->requestCollection->route['name']]??[], 
        [
            'alloewedPost'=>$post
        ]);
        return $this;
    }

    public function allowsPatch(array $patch): self{
        $this->requestCollection->routes[$this->requestCollection->route['name']]= array_merge($this->requestCollection->routes[$this->requestCollection->route['name']]??[], 
        [
            'allowedPatch'=>$patch
        ]);
        return $this;
    }

    public function allowsDelete(array $delete): self{
        $this->requestCollection->routes[$this->requestCollection->route['name']]= array_merge($this->requestCollection->routes[$this->requestCollection->route['name']]??[], 
        [
            'alloewedDelete'=>$delete
        ]);
        return $this;
    }
}
