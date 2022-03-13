<?php

declare(strict_types=1);

namespace Chiron\Attributes\Bootloader;

use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Console\Console;
use Chiron\Attributes\Command\RouteClearCommand;

final class RouteClearCommandBootloader extends AbstractBootloader
{
    public function boot(Console $console): void
    {
        $console->addCommand(RouteClearCommand::getDefaultName(), RouteClearCommand::class);
    }
}
