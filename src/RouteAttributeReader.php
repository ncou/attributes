<?php

declare(strict_types = 1);

namespace Chiron\Attributes;

use Chiron\Filesystem\Filesystem;
use Chiron\Attributes\Attribute\RouteAttribute;

// TRAIT pour avoir un var_export qui utilise des short array => https://github.com/kenjis/ci4-attribute-routes/blob/1.x/src/AttributeRoutes/VarExportTrait.php

// TODO : renommer la classe en Loader !!!!
// TODO : prévoir un mécanisme de cache !!!
final class RouteAttributeReader
{
    private string $path;
    private Filesystem $filesystem;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->filesystem = new Filesystem();
    }

    public function loadRouteAttributes()
    {
        $files = $this->filesystem->find($this->path, '*.php');

        $result = [];
        foreach ($files as $file) {
            if ($class = $this->findClass((string) $file)) {
                /*
                $refl = new \ReflectionClass($class);
                if ($refl->isAbstract()) {
                    //return null;
                    continue;
                }*/

                $result = array_merge($result, $this->load($class));
            }

        }

        //gc_mem_caches();

        return $result;

    }



    /**
     * Returns the full class name for the first class in the file.
     */
    // https://github.com/symfony/symfony/blob/6.1/src/Symfony/Component/Routing/Loader/AnnotationFileLoader.php#L76
    // TODO : ajouter des tests (cf ->load() method dans la classe des tests) !!! https://github.com/symfony/symfony/blob/6.1/src/Symfony/Component/Routing/Tests/Loader/AnnotationFileLoaderTest.php
    private function findClass(string $file): string|false
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));

        if (1 === \count($tokens) && \T_INLINE_HTML === $tokens[0][0]) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not contain PHP code. Did you forgot to add the "<?php" start tag at the beginning of the file?', $file));
        }

        $nsTokens = [\T_NS_SEPARATOR => true, \T_STRING => true];
        // TODO : je pense que comme on est en php8 on peut se passer de la verification sur "defined" car cette constante est intégrée à PHP8 !!!!
        if (\defined('T_NAME_QUALIFIED')) {
            $nsTokens[\T_NAME_QUALIFIED] = true;
        }
        for ($i = 0; isset($tokens[$i]); ++$i) {
            $token = $tokens[$i];
            if (!isset($token[1])) {
                continue;
            }

            if (true === $class && \T_STRING === $token[0]) {
                return $namespace.'\\'.$token[1];
            }

            if (true === $namespace && isset($nsTokens[$token[0]])) {
                $namespace = $token[1];
                while (isset($tokens[++$i][1], $nsTokens[$tokens[$i][0]])) {
                    $namespace .= $tokens[$i][1];
                }
                $token = $tokens[$i];
            }

            if (\T_CLASS === $token[0]) {
                // Skip usage of ::class constant and anonymous classes
                $skipClassToken = false;
                for ($j = $i - 1; $j > 0; --$j) {
                    if (!isset($tokens[$j][1])) {
                        if ('(' === $tokens[$j] || ',' === $tokens[$j]) {
                            $skipClassToken = true;
                        }
                        break;
                    }

                    if (\T_DOUBLE_COLON === $tokens[$j][0] || \T_NEW === $tokens[$j][0]) {
                        $skipClassToken = true;
                        break;
                    } elseif (!\in_array($tokens[$j][0], [\T_WHITESPACE, \T_DOC_COMMENT, \T_COMMENT])) {
                        break;
                    }
                }

                if (!$skipClassToken) {
                    $class = true;
                }
            }

            if (\T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return false;
    }


    /**
     * Loads from annotations from a class.
     *
     * @param string $class A class name
     *
     * @return RouteCollection
     *
     * @throws \InvalidArgumentException When route can't be parsed
     */
    private function load(string $class)
    {
        /*
        if (!class_exists($class)) {
            //throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
            return;
        }*/

        try {
            $class = $this->classReflection($class);
        } catch (\LogicException $e) {
            //Ignoring
            //continue;

            return [];
        }

        if ($class->isAbstract()) {
            //throw new \InvalidArgumentException(sprintf('Annotations from class "%s" cannot be read as it is abstract.', $class->getName()));
            return [];
        }


        $result = [];



        foreach ($class->getMethods() as $method) {

            /*
            // ignore non-available methods...
            if ($method->isStatic() ||
                $method->isPrivate() ||
                $method->isProtected()) {
                continue;
            }
            */

            foreach ($this->getAnnotations($method) as $annot) {

/*
                $action = $method->getName() === '__invoke'
                    ? $class->getName()
                    : [$class->getName(), $method->getName()];
*/

                //$this->addRoute($collection, $annot, $globals, $class, $method);
                $result[] = $annot;
            }
        }

/*
        if (0 === $collection->count() && $class->hasMethod('__invoke')) {
            foreach ($this->getAnnotations($class) as $annot) {
                //$this->addRoute($collection, $annot, $globals, $class, $class->getMethod('__invoke'));
            }
        }
*/
        //return $collection;

        return $result;
    }


    /**
     * Safely get class reflection, class loading errors will be blocked and reflection will be
     * excluded from analysis.
     *
     * @template T
     * @param class-string<T> $class
     * @return \ReflectionClass<T>
     *
     * @throws \LogicException
     */
    // TODO : ajouter des tests !!! https://github.com/spiral/tokenizer/blob/15121a76bc9452e795ee171de47890c4e1a59a2f/tests/ClassLocatorTest.php#L39
    private function classReflection(string $class): \ReflectionClass
    {
        $loader = static function ($class) {
            // TODO : je pense que ce if ne sert à rien !!!!
            if ($class === \LogicException::class) {
                return;
            }

            throw new \LogicException("Class '{$class}' can not be loaded");
        };

        //To suspend class dependency exception
        spl_autoload_register($loader);

        try {
            //In some cases reflection can thrown an exception if class invalid or can not be loaded,
            //we are going to handle such exception and convert it soft exception
            return new \ReflectionClass($class);
        } catch (\Throwable $e) {
            if ($e instanceof \LogicException && $e->getPrevious() != null) {
                $e = $e->getPrevious();
            }

/*
            $this->getLogger()->error(
                sprintf(
                    '%s: %s in %s:%s',
                    $class,
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ),
                ['error' => $e]
            );
*/
            throw new \LogicException($e->getMessage(), $e->getCode(), $e);
        } finally {
            spl_autoload_unregister($loader);
        }
    }


    /**
     * @param \ReflectionClass|\ReflectionMethod $reflection
     *
     * @return iterable<int, RouteAttribute>
     */
    private function getAnnotations(object $reflection): iterable
    {
        foreach ($reflection->getAttributes(RouteAttribute::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            yield $attribute->newInstance();
        }
    }


}
