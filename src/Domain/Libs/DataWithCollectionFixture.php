<?php

namespace ZnDatabase\Fixture\Domain\Libs;

use ZnDatabase\Fixture\Domain\Libs\FixtureGenerator;

abstract class DataWithCollectionFixture extends DataFixture
{

    abstract public function count(): int;

    abstract public function collection(): array;

    abstract public function callback($index, FixtureGenerator $fixtureFactory): array;

    public function load()
    {
        $collection = $this->collection();
        $fixture = new FixtureGenerator;
        $fixture->setCount($this->count());
        $fixture->setStartIndex(count($collection) + 1);
        $fixture->setCallback([$this, 'callback']);
        return array_merge($collection, $fixture->generateCollection());
    }
}
