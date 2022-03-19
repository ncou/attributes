<?php

namespace Chiron\Attributes\Test\Fixtures\MethodActionControllers;

use Chiron\Attributes\Attribute\RouteAttribute as Route;

class MethodActionControllers
{
    #[Route(path: '/the/path/post', name: 'post', methods: ['POST'])]
    public function post()
    {
    }

    #[Route(path: '/the/path/put', name: 'put', methods: ['PUT'])]
    public function put()
    {
    }
}
