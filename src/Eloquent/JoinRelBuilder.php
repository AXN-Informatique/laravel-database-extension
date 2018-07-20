<?php

namespace Axn\Illuminate\Database\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;

class JoinRelBuilder
{
    /**
     * Nom du trait utilisé par le modèle pour gérer les soft deletes.
     */
    const SOFT_DELETES_TRAIT = 'Illuminate\Database\Eloquent\SoftDeletes';

    /**
     * Instance du modèle.
     *
     * @var Model
     */
    protected $model;

    /**
     * Instance du Query Builder.
     *
     * @var QueryBuilder
     */
    protected $query;

    /**
     * Liste des modèles liés à la table (incluant celui de la table dans le "from").
     *
     * @var array[Model]
     */
    protected $relatedModels = [];

    /**
     * Constructeur.
     *
     * @param Model $model
     * @param QueryBuilder $query
     */
    public function __construct(Model $model, QueryBuilder $query)
    {
        $this->model = $model;
        $this->query = $query;
    }

    /**
     * Retourne l'instance d'un modèle lié.
     *
     * @param  string $alias
     * @return Model|null
     */
    public function getRelatedModel($alias)
    {
        if (!isset($this->relatedModels[$alias])) {
            return null;
        }

        return $this->relatedModels[$alias];
    }

    /**
     * Applique une clause "join" en utilisant la relation définie sur le modèle,
     * à l'instar de la méthode with().
     *
     * @param  string        $relationName
     * @param  string|null   $alias
     * @param  \Closure|null $callback
     * @param  string        $type
     * @param  boolean       $withTrashed
     * @return Builder
     */
    public function apply($relationName, $alias = null, $callback = null, $type = 'inner', $withTrashed = false)
    {
        if ($alias instanceof \Closure) {
            $callback = $alias;
            $alias = null;
        }

        $this->parseRelationName($relationName, $parentAlias);
        $alias = $alias ?: $relationName;

        if (isset($this->relatedModels[$alias])) {
            return;
        }

        $relation = Relation::noConstraints(function () use ($parentAlias, $relationName) {
            return $this->relatedModels[$parentAlias]->{$relationName}();
        });

        $this->relatedModels[$alias] = $relation->getRelated();

        $table = $relation->getRelated()->getTable()." as $alias";
        $relation->getRelated()->setTable($alias);
        $relation->getParent()->setTable($parentAlias);

        $cond = $this->buildCondition($relation, $callback, $withTrashed);

        $this->query->join($table, $cond, null, null, $type);
    }

    /**
     * Analyse le nom de la relation pour en extraire l'alias de la table sur laquelle
     * effectuer la jointure.
     *
     * @param  string &$relationName
     * @param  string &$parentAlias
     * @return void
     */
    protected function parseRelationName(&$relationName, &$parentAlias)
    {
        if (strpos($relationName, '.') !== false) {
            list($parentAlias, $relationName) = explode('.', $relationName);
        } else {
            $parentAlias = $this->model->getTable();

            if ($parentAlias && !isset($this->relatedModels[$parentAlias])) {
                $this->relatedModels[$parentAlias] = $this->model;
            }
        }
    }

    /**
     * Construit la condition de jointure en fonction de la relation.
     *
     * Supporte : HasOne, HasMany, MorphOne, MorphMany, BelongsTo
     *
     * @param  Relation      $relation
     * @param  \Closure|null $callback
     * @param  boolean       $withTrashed
     * @return \Closure
     */
    protected function buildCondition(Relation $relation, $callback, $withTrashed)
    {
        return (function($join) use ($relation, $callback, $withTrashed) {
            list($localKey, $foreignKey) = $this->getRelationKeys($relation);

            $join->on($localKey, '=', $foreignKey);

            if ($relation instanceof MorphOneOrMany) {
                $morphType = $relation->getRelated()->getTable().'.'.$relation->getMorphType();
                $join->where($morphType, '=', $relation->getMorphClass());
            }

            if (!$withTrashed && in_array(static::SOFT_DELETES_TRAIT, class_uses($relation->getRelated()))) {
                $join->whereNull($relation->getRelated()->getQualifiedDeletedAtColumn());
            }

            $this->addRelationWheres(
                $join,
                $relation->getBaseQuery()->wheres,
                $relation->getRelated()->getTable()
            );

            if ($callback instanceof \Closure) {
                $callback($join);
            }
        });
    }

    /**
     * Retourne les clés (local et foreign) de la relation.
     *
     * @param  Relation $relation
     * @return array
     */
    protected function getRelationKeys(Relation $relation)
    {
        // HasOneOrMany (comprend aussi MorphOneOrMany)
        if ($relation instanceof HasOneOrMany) {
            return [
                $relation->getParent()->getQualifiedKeyName(),
                $relation->getRelated()->getTable().'.'.$relation->getForeignKeyName()
            ];
        }
        // BelongsTo
        elseif ($relation instanceof BelongsTo) {
            return [
                $relation->getRelated()->getQualifiedKeyName(),
                $relation->getParent()->getTable().'.'.$relation->getForeignKey()
            ];
        }

        // Relation non supportée...
        throw new \Exception(get_class($relation).' relation not supported for making join.');
    }

    /**
     * Ajoute à la jointure les WHERE additionnels définis dans la relation.
     *
     * @param  JoinClause $join
     * @param  array      $wheres
     * @param  string     $alias
     * @return void
     */
    protected function addRelationWheres(JoinClause $join, array $wheres, $alias)
    {
        foreach ($wheres as $where) {
            if ($where['type'] === 'Nested') {
                $join->where(function($join) use ($where, $alias) {
                    $this->addRelationWheres($join, $where['query']->wheres, $alias);
                });
            } else {
                $join->where(
                    $alias.'.'.$where['column'],
                    $where['operator'],
                    $where['value'],
                    $where['boolean']
                );
            }
        }
    }
}
