<?php

namespace Chiron\Attributes\Test\Fixtures\ActionPathController;

use Chiron\Attributes\Attribute\RouteAttribute as Route;

class ActionPathController
{
    #[Route('/path', name: 'action')]
    public function action()
    {
    }
}
