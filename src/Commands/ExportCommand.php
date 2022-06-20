<?php

namespace ZnDatabase\Fixture\Commands;

use Illuminate\Support\Collection;
use ZnLib\Console\Symfony4\Widgets\LogWidget;
use ZnCore\Base\Libs\Entity\Helpers\EntityHelper;
use ZnDatabase\Fixture\Domain\Entities\FixtureEntity;
use ZnLib\Console\Symfony4\Question\ChoiceQuestion;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends BaseCommand
{
    protected static $defaultName = 'db:fixture:export';

    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Export fixture data to files')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<fg=white># Fixture EXPORT</>');

        /** @var FixtureEntity[]|Collection $tableCollection */
        $tableCollection = $this->fixtureService->allFixtures();
        //dd($tableCollection->toArray());

        if ($tableCollection->count() == 0) {
            $output->writeln('');
            $output->writeln('<fg=magenta>No tables in database!</>');
            $output->writeln('');
            return 0;
        }

        $withConfirm = $input->getOption('withConfirm');

        $tableNameArray = EntityHelper::getColumn($tableCollection, 'name');
        if ($withConfirm) {
            $output->writeln('');
            $question = new ChoiceQuestion(
                'Select tables for export',
                $tableNameArray,
                'a'
            );
            $question->setMultiselect(true);
            $selectedTables = $this->getHelper('question')->ask($input, $output, $question);
        } else {
            $selectedTables = $tableNameArray;
        }

        $output->writeln('');

        $logWidget = new LogWidget($output);
        $logWidget->setPretty(true);
        $logWidget->setLineLength(40);
        foreach ($selectedTables as $tableName) {
            $logWidget->start(' ' . $tableName);
            $this->fixtureService->exportTable($tableName);
            $logWidget->finishSuccess();
        }

        $output->writeln(['', '<fg=green>Fixture EXPORT success!</>', '']);
        return 0;
    }

}
