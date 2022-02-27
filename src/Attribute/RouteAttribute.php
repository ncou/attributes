<?php

declare(strict_types = 1);

namespace Chiron\Attributes\Attribute;

// TODO : passer les attributs de la classe en public pour éviter d'avoir des setters/getters qui ne servent à rien !!!

// TODO : initialiser par défaut la methode de la route à "GET" ???? https://github.com/sunrise-php/http-router/blob/master/src/Annotation/Route.php#L157
// TODO : rajouter une propriété de classe + setter/getter pour $holder qui sera soit un string soit un tableau (pour stocker le futur handler de la classe sous forme "classname" si la méthode est __invoke() sinon un tableau ["classname", "methodname"]) !!!

// TODO : on doit vraiment permettre un attribut IS_REPEATABLE ??? et TARGET_CLASS ????

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class RouteAttribute
{
    private ?string $path = null;
    private array $methods;
    private array $schemes;
    private ?string $name = null;
    private array $requirements = [];
    private array $options = [];
    private array $defaults = []; // TODO : à virer !!!
    private ?string $host = null;
    private ?string $condition = null;
    private ?int $priority = null;
    private ?string $env = null;

    /**
     * @param string[]        $requirements
     * @param string[]|string $methods
     * @param string[]|string $schemes
     */
    // TODO : phpdoc à compléter !!!
    public function __construct(
        ?string $path = null,
        ?string $name = null,
        array $requirements = [],
        array $options = [],
        array $defaults = [],
        ?string $host = null,
        array|string $methods = [],
        array|string $schemes = [],
        ?string $condition = null,
        ?int $priority = null,
        ?string $locale = null,
        ?string $format = null,
        ?bool $utf8 = null,
        ?bool $stateless = null,
        ?string $env = null
    ) {
        $this->path = $path; // TODO : attention je ne pense pas que le path puisse $etre null !!!!
        $this->name = $name;
        $this->requirements = $requirements;
        $this->options = $options;
        $this->defaults = $defaults;
        $this->host = $host;
        $this->condition = $condition;
        $this->priority = $priority;
        $this->env = $env;

        $this->setMethods($methods);
        $this->setSchemes($schemes);

        if (null !== $locale) {
            $this->defaults['_locale'] = $locale;
        }

        if (null !== $format) {
            $this->defaults['_format'] = $format;
        }

        if (null !== $utf8) {
            $this->options['utf8'] = $utf8;
        }

        if (null !== $stateless) {
            $this->defaults['_stateless'] = $stateless;
        }
    }

    public function setPath(string $path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setHost(string $pattern)
    {
        $this->host = $pattern;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setRequirements(array $requirements)
    {
        $this->requirements = $requirements;
    }

    public function getRequirements()
    {
        return $this->requirements;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;
    }

    public function getDefaults()
    {
        return $this->defaults;
    }

    public function setSchemes(array|string $schemes)
    {
        $this->schemes = (array) $schemes;
    }

    public function getSchemes()
    {
        return $this->schemes;
    }

    public function setMethods(array|string $methods)
    {
        $this->methods = (array) $methods;
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function setCondition(?string $condition)
    {
        $this->condition = $condition;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setEnv(?string $env): void
    {
        $this->env = $env;
    }

    public function getEnv(): ?string
    {
        return $this->env;
    }
}
