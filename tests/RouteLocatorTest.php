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
use Chiron\Attributes\RouteAttributeReader;
use Chiron\Core\Memory;

//https://github.com/symfony/symfony/blob/60ce5a3dfbd90fad60cd39fcb3d7bf7888a48659/src/Symfony/Component/Routing/Tests/Loader/AnnotationClassLoaderTest.php#L52

class RouteLocatorTest extends TestCase
{
    private function createLocator(string $path): RouteAttributeReader
    {
        $memory = new Memory(sys_get_temp_dir() . '/routes_locator/');

        return new RouteAttributeReader($path, $memory);
    }
    public function testSimplePathRoute(): void
    {
        $locator = $this->createLocator(__DIR__ . '/Fixtures/ActionPathController/');
        $routes = $locator->readRouteAttributes();

        $this->assertCount(1, $routes);
        $this->assertSame([
            'path' => '/path',
            'name' => 'action',
            'handler' => ['Chiron\Attributes\Test\Fixtures\ActionPathController\ActionPathController', 'action']
        ],
        $routes[0]);
    }

    public function testMethodsAndSchemes()
    {
        $locator = $this->createLocator(__DIR__ . '/Fixtures/MethodsAndSchemes/');
        $routes = $locator->readRouteAttributes();

        $this->assertSame(['GET', 'POST'], $routes->get('array_many')->getMethods());
        $this->assertSame(['http', 'https'], $routes->get('array_many')->getSchemes());
        $this->assertSame(['GET'], $routes->get('array_one')->getMethods());
        $this->assertSame(['http'], $routes->get('array_one')->getSchemes());
        $this->assertSame(['POST'], $routes->get('string')->getMethods());
        $this->assertSame(['https'], $routes->get('string')->getSchemes());
    }

    public function testMethodActionControllers()
    {
        $routes = $this->loader->load($this->getNamespace().'\MethodActionControllers');
        $this->assertSame(['put', 'post'], array_keys($routes->all()));
        $this->assertEquals('/the/path', $routes->get('put')->getPath());
        $this->assertEquals('/the/path', $routes->get('post')->getPath());
    }

    public function testDefaultValuesForMethods()
    {
        $routes = $this->loader->load($this->getNamespace().'\DefaultValueController');
        $this->assertCount(3, $routes);
        $this->assertEquals('/{default}/path', $routes->get('action')->getPath());
        $this->assertEquals('value', $routes->get('action')->getDefault('default'));
        $this->assertEquals('Symfony', $routes->get('hello_with_default')->getDefault('name'));
        $this->assertEquals('World', $routes->get('hello_without_default')->getDefault('name'));
    }

    public function testRouteWithoutName()
    {
        $routes = $this->loader->load($this->getNamespace().'\MissingRouteNameController')->all();
        $this->assertCount(1, $routes);
        $this->assertEquals('/path', reset($routes)->getPath());
    }

    public function testNothingButName()
    {
        $routes = $this->loader->load($this->getNamespace().'\NothingButNameController')->all();
        $this->assertCount(1, $routes);
        $this->assertEquals('/', reset($routes)->getPath());
    }

    public function testNonExistingClass()
    {
        $this->expectException(\LogicException::class);
        $this->loader->load('ClassThatDoesNotExist');
    }

    public function testLoadingAbstractClass()
    {
        $this->expectException(\LogicException::class);
        $this->loader->load(AbstractClassController::class);
    }
}
