<?php

namespace Axn\Illuminate\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder as BaseEloquentBuilder;

class Builder extends BaseEloquentBuilder
{
    /**
     * Indique si l'ordre par défaut ne doit pas être appliqué sur la requête.
     *
     * @var boolean
     */
    protected $dontApplyDefaultOrderBy = false;

    /**
     * Instance JoinRelBuilder pour construire les clause JOIN à partir des relations
     * définies sur les modèles.
     *
     * @var JoinRelBuilder
     */
    protected $joinRelBuilder;

    /**
     * Désactive l'ordre par défaut.
     *
     * @return void
     */
    public function disableDefaultOrderBy()
    {
        $this->dontApplyDefaultOrderBy = true;
    }

    /**
     * Get the hydrated models without eager loading.
     *
     * @param  array  $columns
     * @return array|static[]
     */
    public function getModels($columns = ['*'])
    {
        $this->applyDefaultOrderBy();

        return parent::getModels($columns);
    }

    /**
     * Modifie l'alias de la table utilisée dans la clause "from".
     *
     * @param  string $alias
     * @return Builder
     */
    public function alias($alias)
    {
        $table = $this->model->getTable();
        $this->model->setTable($alias);

        $query = $this->model->newQuery();
        $query->from("$table as $alias");

        return $query;
    }

    // ------------------------------------------------------------------------
    // JOIN USING RELATIONSHIPS
    // ------------------------------------------------------------------------

    /**
     * Effectue une jointure en utilisant la relation définie sur le modèle,
     * à l'instar de la méthode with().
     *
     * @param  string        $relationName
     * @param  string|null   $alias
     * @param  \Closure|null $callback
     * @return Builder
     */
    public function joinRel($relationName, $alias = null, $callback = null)
    {
        $this->getJoinRelBuilder()->apply($relationName, $alias, $callback);

        return $this;
    }

    /**
     * Idem méthode joinRel() mais en incluant les enregistrements supprimés.
     *
     * @param  string        $relationName
     * @param  string|null   $alias
     * @param  \Closure|null $callback
     * @return Builder
     */
    public function joinRelWithTrashed($relationName, $alias = null, $callback = null)
    {
        $this->getJoinRelBuilder()->apply($relationName, $alias, $callback, 'inner', true);

        return $this;
    }

    /**
     * Idem méthode joinRel() mais en type LEFT.
     *
     * @param  string        $relationName
     * @param  string|null   $alias
     * @param  \Closure|null $callback
     * @return Builder
     */
    public function leftJoinRel($relationName, $alias = null, $callback = null)
    {
        $this->getJoinRelBuilder()->apply($relationName, $alias, $callback, 'left');

        return $this;
    }

    /**
     * Idem méthode leftJoinRel() mais en incluant les enregistrements supprimés.
     *
     * @param  string        $relationName
     * @param  string|null   $alias
     * @param  \Closure|null $callback
     * @return Builder
     */
    public function leftJoinRelWithTrashed($relationName, $alias = null, $callback = null)
    {
        $this->getJoinRelBuilder()->apply($relationName, $alias, $callback, 'left', true);

        return $this;
    }

    /**
     * Idem méthode joinRel() mais en type RIGHT.
     *
     * @param  string        $relationName
     * @param  string|null   $alias
     * @param  \Closure|null $callback
     * @return Builder
     */
    public function rightJoinRel($relationName, $alias = null, $callback = null)
    {
        $this->getJoinRelBuilder()->apply($relationName, $alias, $callback, 'right');

        return $this;
    }

    /**
     * Idem méthode rightJoinRel() mais en incluant les enregistrements supprimés.
     *
     * @param  string        $relationName
     * @param  string        $alias
     * @param  \Closure|null $wheres
     * @return Builder
     */
    public function rightJoinRelWithTrashed($relationName, $alias = null, $callback = null)
    {
        $this->getJoinRelBuilder()->apply($relationName, $alias, $callback, 'right', true);

        return $this;
    }

    /**
     * Ajoute les mises à jour des champs "updated_at" des tables pour lesquelles
     * il y a modification de champs.
     *
     * @param  array $values
     * @return array
     */
    protected function addUpdatedAtColumn(array $values)
    {
        $added = false;

        foreach ($values as $column => $value) {
            if (!strpos($column, '.')) {
                continue;
            }

            list($alias, $column) = explode('.', $column);
            $relatedModel = $this->getJoinRelBuilder()->getRelatedModel($alias);

            if (isset($relatedModel) && $relatedModel->usesTimestamps()) {
                $updatedAtColumn = "$alias.".$relatedModel->getUpdatedAtColumn();

                if (!array_key_exists($updatedAtColumn, $values)) {
                    $values[$updatedAtColumn] = $this->model->freshTimestampString();
                    $added = true;
                }
            }
        }

        return $added ? $values : parent::addUpdatedAtColumn($values);
    }

    // ------------------------------------------------------------------------
    // HELPERS
    // ------------------------------------------------------------------------

    /**
     * Crée et retourne l'instance du JoinRel Builder.
     *
     * @return JoinRelBuilder
     */
    protected function getJoinRelBuilder()
    {
        if (!isset($this->joinRelBuilder)) {
            $this->joinRelBuilder = new JoinRelBuilder($this->model, $this->query);
        }

        return $this->joinRelBuilder;
    }

    /**
	 * Applique l'ordre par défaut si défini et qu'aucun autre ordre n'a été spécifié.
     *
     * https://github.com/innoscience/eloquental/blob/master/src/Query/Builder.php
	 *
	 * @return void
	 */
	protected function applyDefaultOrderBy()
    {
		if ($this->dontApplyDefaultOrderBy || !$this->model->getOrderBy() || $this->query->orders) {
            return;
        }

        foreach ((array) $this->model->getOrderBy() as $column => $option) {
            if (is_int($column)) {
                $this->query->orderBy($this->model->getTable().'.'.$option);
            }
            elseif ($option == 'natural' || $option == 'natural_asc') {
                $this->query->orderByNatural($this->model->getTable().'.'.$column);
            }
            elseif ($option == 'natural_desc') {
                $this->query->orderByNatural($this->model->getTable().'.'.$column, 'desc');
            }
            else {
                $this->query->orderBy($this->model->getTable().'.'.$column, $option);
            }
        }
    }
}
