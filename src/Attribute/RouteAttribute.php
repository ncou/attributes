<?php

declare(strict_types = 1);

namespace Chiron\Attributes\Attribute;

use Chiron\Http\Message\RequestMethod as Method;

//Et voici un exemple pour hydrater une classe (trouvé dans le code de cakephp) :
/**
     * Set state magic method to support var_export
     *
     * This method helps for applications that want to implement
     * router caching.
     *
     * @param array<string, mixed> $fields Key/Value of object attributes
     * @return static A new instance of the route
     */
/*
    public static function __set_state(array $fields)
    {
        $class = static::class;
        $obj = new $class('');
        foreach ($fields as $field => $value) {
            $obj->$field = $value;
        }

        return $obj;
    }*/

// TODO : passer les attributs de la classe en public pour éviter d'avoir des setters/getters qui ne servent à rien !!!

// TODO : initialiser par défaut la methode de la route à "GET" ???? https://github.com/sunrise-php/http-router/blob/master/src/Annotation/Route.php#L157
// TODO : rajouter une propriété de classe + setter/getter pour $holder qui sera soit un string soit un tableau (pour stocker le futur handler de la classe sous forme "classname" si la méthode est __invoke() sinon un tableau ["classname", "methodname"]) !!!

// TODO : on doit vraiment permettre un attribut IS_REPEATABLE ??? et TARGET_CLASS ????

//https://github.com/symfony/routing/blob/6.1/Annotation/Route.php

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class RouteAttribute
{
    // TODO : bien vérifier le comportement pour les middlewares !!!!
    public string $path;
    public ?int $port = null;
    public ?string $scheme = null;
    public ?string $host = null;
    public ?string $name = null;
    public array|string $methods = [];
    public array $defaults = [];
    public array $requirements = [];
    public array $middlewares = [];

    // TODO : vérifier si les "defaults" / "requirements" / "middlewares" sont bien des tableaux de string !!! si ce n'est pas le cas il faut changer le phpdoc
    /**
     * Constructor of the class
     *
     * @param  string          $path
     * @param  int|null        $port
     * @param  string|null     $scheme
     * @param  string|null     $host
     * @param  string          $name
     * @param  string[]|string $methods
     * @param  string[]        $defaults
     * @param  string[]        $requirements
     * @param  string[]        $middlewares
     */
    public function __construct(
        string $path,
        ?int $port = null,
        ?string $scheme = null,
        ?string $host = null,
        ?string $name = null,
        array|string $methods = [],
        array $defaults = [],
        array $requirements = [],
        array $middlewares = []
    ) {
        $this->path = $path;
        $this->port = $port;
        $this->scheme = $scheme;
        $this->host = $host;
        $this->name = $name;
        $this->methods = (array) $methods;
        $this->defaults = $defaults;
        $this->requirements = $requirements;
        $this->middlewares = $middlewares;
    }
}
