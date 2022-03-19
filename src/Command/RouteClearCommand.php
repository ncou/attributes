<?php

declare(strict_types=1);

namespace Chiron\Attributes\Command;

use Chiron\Core\Command\AbstractCommand;
use Chiron\Core\Memory;
use Chiron\Support\Random;
use Symfony\Component\Console\Input\InputOption;
use Chiron\Attributes\RouteLocator;

final class RouteClearCommand extends AbstractCommand
{
    protected static $defaultName = 'route:clear';

    protected function configure()
    {
        $this->setDescription('Clear the cache file route attributes.');
    }

    protected function perform(Memory $memory): int
    {
        // TODO : voir comment gérer le cas ou on lance plusieurs fois cette commande et donc que le cache est déjà cleared (ou qu'on a désactivé l'utilisation du cache donc on aura jamais de fichier à nettoyer !!!!), on affiche un warning si $memory->exists() est à false ?
        // TODO : voir si le clear peut renvoyer un booléen et si le booléen est false alors lever un warning/error pour afficher que le cache n'est pas cleared.
        $memory->clear(RouteLocator::MEMORY_SECTION);

        $this->info("Route cache file cleared!");

        return self::SUCCESS;
    }
}
