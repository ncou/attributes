<?php

namespace Chiron\Attributes\Test\Classes\NotLoaded;

trait AnonymousClassInTrait
{
    public function test()
    {
        return new class() {
            public function foo()
            {
            }
        };
    }
}
