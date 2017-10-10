<?php

/**
 * Factory which generates entities with repository
 */
class FactoryRepository {

    /**
     * Return an entity with from repository
     * @param  [string] $repository [Repository name]
     * @param  [int] $id         [Entity's id]
     * @return [object]             [Entity]
     */
    public static function generateEntityFromRepository($repository, $id) {
        return $repository::getInstance()->getById($id);
    }

}
