<?php

namespace ZnDatabase\Fixture\Domain\Libs;

class DataFixture implements FixtureInterface
{

    private $data;
    private $deps;

    public function __construct($data = [], array $deps = [])
    {
        $this->data = $data;
        $this->deps = $deps;
    }

    public function load() {
        return $this->data;
    }

    public function unload() {

    }

    public function deps() {
        return $this->deps;
    }
}
