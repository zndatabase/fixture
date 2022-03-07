<?php

namespace ZnDatabase\Fixture\Commands;

use ZnDatabase\Fixture\Domain\Services\FixtureService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

abstract class BaseCommand extends Command
{

    protected $fixtureService;

    public function __construct(?string $name = null, FixtureService $fixtureService)
    {
        parent::__construct($name);
        $this->fixtureService = $fixtureService;
    }

    protected function configure()
    {
        $this
            ->addOption(
                'withConfirm',
                null,
                InputOption::VALUE_REQUIRED,
                'Your selection migrations',
                true
            );
    }
}
