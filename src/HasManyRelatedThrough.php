<?php

namespace MMedia\LaravelCollaborativeFiltering;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @extends \Illuminate\Database\Eloquent\Relations\Relation<TRelatedModel>
 */
class HasManyRelatedThrough extends HasManyThrough
{

    protected string $usingColumn;

    public function setUsingColumn(string $value)
    {
        $this->usingColumn = $value;
    }

    public function __construct(Builder $query, Model $farParent, Model $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey, $usingColumn)
    {
        $this->setUsingColumn($usingColumn);
        parent::__construct($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
    }

    /**
     * Set the select clause for the relation query.
     *
     * @param  array  $columns
     * @return array
     */
    protected function shouldSelect(array $columns = ['*'])
    {
        if ($columns == ['*']) {
            $columns = [$this->related->getTable() . '.*'];
        }

        return array_merge(
            $columns,
            [$this->getQualifiedFirstKeyName() . ' as laravel_through_key'],
            [DB::raw("COUNT(" . $this->throughParent->getTable() . "." . $this->usingColumn . ") as score")]
        );
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        $localValue = $this->farParent[$this->localKey];

        $viaTable = $this->throughParent->getTable();

        $usingColumn = $this->usingColumn;

        $farForeignKey = $this->getSecondLocalKeyName();

        $this->performJoin();

        if (static::$constraints) {
            $this->query
                // ->where($this->getQualifiedFirstKeyName(), '=', $localValue)
                ->whereIn($viaTable . "." . $usingColumn, function ($query) use (
                    $viaTable,
                    $usingColumn,
                    $farForeignKey,
                    $localValue
                ) {
                    $query->from($viaTable)
                        ->select($usingColumn)
                        ->where(
                            $farForeignKey,
                            "=",
                            $localValue
                        );
                })
                ->where(
                    $viaTable . "." . $farForeignKey,
                    "<>",
                    $localValue
                )
                ->groupBy(
                    // These are the actual group by columns that are important for this query
                    $viaTable . "." . $farForeignKey,

                    // These fields are here because needed by mysql full group by
                    ...$this->currentTableColumns(),
                );
        }
    }

    private function currentTableColumns()
    {
        $table = $this->farParent->getTable();
        $columns = $this->getConnection()->getSchemaBuilder()->getColumnListing($table);

        // Convert each $column to fullyQualified name
        $columns = array_map(function ($column) use ($table) {
            return $table . "." . $column;
        }, $columns);

        return $columns;
    }
}
