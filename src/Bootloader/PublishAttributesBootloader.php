<?php

namespace Chiron\Attributes\Bootloader;

use Chiron\Core\Directories;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Publisher\Publisher;

final class PublishAttributesBootloader extends AbstractBootloader
{
    public function boot(Publisher $publisher, Directories $directories): void
    {
        // copy the configuration file template from the package "config" folder to the user "config" folder.
        $publisher->add(__DIR__ . '/../../config/attributes.php.dist', $directories->get('@config/attributes.php'));
    }
}
