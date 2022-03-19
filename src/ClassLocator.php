<?php

declare(strict_types = 1);

namespace Chiron\Attributes;

use Chiron\Filesystem\Filesystem;

// TODO : transformer cette classe en trait ???? ou alors en classe et virer la notion de static sur les méthodes !!!!
final class ClassLocator
{
    public static function locate(string $directory): array
    {
        $filesystem = new Filesystem();
        $files = $filesystem->find($directory, '*.php'); // TODO : il faudra surement lever une ImproperlyConfiguredException si le répertoire n'existe pas !!!! ou si on passe directement le nom d'un fichier (car ce n'est pas le fonctionnement attendu !!!!).

        $files = iterator_to_array($files);
        usort($files, function (\SplFileInfo $a, \SplFileInfo $b) {
            return (string) $a > (string) $b ? 1 : -1;
        });

        $classes = [];
        foreach ($files as $file) {
            if ($class = static::findClass((string) $file)) {
                try {
                    $reflection = new \ReflectionClass($class);
                } catch (\Throwable $e) {
                    continue;
                }

                $classes[] = $reflection;
            }
        }

        return $classes;
    }

    /**
     * Returns the full class name for the first class in the file.
     */
    // https://github.com/symfony/symfony/blob/6.1/src/Symfony/Component/Routing/Loader/AnnotationFileLoader.php#L76
    // TODO : ajouter des tests (cf ->load() method dans la classe des tests) !!! https://github.com/symfony/symfony/blob/6.1/src/Symfony/Component/Routing/Tests/Loader/AnnotationFileLoaderTest.php
    private static function findClass(string $file): string|false
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));

        $nsTokens = [
            \T_NS_SEPARATOR => true,
            \T_STRING => true,
            \T_NAME_QUALIFIED => true
        ];

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
}
