<?php

namespace ZnDatabase\Fixture\Domain\Libs;

interface FixtureInterface
{

    public function load();
    public function unload();
    public function deps();

}
