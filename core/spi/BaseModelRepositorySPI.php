<?php

namespace app\core\spi;

use yii\base\Model;

/**
 * Base SPI for model repositories
 *
 * @template T
 */
interface BaseModelRepositorySPI
{
    /**
     * Returns all instances matching given conditions
     *
     * @param array|null $conditions Search conditions
     * @param int $page Result page to return
     * @param int $pageSize Returned page size (if 0 - pagination is not used)
     * @return T[]
     */
    public function find(?array $conditions = null, int $page = 0, int $pageSize = 0): array;

    /**
     * Return first instance matching given conditions
     *
     * @param array $conditions Search conditions
     * @return T|null
     */
    public function findOne(array $conditions): ?Model;

    /**
     * Adds or updates model in the repository
     * @param T $model
     * @return bool
     */
    public function save(mixed $model): bool;

    /**
     * Deletes given model from the repository
     *
     * @param T $model
     * @return bool
     */
    public function delete(mixed $model): bool;
}
