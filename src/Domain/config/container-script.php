<?php

use ZnDatabase\Fixture\Domain\Repositories\FileRepository;

return [
    'definitions' => [],
    'singletons' => [
        FileRepository::class => function () {
            return new FileRepository($_ENV['ELOQUENT_CONFIG_FILE']);
        },
    ],
];
