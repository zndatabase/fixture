# Фикстуры

Пример фикстуры:

```php
use ZnLib\Db\Fixture\Libs\FixtureGenerator;

$fixture = new FixtureGenerator;
$fixture->setCount(200);
$fixture->setCallback(function ($index, FixtureGenerator $fixtureFactory) {
    return [
        'id' => $index,
        'title' => 'post ' . $index,
        'category_id' => $fixtureFactory->ordIndex($index, 3),
        'created_at' => '2019-11-05 20:23:00',
    ];
});
return $fixture->generateCollection();
```

Либо в виде простого массива:

```php
return [
    [
        'id' => 1,
        'title' => 'post 1',
        'category_id' => 1,
        'created_at' => '2019-11-05 20:23:00',
    ],
    [
        'id' => 2,
        'title' => 'post 2',
        'category_id' => 1,
        'created_at' => '2019-11-05 20:23:00',
    ],
];
```

## Консоль

Переходим в папку `bin`:

    cd vendor/znlib/fixture/bin

Импорт фикстур:

    php console db:fixture:import

Экспорт фикстур:

    php console db:fixture:export
