<?php

namespace Chiron\Attributes\Test\Fixtures\MethodsAndSchemes;

use Chiron\Attributes\Attribute\RouteAttribute as Route;

final class MethodsAndSchemes
{
    #[Route(path: '/array-one', name: 'array_one', methods: ['GET'], scheme: 'http')]
    public function arrayOne(): void
    {
    }

    #[Route(path: '/string', name: 'string', methods: 'POST', scheme: 'https')]
    public function string(): void
    {
    }
}
