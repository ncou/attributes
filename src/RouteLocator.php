<?php

declare(strict_types = 1);

namespace Chiron\Attributes;

use Chiron\Attributes\Attribute\RouteAttribute;
use Chiron\Core\Memory;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

use Chiron\Container\SingletonInterface;

use Chiron\Attributes\Config\AttributesConfig;

//************ DOCUMENTATION !!!!!
//https://github.com/spiral/docs/blob/fbd19d5dc8e02f04dae199fcac0091928be5f2f6/http/annotated-routes.md
//https://github.com/spiral/framework/blob/23299ff3442a9334494b9481b9adbd2b4a317907/src/AnnotatedRoutes/README.md
//https://github.com/spiral/annotated-routes/blob/8e0d96cf17ad5f0c5562490b667112508842be61/README.md

//https://symfony.com/doc/current/routing.html#creating-routes-as-attributes-or-annotations
//https://github.com/symfony/symfony-docs/blob/2cec233f47f7feba08a7e69386cd40d2b48bd865/routing.rst#creating-routes-as-attributes-or-annotations
//*****************

// https://github.com/spiral/annotated-routes/blob/a2c928290c067df7cd9bb6c405e37877051c4955/src/Command/ResetCommand.php

//https://github.com/symfony/routing/blob/9eeae93c32ca86746e5d38f3679e9569981038b1/Loader/AnnotationClassLoader.php
//https://github.com/symfony/symfony/blob/6.1/src/Symfony/Component/Routing/Loader/AnnotationFileLoader.php#L76

// TRAIT pour avoir un var_export qui utilise des short array => https://github.com/kenjis/ci4-attribute-routes/blob/1.x/src/AttributeRoutes/VarExportTrait.php

final class RouteLocator implements SingletonInterface
{
    public const MEMORY_SECTION = 'routes';

    private AttributesConfig $config;
    private Memory $memory;

    // TODO : Essayer de supporter un tableau de $path ? dans le cas ou on souhaite scanner plusieurs répertoires de Controllers pour trouver les attributs ????
    public function __construct(AttributesConfig $config, Memory $memory)
    {
        $this->config = $config; // TODO : il faudra surement lever une exception si le répertoire n'existe pas !!!!
        $this->memory = $memory;
    }

    public function locateRouteAttributes(): array
    {
        // TODO : eventuellement déporter ce IF dans la méthode findRoutes() au tout début du code !!!
        if (! $this->config->isLocatorEnabled()) {
            return [];
        }

        $useCache = $this->config->getUseCache();
        if ($useCache && $this->memory->exists(self::MEMORY_SECTION)) {
            return $this->memory->read(self::MEMORY_SECTION);
        }

        $routes = $this->findRoutes();
        // Always write the cache file but read-it only if the cache is enabled.
        $this->memory->write(self::MEMORY_SECTION, $routes); // TODO : eventuellement ne pas écrire le fichier si on utilise pas le cache, voir même supprimer le fichier si il existe et que le cache est désactivé (ca permet de nettoyer/supprimer le fichier de cache si l'utilisateur désactive l'ioption du cache, ca évitera de conserver un ancien fichier pour rien et qui pourrait être en déphasage si on ajoute des attributs et qu'on suite on réactive le cache !!!!)

        return $routes;
    }

    private function findRoutes(): array
    {
        // TODO : il faudra surement lever une exception si le répertoire n'existe pas !!!!
        $path = directory($this->config->getControllerDirectory()); // TODO : faire ce directory() directement dans le getteur de la classe de config !!! et il faudra aussi ajouter ou virer le '/' à la fin de ce path. je ne pense pas que cela change grand chose mais à vérifier !!!!!
        $classes = ClassLocator::locate($path);

        $result = [];
        foreach ($classes as $class) {
            // Ignore abstract classes.
            if ($class->isAbstract()) {
                continue;
            }

            foreach ($class->getMethods() as $method) {
                // TODO : vérifier si le fait que la méthode soit statique pose un probléme !!!! il faudrait simplement faire un "if not isPublic continue;"
                // Ignore non-available methods.
                if ($method->isStatic() ||
                    $method->isPrivate() ||
                    $method->isProtected()) {
                    continue;
                }

                foreach ($this->getAttributes($method) as $attribute)
                {
                    $result[] = [
                        'path' => $attribute->path,
                        'port' => $attribute->port,
                        'scheme' => $attribute->scheme,
                        'host' => $attribute->host,
                        'name' => $attribute->name,
                        'methods' => $attribute->methods,
                        'defaults' => $attribute->defaults,
                        'requirements' => $attribute->requirements,
                        'middlewares' => $attribute->middlewares,
                        'handler' => [$class->getName(), $method->getName()],
                    ];

                }
            }
        }

        return $result;
    }

    /**
     * @param ReflectionClass|ReflectionMethod $reflection
     *
     * @return iterable<int, RouteAttribute>
     */
    //https://github.com/symfony/symfony/blob/6.1/src/Symfony/Component/Config/Resource/ReflectionClassResource.php#L123
    private function getAttributes(ReflectionClass|ReflectionMethod $reflection): iterable
    {
        $attributes = $reflection->getAttributes(
            RouteAttribute::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        foreach ($attributes as $attribute) {
            yield $attribute->newInstance();
        }
    }
}
