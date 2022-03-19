<?php

declare(strict_types=1);

namespace Chiron\Attributes\Test;

use Chiron\Attributes\ClassLocator;
use Chiron\Injector\Exception\InjectorException;
use Chiron\Injector\Exception\InvalidParameterTypeException;
use Chiron\Injector\Exception\MissingRequiredParameterException;
use Chiron\Injector\Exception\NotCallableException;
use Chiron\Injector\Injector;
use Chiron\Container\Container;
use Chiron\Injector\Test\Support\CallStaticObject;
use Chiron\Injector\Test\Support\CallStaticWithSelfObject;
use Chiron\Injector\Test\Support\CallStaticWithStaticObject;
use Chiron\Injector\Test\Support\ColorInterface;
use Chiron\Injector\Test\Support\EngineInterface;
use Chiron\Injector\Test\Support\EngineMarkTwo;
use Chiron\Injector\Test\Support\StaticMethod;
use Chiron\Injector\Test\Support\StaticWithSelfObject;
use Chiron\Injector\Test\Support\StaticWithStaticObject;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use StdClass;
use Chiron\Attributes\RouteAttributeReader;
use Chiron\Attributes\Bootloader\RouteAttributeBootloader;
use Chiron\Routing\Map;
use Chiron\Core\Memory;
use Chiron\Attributes\Config\AttributesConfig;
use Chiron\Event\ListenerProvider;
use Chiron\Event\EventDispatcher;

//https://github.com/symfony/symfony/blob/60ce5a3dfbd90fad60cd39fcb3d7bf7888a48659/src/Symfony/Component/Routing/Tests/Loader/AnnotationClassLoaderTest.php#L52

class RouteAttributeBootloaderTest extends TestCase
{
    private Map $map;
    private function bootRouteAttributes(string $path): void
    {

        $container = new Container();

        $eventDispatcher = new EventDispatcher(new ListenerProvider());

        $this->map = new Map();
        $this->map->setContainer($container);
        $this->map->setEventDispatcher($eventDispatcher);
        $container->bind(Map::class, $this->map);

        /* // Exemple de code pour générer un fichier ou répertoire temporaire !!!
$tempfile = sys_get_temp_dir() . '/temp-' . md5(microtime());
// Directory used to download the sources
$sourcePath = sys_get_temp_dir().'/composer_archive'.uniqid();
$filesystem->ensureDirectoryExists($sourcePath);
*/

        $cachePath = sys_get_temp_dir() . '/routes_locator/';
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755);
        }
        $memory = new Memory($cachePath); // TODO : initialiser la méthode stepup() avec la création du répertoire ? et dans la méthode tearDown faire un clear ???
        $container->bind(Memory::class, $memory);

        $config = new AttributesConfig([
            'locator_enabled' => true,
            'controller_directory' => $path,
            'use_cache' => false,
        ]);
        $container->bind(AttributesConfig::class, $config);

        $injector = new Injector($container);

        $bootloader = new RouteAttributeBootloader();
        $bootloader->bootload($injector);
    }

    public function testSimplePathRoute(): void
    {
        $this->bootRouteAttributes(__DIR__ . '/Fixtures/ActionPathController/');
        $routes = $this->map;

        $this->assertCount(1, $routes);
        $this->assertEquals('/path', $routes->getRoute('action')->getPath());
    }


    public function testMethodsAndSchemes()
    {
        $this->bootRouteAttributes(__DIR__ . '/Fixtures/MethodsAndSchemes/');
        $routes = $this->map;

        $this->assertSame(['GET'], $routes->getRoute('array_one')->getAllowedMethods());
        $this->assertSame('http', $routes->getRoute('array_one')->getScheme());
        $this->assertSame(['POST'], $routes->getRoute('string')->getAllowedMethods());
        $this->assertSame('https', $routes->getRoute('string')->getScheme());
    }


    public function testMethodActionControllers()
    {
        $this->bootRouteAttributes(__DIR__ . '/Fixtures/MethodActionControllers/');
        $routes = $this->map;

        $this->assertEquals('/the/path/put', $routes->getRoute('put')->getPath());
        $this->assertSame(['PUT'], $routes->getRoute('put')->getAllowedMethods());
        $this->assertEquals('/the/path/post', $routes->getRoute('post')->getPath());
        $this->assertSame(['POST'], $routes->getRoute('post')->getAllowedMethods());
    }

    public function testRouteWithoutName()
    {
        $this->bootRouteAttributes(__DIR__ . '/Fixtures/MissingRouteNameController/');
        $routes = $this->map;

        $this->assertCount(1, $routes);
        $this->assertEquals('/path', ($routes->getRoutes()[0])->getPath());
    }
}

