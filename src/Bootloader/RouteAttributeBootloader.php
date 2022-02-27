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

use Chiron\Attributes\RouteAttributeReader;

// https://github.com/spiral/annotated-routes/blob/a2c928290c067df7cd9bb6c405e37877051c4955/src/Bootloader/AnnotatedRoutesBootloader.php#L68

class RouteAttributeBootloader extends AbstractBootloader
{
    public function boot(Mapper $map)
    {
        $loader = new RouteAttributeReader(directory('@app/Controllers'));

        $routes = $loader->loadRouteAttributes();

        foreach ($routes as $route) {
            //die(var_dump($route->getName()));

/*
            if ('__invoke' === $method->getName()) {
                $handler = $class->getName(); // TODO : vérifier si il faut pas laisser concaténé le methode name (cad concaténer "::__invoke")
            } else {
                $handler = $class->getName().'::'.$method->getName();
            }
*/
            $handler = \Controllers\MainController::class . '::' . 'ping';

            $map->route($route->getPath())->to($handler)->name($route->getName());
        }

        //die(var_dump($routes));

    }
}

