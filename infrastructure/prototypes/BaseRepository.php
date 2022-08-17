<?php

namespace app\infrastructure\prototypes;

use app\core\spi\BaseModelRepositorySPI;
use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\db\Query;
use yii\log\Logger;

/**
 * Base class for domain repositories.
 * @template T of yii\base\Model
 * @implements BaseModelRepositorySPI<Model>
 */
abstract class BaseRepository implements BaseModelRepositorySPI
{
    /** @var string $domainClass Domain model class */
    private readonly string $domainClass;

    public function __construct()
    {
        $this->domainClass = $this->domainClass();
    }

    /**
     * Returns domain class for this repository.
     *
     * @return string
     */
    abstract protected function domainClass(): string;

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function find(?array $conditions = null, int $page = 0, int $pageSize = 0): array
    {
        $query = $this->createSelectQuery($page, $pageSize)
            ->where($conditions);

        $recordsData = $query
            ->all();

        $list = [];
        foreach ($recordsData as $data) {
            $list[] = $this->createModel($data);
        }

        return $list;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function findOne(array $conditions): ?Model
    {
        $modelData = $this->createSelectQuery()
            ->where($conditions)
            ->one();

        $result = null;
        if ($modelData !== false) {
            $result = $this->createModel($modelData);
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @param Model $model
     * @throws \Exception
     */
    public function save(mixed $model): bool
    {
        $data = $this->getModelAttributes($model);

        $insert = true;

        if (!empty($data['id']) && $this->existsById($data['id'])) {
            $insert = false;
        }

        if ($insert) {
            unset($data['id']);
            $dbCommand = Yii::$app->db
                ->createCommand()
                ->insert($this->tableName(), $data);
        } else {
            $dbCommand = Yii::$app->db
                ->createCommand()
                ->update($this->tableName(), $data, ['id' => $model->id]);
        }

        try {
            $dbCommand->execute();
            if (empty($data['id'])) {
                $model->id = Yii::$app->db->lastInsertID;
            }
            return true;
        } catch (\Exception $e) {
            Yii::getLogger()->log($e, Logger::LEVEL_ERROR);
            return false;
        }
    }

    /**
     * @inheritDoc
     * @param Model $model
     * @throws Exception
     */
    public function delete(mixed $model): bool
    {
        $affectedCount = Yii::$app->db
            ->createCommand()
            ->delete($this->tableName(), ['id' => $model->id])
            ->execute();

        return $affectedCount > 0;
    }

    /**
     * Creates select query for current model
     *
     * @param int $page
     * @param int $pageSize
     * @return Query
     */
    protected function createSelectQuery(int $page = 0, int $pageSize = 0): Query
    {
        $query = (new Query())->from($this->tableName());

        if ($pageSize > 0) {
            $query->limit($pageSize)->offset($pageSize * $page);
        }

        return $query;
    }

    /**
     * Creates new model instance from given data
     *
     * @param array $args
     * @return Model
     * @throws \Exception
     */
    protected function createModel(...$args): Model
    {
        $data = &$args[0];
        $this->mapSpecialFields($data, false);

        return new $this->domainClass(...$args);
    }

    /**
     * Returns table name for this repository.
     *
     * @return string
     */
    abstract public function tableName(): string;

    /**
     * @param array $data
     * @param bool $toDatabase
     * @return void
     * @throws \Exception
     */
    private function mapSpecialFields(array &$data, bool $toDatabase): void
    {
        foreach ($this->specialFieldTypesForMapping() as $fieldName => $type) {
            if ($toDatabase) {
                $data[$fieldName] = $this->mapSpecialTypeToDatabase($type, $data[$fieldName]);
            } else {
                $data[$fieldName] = $this->mapSpecialTypeFromDatabase($type, $data[$fieldName]);
            }
        }
    }

    /**
     * Returns a map of fields to one of special types.
     * Specified fields will undergo data mapping according to the specified type.
     * For example, 'json' type fields will be encoded and decoded when saving to database
     * and will be a mapper (model)array<->string(database)
     *
     * @return array
     */
    protected function specialFieldTypesForMapping(): array
    {
        return [];
    }

    /**
     * @param mixed $type
     * @param mixed $value
     * @return mixed
     * @throws \Exception
     */
    private function mapSpecialTypeToDatabase(string $type, mixed $value): mixed
    {
        return match ($type) {
            'json' => json_encode($value),
            default => throw new \Exception('Unsupported special type: ' . $type)
        };
    }

    /**
     * @param mixed $type
     * @param mixed $data
     * @return mixed
     * @throws \Exception
     */
    private function mapSpecialTypeFromDatabase(string $type, mixed $data): mixed
    {
        return match ($type) {
            'json' => json_decode($data, true),
            default => throw new \Exception('Unsupported special type: ' . $type)
        };
    }

    /**
     * @param T $model
     * @return array
     * @throws \Exception
     */
    protected function getModelAttributes(mixed $model): array
    {
        $data = $model->getAttributes(null, ['created_at', 'updated_at']);
        $this->mapSpecialFields($data, true);

        return $data;
    }

    /**
     * Checks existence of record with given id
     *
     * @param int $id
     * @return bool
     */
    protected function existsById(int $id): bool
    {
        return $this->createSelectQuery()->where(['id' => $id])->exists();
    }
}
