<?php

namespace ZnDatabase\Fixture\Domain\Helpers;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use ZnCore\Base\Libs\Arr\Helpers\ArrayHelper;
use ZnDatabase\Base\Domain\Entities\SchemaEntity;
use ZnDatabase\Base\Domain\Entities\TableEntity;

class StructHelper
{
    
    public static function getTableNameFromEntity(TableEntity $tableEntity): string {
        $tableName = '';
        if($tableEntity->getSchema() && $tableEntity->getSchema()->getName() != 'public') {
            $tableName .= $tableEntity->getSchema()->getName() . '.';
        }
        $tableName .=$tableEntity->getName();
        return $tableName;
    }
    
    /**
     * @param Builder $schema
     * @return Collection | TableEntity[]
     */
    public static function allTables(Builder $schema): Collection
    {
        $dbName = $schema->getConnection()->getDatabaseName();
        $array = $schema->getAllTables();
        $key = 'Tables_in_' . $dbName;
        $array = ArrayHelper::getColumn($array, $key);
        $tableCollection = new Collection;
        foreach ($array as $tableName) {
            $tableEntity = new TableEntity;
            $tableEntity->setName($tableName);
            //$tableEntity->setSchema($schemaEntity);
            $tableCollection->add($tableEntity);
        }
        return $tableCollection;
    }

    /**
     * @param ConnectionInterface $connection
     * @return Collection | TableEntity[]
     */
    public static function allPostgresTables(ConnectionInterface $connection): Collection
    {
        $schemaCollection = self::allPostgresSchemas($connection);
        $tableCollection = new Collection;
        foreach ($schemaCollection as $schemaEntity) {
            $tables = $connection->select("SELECT * FROM information_schema.tables WHERE table_schema = '{$schemaEntity->getName()}'");
            // select * from pg_tables where schemaname='public';
            $tableNames = ArrayHelper::getColumn($tables, 'table_name');
            foreach ($tableNames as $tableName) {
                $tableEntity = new TableEntity;
                $tableEntity->setName($tableName);
                $tableEntity->setSchema($schemaEntity);
                $tableCollection->add($tableEntity);
            }
        }
        return $tableCollection;
    }

    /**
     * @param ConnectionInterface $connection
     * @return Collection | SchemaEntity[]
     */
    public static function allPostgresSchemas(ConnectionInterface $connection): Collection
    {
        $schemaCollection = $connection->select("select schema_name from information_schema.schemata;");
        $schemaNames = ArrayHelper::getColumn($schemaCollection, 'schema_name');
        $excludes = [
            "pg_toast",
            "pg_temp_1",
            "pg_toast_temp_1",
            "pg_catalog",
            "information_schema",
        ];
        $schemaNames = array_diff($schemaNames, $excludes);
        /** @var SchemaEntity[] | Collection $collection */
        $collection = new Collection;
        foreach ($schemaNames as $schemaName) {
            $entity = new SchemaEntity;
            $entity->setName($schemaName);
            $entity->setDbName($connection->getConfig('database'));
            $collection->add($entity);
        }
        return $collection;
    }

}