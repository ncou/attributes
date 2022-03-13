<?php

declare(strict_types=1);

namespace Chiron\Attributes\Config;

use Chiron\Config\AbstractInjectableConfig;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Closure;

final class AttributesConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'attributes';

    protected function getConfigSchema(): Schema
    {
        return Expect::structure([
            'locator_enabled' => Expect::bool()->default(true),
            'controller_directory' => Expect::string()->default('@app/Controllers'),
            'use_cache' => Expect::bool()->default(env('ROUTE_CACHE', !env('DEBUG'))),
        ]);
    }

    public function isLocatorEnabled(): bool
    {
        return $this->get('locator_enabled');
    }

    public function getControllerDirectory(): string
    {
        return $this->get('controller_directory');
    }

    public function getUseCache(): bool
    {
        return $this->get('use_cache');
    }

}
