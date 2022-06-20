<?php

namespace ZnDatabase\Fixture\Domain\Repositories;

use Illuminate\Support\Collection;
use ZnCore\Base\Exceptions\InvalidConfigException;
use ZnCore\Base\Helpers\ClassHelper;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Base\Libs\FileSystem\Helpers\FilePathHelper;
use ZnCore\Base\Libs\FileSystem\Helpers\FindFileHelper;
use ZnCore\Base\Libs\Store\Helpers\StoreHelper;
use ZnCore\Base\Libs\Store\StoreFile;
use ZnCore\Base\Libs\Entity\Helpers\EntityHelper;
use ZnCore\Domain\Interfaces\GetEntityClassInterface;
use ZnCore\Domain\Interfaces\Repository\RepositoryInterface;
use ZnDatabase\Base\Domain\Entities\RelationEntity;
use ZnDatabase\Fixture\Domain\Entities\FixtureEntity;
use ZnDatabase\Fixture\Domain\Libs\DataFixture;
use ZnDatabase\Fixture\Domain\Libs\FixtureInterface;
use ZnSandbox\Sandbox\Generator\Domain\Services\GeneratorService;

class FileRepository implements RepositoryInterface, GetEntityClassInterface
{

    public $extension = 'php';

    public function __construct($mainConfigFile = null)
    {
        $config = StoreHelper::load($_ENV['ROOT_DIRECTORY'] . '/' . $mainConfigFile);
//        $config = LoadHelper::loadConfig($mainConfigFile);
        $this->config = $config['fixture'];
        /*if(empty($this->config)) {
            throw new InvalidConfigException('Empty fixture configuration!');
        }*/
    }

    public function getEntityClass(): string
    {
        return FixtureEntity::class;
    }

    public function allTables(): Collection
    {
        $array = [];
        if (empty($this->config['directory'])) {
            throw new InvalidConfigException('Empty directories configuration for fixtures!');
        }
        foreach ($this->config['directory'] as $dir) {
            $fixtureArray = $this->scanDir(FilePathHelper::prepareRootPath($dir));
            foreach ($fixtureArray as $i => $item) {
//                dd($item);
                if (FilePathHelper::fileExt($item['fileName']) != 'php') {
                    unset($fixtureArray[$i]);
                }
            }
            $array = ArrayHelper::merge($array, $fixtureArray);
        }
        //$collection = $this->forgeEntityCollection($array);
        //return $collection;

        $entityClass = $this->getEntityClass();
        return EntityHelper::createEntityCollection($entityClass, $array);
    }

    private function getRelations(string $name): array
    {
        /** @var GeneratorService $generatorService */
        $generatorService = ClassHelper::createObject(GeneratorService::class);
        $struct = $generatorService->getStructure([$name]);
        $deps = [];
        /** @var RelationEntity $relationEntity */
        foreach ($struct[0]->getRelations() as $relationEntity) {
            $deps[] = $relationEntity->getForeignTableName();
        }
        return $deps;
    }

    public function saveData($name, Collection $collection)
    {
        $dataFixture = $this->loadData($name);
        $data['deps'] = $dataFixture->deps();
        $data['deps'] = array_merge($data['deps'], $this->getRelations($name));
        ArrayHelper::removeValue($data['deps'], $name);
        $data['deps'] = array_unique($data['deps']);
        $data['deps'] = array_values($data['deps']);

        if (property_exists($collection->first(), 'id')) {
            $collection = $collection->sortBy('id');
        }
        $data['collection'] = ArrayHelper::toArray($collection->toArray());
        $data['collection'] = array_values($data['collection']);
        $this->getStoreInstance($name)->save($data);
    }

    public function loadData($name): FixtureInterface
    {
        $data = $this->getStoreInstance($name)->load();
        if (empty($data)) {
            return new DataFixture([], []);
        } elseif (ArrayHelper::isAssociative($data)) {
            return new DataFixture($data['collection'], $data['deps'] ?? []);
        } elseif ($data instanceof FixtureInterface) {
            return $data;
        } elseif (ArrayHelper::isIndexed($data)) {
            return new DataFixture($data);
        }

        //dd($data);
        throw new \Exception('Bad fixture format of ' . $name . '!');
    }

    private function oneByName(string $name): FixtureEntity
    {
        $collection = $this->allTables();
        $collection = $collection->where('name', '=', $name);
        if ($collection->count() < 1) {

            $entityClass = $this->getEntityClass();
            return EntityHelper::createEntity($entityClass, [
                'name' => $name,
                'fileName' => $this->config['directory']['default'] . '/' . $name . '.' . $this->extension,
            ]);

            //return $this->forgeEntity();
        }

        $entityClass = $this->getEntityClass();
        return EntityHelper::createEntity($entityClass, $collection->first());
    }

    private function getStoreInstance(string $name): StoreFile
    {
        $entity = $this->oneByName($name);
        $store = new StoreFile($entity->fileName);
        return $store;
    }

    private function scanDir($dir): array
    {
        $files = FindFileHelper::scanDir($dir);
        $array = [];
        foreach ($files as $file) {
            $name = FilePathHelper::fileRemoveExt($file);
            $entity = [
                'name' => $name,
                'fileName' => $dir . '/' . $file,
            ];
            $array[] = $entity;
        }
        return $array;
    }

}