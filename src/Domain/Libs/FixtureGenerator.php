<?php

namespace ZnDatabase\Fixture\Domain\Libs;

use Closure;

class FixtureGenerator
{

    private $startIndex = 1;
    private $step = 1;
    private $count;
    private $callback;

    public function getStartIndex(): int
    {
        return $this->startIndex;
    }

    public function setStartIndex(int $startIndex): void
    {
        $this->startIndex = $startIndex;
    }

    public function getStep(): int
    {
        return $this->step;
    }

    public function setStep(int $step): void
    {
        $this->step = $step;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function setCallback($callback): void
    {
        $this->callback = $callback;
    }

    /*public function __construct($count = 10)
    {
        $this->count = $count;
    }*/

    public function ordIndex($index, $count)
    {
        return ($index + $count - 1) % $count + 1;
    }

    public function generateCollection()
    {
        $collection = [];
        for ($index = $this->startIndex; $index <= $this->count; $index = $index + $this->step) {
            $item = $this->generateItem($index);
            if($item) {
                $collection[] = $item;
            }
        }
        return $collection;
    }

    public function generateItem($index)
    {
        return call_user_func_array($this->callback, [$index, $this]);
    }

}