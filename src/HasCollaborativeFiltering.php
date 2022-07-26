<?php

namespace MMedia\LaravelCollaborativeFiltering;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait HasCollaborativeFiltering
{
    /**
     * Simple collaborative  filtering.
     *
     * @see https://arctype.com/blog/collaborative-filtering-tutorial/ - Thanks arctype!
     *
     * @deprecated - use the relationship hasManyRelatedThrough
     * @param string $viaTable The table to join to.
     * @param string $usingColumn The column that determines the shared relationship - this is the column that will be used to find models that are related.
     * @param \Closure|null $callback - Optional callback to modify the query. Use it to add further with() constraints.
     * @return Collection
     */
    public function getRelatedModels(string $viaTable, string $usingColumn, ?Closure $callback = null)
    {
        $viaTable = $this->newRelatedInstance($viaTable)->getTable();
        $finalQuery =
            $this->query()
            ->join(
                $viaTable,
                $viaTable . "." . $this->getForeignKey(),
                "=",
                $this->getTable() . "." . $this->primaryKey
            );

        $finalQuery = $this->performRelatedSelect($finalQuery, $viaTable, $usingColumn);

        // Call any callback if provided.
        if ($callback instanceof Closure && $callback !== null) {
            $finalQuery = $callback($finalQuery);
        }

        return $finalQuery->get();
    }

    private function currentTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    private function performRelatedSelect($query, $viaTable, $usingColumn)
    {
        return $query
            ->select($this->getTable() . ".*", DB::raw("COUNT(" . $viaTable . "." . $usingColumn . ") as score"))
            ->whereIn($viaTable . "." . $usingColumn, function ($query) use (
                $viaTable,
                $usingColumn
            ) {
                $query->from($viaTable)
                    ->select($usingColumn)
                    ->where(
                        $this->getForeignKey(),
                        "=",
                        $this->{$this->primaryKey}
                    );
            })
            ->where(
                $viaTable . "." . $this->getForeignKey(),
                "<>",
                $this->{$this->primaryKey}
            )
            ->orderBy("score", "desc")
            ->groupBy(
                // These are the actual group by columns that are important for this query
                $viaTable . "." . $this->getForeignKey(),

                // These fields are here because needed by mysql full group by
                ...$this->currentTableColumns(),
            )
            ->withCount($viaTable);
    }

    /**
     * Define a has-many-through relationship.
     *
     * @param  string  $through
     * @param  string  $usingColumn
     * @param  string|null  $firstKey
     * @param  string|null  $secondKey
     * @param  string|null  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function hasManyRelatedThrough($through, $usingColumn, $firstKey = null, $secondKey = null, $localKey = null)
    {
        $through = new $through;

        $firstKey = $firstKey ?: $this->getForeignKey();

        $secondKey = $secondKey ?: $through->getForeignKey() ?? $through->primaryKey;

        return $this->newHasManyRelated(
            $this->newRelatedInstance($this)->newQuery(),
            $through,
            $firstKey,
            $secondKey,
            $localKey ?: $this->getKeyName(),
            $usingColumn
        );
    }

    /**
     * Instantiate a new HasManyThrough relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $farParent
     * @param  \Illuminate\Database\Eloquent\Model  $throughParent
     * @param  string  $firstKey
     * @param  string  $secondKey
     * @param  string  $localKey
     * @param  string  $secondLocalKey
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    protected function newHasManyRelated(Builder $query, Model $throughParent, $firstKey, $secondKey, $localKey, $usingColumn)
    {
        $farParent = $this;
        $secondLocalKey = $this->getForeignKey();
        return new HasManyRelatedThrough($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey, $usingColumn);
    }
}
