<?php

namespace ZnDatabase\Fixture\Domain\Repositories;

use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\MySqlBuilder;
use Illuminate\Database\Schema\PostgresBuilder;
use ZnCore\Arr\Helpers\ArrayHelper;
use ZnCore\Collection\Interfaces\Enumerable;
use ZnCore\Collection\Libs\Collection;
use ZnCore\Collection\Helpers\CollectionHelper;
use ZnDomain\Entity\Helpers\EntityHelper;
use ZnDomain\EntityManager\Interfaces\EntityManagerInterface;
use ZnDatabase\Base\Domain\Enums\DbDriverEnum;
use ZnDatabase\Base\Domain\Traits\TableNameTrait;
use ZnDatabase\Eloquent\Domain\Capsule\Manager;
use ZnDatabase\Eloquent\Domain\Traits\EloquentTrait;
use ZnDatabase\Fixture\Domain\Helpers\StructHelper;

class DbRepository //extends BaseEloquentRepository
{

    use TableNameTrait;
    use EloquentTrait;

    //use EntityManagerAwareTrait;

//    private $capsule;

    public function __construct(EntityManagerInterface $em, Manager $capsule)
    {
        $this->setCapsule($capsule);
        //$this->setEntityManager($em);
        $schema = $this->getSchema();
        // Выключаем проверку целостности связей
        $schema->disableForeignKeyConstraints();
    }

    public function connectionName()
    {
        return 'default';
//        return $this->capsule->getConnectionNameByTableName($this->tableName());
    }

    /*public function getEntityClass(): string
    {
        return FixtureEntity::class;
    }

    public function tableName(): string
    {
        return '';
    }*/

    /*public function __construct(EntityManagerInterface $em, Manager $capsule)
    {
        parent::__construct($em, $capsule);

        $schema = $this->getSchema();

        // Выключаем проверку целостности связей
        $schema->disableForeignKeyConstraints();
    }*/

    public function dropAllTables()
    {
        $this
            ->getSchema()
            ->dropAllTables();
    }

    public function dropAllViews()
    {
        $this
            ->getSchema()
            ->dropAllViews();
    }

    public function dropAllTypes()
    {
        $this
            ->getSchema()
            ->dropAllTypes();
    }

    public function deleteTable($name)
    {
//        $tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $this->encodeTableName($name);
        $this
            ->getSchema()
            ->drop($targetTableName);
    }

    public function truncateData($name)
    {
//        $tableAlias = $this->getCapsule()->getAlias();
//        $targetTableName = $this->encodeTableName($name);
//        $connection = $this->getCapsule()->getConnectionByTableName($name);
//        $queryBuilder = $connection->table($targetTableName);

        $queryBuilder = $this->getQueryBuilderByTableName($name);
        $queryBuilder->truncate();
    }

    public function isHasTable($name)
    {
//        $tableAlias = $this->getCapsule()->getAlias();
//        $targetTableName = $this->encodeTableName($name);

        $targetTableName = $this->encodeTableName($name);
        $connection = $this->getCapsule()->getConnectionByTableName($name);
        return $connection->getSchemaBuilder()->hasTable($targetTableName);
    }

    /*public function getQueryBuilderByTableName($name): \Illuminate\Database\Query\Builder
    {
        $tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $this->encodeTableName($name);
        $connection = $this->getCapsule()->getConnectionByTableName($name);
        $queryBuilder = $connection->table($targetTableName);
        return $queryBuilder;
    }*/

    public function saveData($name, Enumerable $collection)
    {
        /*$tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $this->encodeTableName($name);
        $connection = $this->getCapsule()->getConnectionByTableName($name);
        $queryBuilder = $connection->table($targetTableName);*/
        $queryBuilder = $this->getQueryBuilderByTableName($name);
        //$queryBuilder->truncate();

        $chunks = CollectionHelper::chunk($collection, 150);

//        $chunks = $collection->chunk(150);
        foreach ($chunks as $chunk) {
            $data = ArrayHelper::toArray($chunk);
            $queryBuilder->insert($data);
        }
        $this->resetAutoIncrement($name);
    }

    public function loadData($name): Enumerable
    {
        /*$tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $this->encodeTableName($name);
        $connection = $this->getCapsule()->getConnectionByTableName($name);
        $queryBuilder = $connection->table($targetTableName);*/
        $queryBuilder = $this->getQueryBuilderByTableName($name);
        $data = $queryBuilder->get()->toArray();
        return new Collection($data);
    }

    public function allTables(): Enumerable
    {
        $tableAlias = $this->getCapsule()->getAlias();
        /* @var Builder|MySqlBuilder|PostgresBuilder $schema */
        $schema = $this->getSchema();


        //dd($this->getCapsule()->getDatabaseManager());
        $dbName = $schema->getConnection()->getDatabaseName();
        $collection = new Collection();
        if ($schema->getConnection()->getDriverName() == DbDriverEnum::SQLITE) {
            $array = $schema->getConnection()->getPdo()->query('SELECT name FROM sqlite_master WHERE type=\'table\'')->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($array as $targetTableName) {
                $sourceTableName = $tableAlias->decode('default', $targetTableName);
                $entityClass = $this->getEntityClass();
                $entity = EntityHelper::createEntity($entityClass, [
                    'name' => $sourceTableName,
                ]);
                $collection->add($entity);
            }
        } else {
            if ($schema->getConnection()->getDriverName() == DbDriverEnum::PGSQL) {
                $tableCollection = StructHelper::allPostgresTables($schema->getConnection());
            } else {
                $tableCollection = StructHelper::allTables($schema);
            }
            foreach ($tableCollection as $tableEntity) {
                $tableName = StructHelper::getTableNameFromEntity($tableEntity);
                $array[] = $tableAlias->decode('default', $tableName);
            }
            foreach ($array as $targetTableName) {
                //$key = 'Tables_in_' . $dbName;
                //$targetTableName = $item->{$key};
                $sourceTableName = $tableAlias->decode('default', $targetTableName);
                $entityClass = $this->getEntityClass();
                $entity = EntityHelper::createEntity($entityClass, [
                    'name' => $sourceTableName,
                ]);
                $collection->add($entity);
            }
        }
        return $collection;
    }

    public function resetAutoIncrement($name)
    {
//        $tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $this->encodeTableName($name);

        /*$tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $this->encodeTableName($name);
        $connection = $this->getCapsule()->getConnectionByTableName($name);
        $queryBuilder = $connection->table($targetTableName);*/
        $queryBuilder = $this->getQueryBuilderByTableName($name);
        $connection = $queryBuilder->getConnection();
        $driver = $connection->getConfig('driver');

        /* @var Builder|MySqlBuilder|PostgresBuilder $schema */
        $schema = $this->getSchema();

        if ($driver == DbDriverEnum::PGSQL && $schema->hasColumn($name, 'id')) {
            $max = $queryBuilder->max('id');
            if ($max) {
                $pkName = 'id';
                $sql = 'SELECT setval(\'' . $targetTableName . '_' . $pkName . '_seq\', ' . ($max) . ')';
                $connection = $queryBuilder->getConnection();
                $connection->statement($sql);
            }
        }
    }

}