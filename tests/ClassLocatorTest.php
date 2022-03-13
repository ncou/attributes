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

class ClassLocatorTest extends TestCase
{
    public function testLocateBadOrBrokenClasses(): void
    {
        $classes = ClassLocator::locate(__DIR__ . '/Classes/Bad/');

        $this->assertSame([], $classes);
    }

    public function testLocateNotLoadedClasses(): void
    {
        $classes = ClassLocator::locate(__DIR__ . '/Classes/NotLoaded/');

        $this->assertSame([], $classes);
    }

    public function testLocateGoodClasses(): void
    {
        $classes = ClassLocator::locate(__DIR__ . '/Classes/Good/');

        $this->assertSame(4, count($classes));
        $this->assertSame("Chiron\Attributes\Test\Classes\Good\AbstractClass", $classes[0]->getName());
        $this->assertSame("Chiron\Attributes\Test\Classes\Good\ClassA", $classes[1]->getName());
        $this->assertSame("Chiron\Attributes\Test\Classes\Good\ClassB", $classes[2]->getName());
        $this->assertSame("Chiron\Attributes\Test\Classes\Good\Inner\ClassC", $classes[3]->getName());
    }

    public function testLocateMultipleClasses(): void
    {
        $classes = ClassLocator::locate(__DIR__ . '/Classes/Multiple/');

        $this->assertSame(2, count($classes));
        $this->assertSame("Chiron\Attributes\Test\Classes\Multiple\WithIncludes", $classes[0]->getName());
        $this->assertSame("Chiron\Attributes\Test\Classes\Multiple\WithTwoClass", $classes[1]->getName());
    }
}
