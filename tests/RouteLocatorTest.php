<?php

declare(strict_types=1);

namespace Chiron\Attributes\Test;

use Chiron\Attributes\ClassLocator;
use Chiron\Injector\Exception\InjectorException;
use Chiron\Injector\Exception\InvalidParameterTypeException;
use Chiron\Injector\Exception\MissingRequiredParameterException;
use Chiron\Injector\Exception\NotCallableException;
use Chiron\Injector\Injector;
use Chiron\Injector\Test\Container\SimpleContainer as Container;
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
use Chiron\Attributes\RouteLocator;
use Chiron\Attributes\Bootloader\RouteAttributeBootloader;
use Chiron\Routing\Map;

use Chiron\Core\Memory;
use Chiron\Attributes\Config\AttributesConfig;

//https://github.com/symfony/symfony/blob/60ce5a3dfbd90fad60cd39fcb3d7bf7888a48659/src/Symfony/Component/Routing/Tests/Loader/AnnotationClassLoaderTest.php#L52

class RouteLocatorTest extends TestCase
{
    private function createLocator(string $path): RouteLocator
    {

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

        $config = new AttributesConfig([
            'locator_enabled' => true,
            'controller_directory' => $path,
            'use_cache' => false,
        ]);

        return new RouteLocator($config, $memory);
    }

    public function testSimplePathRoute(): void
    {
        $locator = $this->createLocator(__DIR__ . '/Fixtures/ActionPathController/');
        $routes = $locator->locateRouteAttributes();

        $this->assertCount(1, $routes);
        $this->assertSame([
            'path' => '/path',
            'port' => null,
            'scheme' => null,
            'host' => null,
            'name' => 'action',
            'methods' => [],
            'defaults' => [],
            'requirements' => [],
            'middlewares' => [],
            'handler' => ['Chiron\Attributes\Test\Fixtures\ActionPathController\ActionPathController', 'action']
        ],
        $routes[0]);
    }
}

