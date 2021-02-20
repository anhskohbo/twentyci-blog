<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Builder;

abstract class AbstractRepository
{
    /**
     * @var array
     */
    protected $with = [];

    /**
     * @var array
     */
    protected $search = [];

    /**
     * Build an "index" query.
     *
     * @param Builder $query
     * @param string $search
     * @param array $filters
     * @param array $orderings
     * @return Builder
     */
    public function buildIndexQuery(
        Builder $query,
        string $search = null,
        array $filters = [],
        array $orderings = []
    ) {
        $query = empty(trim($search))
            ? $query
            : $this->applySearch($query, $search);

        $query = $this->applyFilters(
            $this->applyOrderings($query, $orderings),
            $filters
        );

        if ($this->with) {
            $query->with($this->with);
        }

        return $query;
    }

    /**
     * Apply the search query to the query.
     *
     * @param Builder $query
     * @param string $search
     * @return Builder
     */
    protected function applySearch(Builder $query, $search)
    {
        return $query->where(
            function ($query) use ($search) {
                $model = $query->getModel();

                $connectionType = $query->getModel()->getConnection()->getDriverName();

                $canSearchPrimaryKey = is_numeric($search) &&
                                       in_array($query->getModel()->getKeyType(), ['int', 'integer']) &&
                                       ($connectionType !== 'pgsql' || $search <= PHP_INT_MAX) &&
                                       in_array($query->getModel()->getKeyName(), $this->search, true);

                if ($canSearchPrimaryKey) {
                    $query->orWhere($query->getModel()->getQualifiedKeyName(), $search);
                }

                $likeOperator = $connectionType === 'pgsql' ? 'ilike' : 'like';

                foreach ($this->search as $column) {
                    $query->orWhere($model->qualifyColumn($column), $likeOperator, '%' . $search . '%');
                }
            }
        );
    }

    /**
     * Apply any applicable filters to the query.
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    protected function applyFilters(Builder $query, array $filters)
    {
        collect($filters)->each(
            function ($filter) use ($query) {
                $filter($query);
            }
        );

        return $query;
    }

    /**
     * Apply any applicable orderings to the query.
     *
     * @param Builder $query
     * @param array $orderings
     * @return Builder
     */
    protected function applyOrderings(Builder $query, array $orderings)
    {
        $orderings = array_filter($orderings);

        if (empty($orderings)) {
            return empty($query->getQuery()->orders)
                ? $query->latest($query->getModel()->getQualifiedKeyName())
                : $query;
        }

        foreach ($orderings as $column => $direction) {
            if (!in_array(strtolower($direction), ['asc', 'desc'], true)) {
                continue;
            }

            $query->orderBy($column, $direction);
        }

        return $query;
    }
}
