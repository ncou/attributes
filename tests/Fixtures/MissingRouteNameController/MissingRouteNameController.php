<?php

namespace Chiron\Attributes\Test\Fixtures\MissingRouteNameController;

use Chiron\Attributes\Attribute\RouteAttribute as Route;

class MissingRouteNameController
{
    #[Route('/path')]
    public function action()
    {
    }
}
