<?php

namespace Chiron\Attributes\Bootloader;

//use Chiron\Http\Psr\Response;
use Chiron\Http\Response\HtmlResponse;

use Psr\Container\ContainerInterface;
use Chiron\Views\TemplateRendererInterface;
use Chiron\Container\Container;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use LogicException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Chiron\Config\Config;
use Dotenv\Dotenv;
use Dotenv\Environment\DotenvFactory;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Environment\Adapter\PutenvAdapter;
use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\ServerConstAdapter;
use Chiron\Routing\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;
use Chiron\Routing\Target\TargetFactory;
use Chiron\Routing\Route;
use Chiron\Routing\Map as Mapper;
use Chiron\Container\BindingInterface;

use Chiron\Routing\Facade\Map;
use Chiron\Facade\Target;

use Chiron\Routing\Target\Action;
use Chiron\Routing\Target\Controller;
use Chiron\Routing\Target\Group;
use Chiron\Routing\Target\Namespaced;

use Middlewares\MiddlewareOne;
use Middlewares\MiddlewareParameterized;

use Controllers\FooController;
use Controllers\BarController;

use Chiron\Attributes\RouteLocator;

use Chiron\Core\Memory;

// https://github.com/spiral/annotated-routes/blob/a2c928290c067df7cd9bb6c405e37877051c4955/src/Bootloader/AnnotatedRoutesBootloader.php#L68
// https://github.com/symfony/symfony/blob/3eb26c1de901478c09f3965748c6a841eaebe7f0/src/Symfony/Component/Routing/Loader/AnnotationClassLoader.php#L147

class RouteAttributeBootloader extends AbstractBootloader
{
    public function boot(RouteLocator $locator, Mapper $map)
    {
        $attributes = $locator->locateRouteAttributes();

        // Register a new route using attributes values.
        foreach ($attributes as $attribute) {
            $route = new Route($attribute['path']);

            if ($attribute['port'] !== null) {
                $route = $route->setPort($attribute['port']);
            }
            if ($attribute['scheme'] !== null) {
                $route = $route->setScheme($attribute['scheme']);
            }
            if ($attribute['host'] !== null) {
                $route = $route->setHost($attribute['host']);
            }
            if ($attribute['name'] !== null) {
                $route = $route->setName($attribute['name']);
            }
            if ($attribute['methods'] !== []) {
                $route = $route->setAllowedMethods($attribute['methods']);
            }
            if ($attribute['defaults'] !== []) {
                $route = $route->setDefaults($attribute['defaults']);
            }
            if ($attribute['requirements'] !== []) {
                $route = $route->setRequirements($attribute['requirements']);
            }
            if ($attribute['middlewares'] !== []) {
                foreach ($attribute['middlewares'] as $middleware) {
                    $route = $route->middleware($middleware); // TODO : prévoir une méthode setMiddlewares() dans la classe Route !!!!
                }
            }

            $map->addRoute($route)->to($attribute['handler']);
        }
    }
}

