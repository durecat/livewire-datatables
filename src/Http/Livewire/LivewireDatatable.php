<?php

namespace Mediconesystems\LivewireDatatables\Http\Livewire;

use Exception;
use Livewire\Component;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Query\Expression;
use Mediconesystems\LivewireDatatables\ColumnSet;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Mediconesystems\LivewireDatatables\Traits\WithColumnFilters;
use Mediconesystems\LivewireDatatables\Traits\WithCallbacks;
use Mediconesystems\LivewireDatatables\Exports\DatatableExport;
use Mediconesystems\LivewireDatatables\Traits\WithCreateOrEditAction;
use Mediconesystems\LivewireDatatables\Traits\WithDeleteAction;
use Mediconesystems\LivewireDatatables\Traits\WithPresetDateFilters;
use Mediconesystems\LivewireDatatables\Traits\WithPresetTimeFilters;

class LivewireDatatable extends Component
{
    use WithPagination, WithColumnFilters, WithCallbacks, WithPresetDateFilters, WithPresetTimeFilters, WithCreateOrEditAction, WithDeleteAction;

    const SEPARATOR = '|**lwdt**|';
    public $model;
    public $columns;
    public $search;
    public $sort;
    public $direction;
    public $hideHeader;
    public $hidePagination;
    public $perPage;
    public $include;
    public $exclude;
    public $hide;
    public $dates;
    public $times;
    public $searchable;
    public $exportable;
    public $hideable;
    public $params;
    public $selected = [];
    public $actions = []; // create, edit, delete    
    public $table;
    public $beforeTableSlot;
    public $afterTableSlot;
    public $addtionalSearch;
    public $customizeCreateForm;

    protected $query;
    protected $listeners = ['refreshLivewireDatatable'];
    protected $queryString = ['search'];

    public function mount(
        $model = null,
        $include = [],
        $exclude = [],
        $hide = [],
        $dates = [],
        $times = [],
        $searchable = [],
        $sort = null,
        $hideHeader = null,
        $hidePagination = null,
        $perPage = 10,
        $exportable = false,
        $hideable = false,
        $actions = [],
        $beforeTableSlot = false,
        $afterTableSlot = false,
        $addtionalSearch = false,
        $customizeCreateForm = false,
        $params = []
    ) {
        foreach (['model', 'include', 'exclude', 'hide', 'dates', 'times', 'searchable', 'sort', 'hideHeader', 'hidePagination', 'perPage', 'exportable','hideable', 'actions', 'beforeTableSlot', 'afterTableSlot', 'addtionalSearch', 'customizeCreateForm'] as $property) {
            $this->$property = $this->$property ?? $$property;
        }
        $this->params = $params;
        
        $this->table = $this->builder()->getModel()->getTable();
        
        $this->setExtraProperties();

        $this->columns = $this->getViewColumns();

        $this->initialiseSort();
    }

    public function setExtraProperties(){}

    public function columns()
    {
        return $this->modelInstance;
    }

    public function getViewColumns()
    {
        return collect($this->freshColumns)->map(function ($column) {
            $columns = ['hidden', 'label', 'align', 'type', 'input', 'filterable', 'filterview', 'name', 'class'];

            if (in_array($column['input'], ['select', 'checkbox'])) {
                array_push($columns, 'options');
            }

            return collect($column)->only($columns)->toArray();
        })->toArray();
    }

    public function getModelInstanceProperty()
    {
        return $this->model::firstOrFail();
    }

    public function builder()
    {
        return $this->model::query();
    }

    public function getProcessedColumnsProperty()
    {
        return ColumnSet::build($this->columns())
            ->include($this->include)
            ->exclude($this->exclude)
            ->hide($this->hide)
            ->formatDates($this->dates)
            ->formatTimes($this->times)
            ->search($this->searchable)
            ->sort($this->sort);
    }

    public function resolveColumnName($column)
    {
        return $column->isBaseColumn()
            ? $this->query->getModel()->getTable().'.'.($column->base ?? Str::before($column->name, ':'))
            : $column->select ?? $this->resolveRelationColumn($column->base ?? $column->name, $column->aggregate);
    }

    public function resolveCheckboxColumnName($column)
    {
        $column = is_object($column)
            ? $column->toArray()
            : $column;

        return Str::contains($column['base'], '.')
            ? $this->resolveRelationColumn($column['base'], $column['aggregate'])
            : $this->query->getModel()->getTable().'.'.$column['base'];
    }

    public function resolveAdditionalSelects($column)
    {
        $selects = collect($column->additionalSelects)->map(function ($select) {
            return Str::contains($select, '.')
                ? $this->resolveRelationColumn($select, Str::contains($select, ':') ? Str::before($select, ':') : null)
                : $this->query->getModel()->getTable().'.'.$select;
        });

        return $selects->count() > 1
            ? new Expression('CONCAT_WS("'.static::SEPARATOR.'" ,'.
                collect($selects)->map(function ($select) {
                    return "COALESCE($select, '')";
                })->join(', ').')')
            : $selects->first();
    }

    public function resolveEditableColumnName($column)
    {
        return [
            $column->select,
            $this->query->getModel()->getTable().'.'.$this->query->getModel()->getKeyName(),
        ];
    }

    public function getSelectStatements($withAlias = false)
    {
        return $this->processedColumns->columns->reject(function ($column) {
            return $column->scope;
        })->map(function ($column) {
            if ($column->select) {
                return $column;
            }

            if ($column->isType('checkbox')) {
                $column->select = $this->resolveCheckboxColumnName($column);

                return $column;
            }

            if (Str::startsWith($column->name, 'callback_')) {
                $column->select = $this->resolveAdditionalSelects($column);

                return $column;
            }

            $column->select = $this->resolveColumnName($column);

            if ($column->isEditable()) {
                $column->select = $this->resolveEditableColumnName($column);
            }

            return $column;
        })->when($withAlias, function ($columns) {
            return $columns->map(function ($column) {
                if (! $column->select) {
                    return null;
                }
                if ($column->select instanceof Expression) {
                    return new Expression($column->select->getValue().' AS `'.$column->name.'`');
                }

                if (is_array($column->select)) {
                    $selects = $column->select;
                    $first = array_shift($selects).' AS '.$column->name;
                    $others = array_map(function ($select) {
                        return $select.' AS '.$select;
                    }, $selects);

                    return array_merge([$first], $others);
                }

                return $column->select.' AS '.$column->name;
            });
        }, function ($columns) {
            return $columns->map->select;
        });
    }

    protected function resolveRelationColumn($name, $aggregate = null)
    {
        $parts = explode('.', Str::before($name, ':'));
        $columnName = array_pop($parts);
        $relation = implode('.', $parts);

        return  method_exists($this->query->getModel(), $parts[0])
            ? $this->joinRelation($relation, $columnName, $aggregate, $name)
            : $name;
    }

    protected function joinRelation($relation, $relationColumn, $aggregate = null, $alias = null)
    {
        $table = '';
        $model = '';
        $lastQuery = $this->query;
        foreach (explode('.', $relation) as $eachRelation) {
            $model = $lastQuery->getRelation($eachRelation);

            switch (true) {
                case $model instanceof HasOne:
                    $table = $model->getRelated()->getTable();
                    $foreign = $model->getQualifiedForeignKeyName();
                    $other = $model->getQualifiedParentKeyName();
                    break;

                case $model instanceof HasMany:
                    $this->query->customWithAggregate($relation, $aggregate ?? 'count', $relationColumn, $alias);
                    $table = null;
                    break;

                case $model instanceof BelongsTo:
                    $table = $model->getRelated()->getTable();
                    $foreign = $model->getQualifiedForeignKeyName();
                    $other = $model->getQualifiedOwnerKeyName();
                    break;

                case $model instanceof BelongsToMany:
                    $this->query->customWithAggregate($relation, $aggregate ?? 'count', $relationColumn, $alias);
                    $table = null;
                    break;

                case $model instanceof HasOneThrough:
                    $pivot = explode('.', $model->getQualifiedParentKeyName())[0];
                    $pivotPK = $model->getQualifiedFirstKeyName();
                    $pivotFK = $model->getQualifiedLocalKeyName();
                    $this->performJoin($pivot, $pivotPK, $pivotFK);

                    $related = $model->getRelated();
                    $table = $related->getTable();
                    $tablePK = $related->getForeignKey();
                    $foreign = $pivot.'.'.$tablePK;
                    $other = $related->getQualifiedKeyName();

                    break;

                default:
                    $this->query->customWithAggregate($relation, $aggregate ?? 'count', $relationColumn, $alias);
            }
            if ($table) {
                $this->performJoin($table, $foreign, $other);
            }
            $lastQuery = $model->getQuery();
        }

        if ($model instanceof HasOne || $model instanceof BelongsTo || $model instanceof HasOneThrough) {
            return $table.'.'.$relationColumn;
        }

        if ($model instanceof HasMany) {
            return;
        }

        if ($model instanceof BelongsToMany) {
            return;
        }
    }

    protected function performJoin($table, $foreign, $other, $type = 'left')
    {
        $joins = [];
        foreach ((array) $this->query->getQuery()->joins as $key => $join) {
            $joins[] = $join->table;
        }

        if (! in_array($table, $joins)) {
            $this->query->join($table, $foreign, '=', $other, $type);
        }
    }

    public function getFreshColumnsProperty()
    {
        $columns = $this->processedColumns->columnsArray();

        if (($name = collect($columns)->pluck('name')->duplicates()) && collect($columns)->pluck('name')->duplicates()->count()) {
            throw new Exception('Duplicate Column Name: '.$name->first());
        }

        return $columns;
    }

    public function initialiseSort()
    {
        $this->sort = $this->defaultSort()
            ? $this->defaultSort()['key']
            : collect($this->freshColumns)->reject(function ($column) {
                return $column['type'] === 'checkbox' || $column['hidden'];
            })->keys()->first();
        $this->direction = $this->defaultSort() && $this->defaultSort()['direction'] === 'asc';
    }

    public function defaultSort()
    {
        $columnIndex = collect($this->freshColumns)->search(function ($column) {
            return is_string($column['defaultSort']);
        });

        return is_numeric($columnIndex) ? [
            'key' => $columnIndex,
            'direction' => $this->freshColumns[$columnIndex]['defaultSort'],
        ] : null;
    }

    public function getSortString()
    {
        $column = $this->freshColumns[$this->sort];
        $dbTable = env('DB_CONNECTION');

        switch (true) {
            case $column['sort']:
                return $column['sort'];
                break;

            case $column['base']:
                return $column['base'];
                break;

            case is_array($column['select']):
                return Str::before($column['select'][0], ' AS ');
                break;

            case $column['select']:
                return Str::before($column['select'], ' AS ');
                break;

             default:
                return $dbTable == 'pgsql'
                ? new Expression('"'.$column['name'].'"')
                : new Expression('`'.$column['name'].'`');
                break;
        }
    }

    public function updatingPerPage()
    {
        $this->refreshLivewireDatatable();
    }

    public function refreshLivewireDatatable()
    {
        $this->page = 1;
    }

    public function sort($index)
    {
        if ($this->sort === (int) $index) {
            $this->direction = ! $this->direction;
        } else {
            $this->sort = (int) $index;
        }
        $this->page = 1;
    }

    public function toggle($index)
    {
        if ($this->sort == $index) {
            $this->initialiseSort();
        }

        if (! $this->columns[$index]['hidden']) {
            unset($this->activeSelectFilters[$index]);
        }

        $this->columns[$index]['hidden'] = ! $this->columns[$index]['hidden'];
    }

    public function searchableColumns()
    {
        return collect($this->freshColumns)->filter(function ($column, $key) {
            return $column['searchable'];
        });
    }

    public function scopeColumns()
    {
        return collect($this->freshColumns)->filter(function ($column, $key) {
            return isset($column['scope']);
        });
    }

    public function getHeaderProperty()
    {
        return method_exists(static::class, 'header');
    }

    public function getShowHideProperty()
    {
        return $this->showHide() ?? $this->showHide;
    }

    public function getPaginationControlsProperty()
    {
        return $this->paginationControls() ?? $this->paginationControls;
    }

    public function getResultsProperty()
    {
        return $this->mapCallbacks(
            $this->getQuery()->paginate($this->perPage)
        );
    }

    public function columnIsRelation($column)
    {
        return Str::contains($column['name'], '.') && method_exists($this->builder()->getModel(), Str::before($column['name'], '.'));
    }

    public function columnIsAggregateRelation($column)
    {
        if (! $this->columnIsRelation($column)) {
            return;
        }
        $relation = $this->builder()->getRelation(Str::before($column['name'], '.'));

        return /* $relation instanceof HasOne || */ $relation instanceof HasManyThrough || $relation instanceof HasMany || $relation instanceof belongsToMany;
    }

    public function columnAggregateType($column)
    {
        return $column['type'] === 'string'
            ? 'group_concat'
            : 'count';
    }

    public function buildDatabaseQuery()
    {
        $this->query = $this->builder();
        $this->query->addSelect($this->getSelectStatements(true)->filter()->flatten()->toArray());

        $this->addGlobalSearch()
            ->addScopeColumns()
            ->addSelectFilters()
            ->addBooleanFilters()
            ->addTextFilters()
            ->addNumberFilters()
            ->addDateRangeFilter()
            ->addTimeRangeFilter()
            ->addSort();
    }

    public function addGlobalSearch()
    {
        if (! $this->search) {
            return $this;
        }

        $this->query->where(function ($query) {
            foreach (explode(' ', $this->search) as $search) {
                $query->where(function ($query) use ($search) {
                    $this->searchableColumns()->each(function ($column, $i) use ($query, $search) {
                        $query->orWhere(function ($query) use ($i, $search) {
                            foreach ($this->getColumnField($i) as $column) {
                                $query->when(is_array($column), function ($query) use ($search, $column) {
                                    foreach ($column as $col) {
                                        $query->orWhereRaw('LOWER('.$col.') like ?', "%$search%");
                                    }
                                }, function ($query) use ($search, $column) {
                                    $query->orWhereRaw('LOWER('.$column.') like ?', "%$search%");
                                });
                            }
                        });
                    });
                });
            }
        });

        return $this;
    }

    public function addScopeColumns()
    {
        $this->scopeColumns()->each(function ($column) {
            $this->query->{$column['scope']}($column['label']);
        });

        return $this;
    }

    public function addSort()
    {
        if (isset($this->sort)) {
            $this->query->orderBy(DB::raw($this->getSortString()), $this->direction ? 'asc' : 'desc');
        }

        return $this;
    }

    public function getCallbacksProperty()
    {
        return collect($this->freshColumns)->filter->callback->mapWithKeys(function ($column) {
            return [$column['name'] => $column['callback']];
        });
    }

    public function getEditablesProperty()
    {
        return collect($this->freshColumns)->filter(function ($column) {
            return $column['type'] === 'editable';
        })->mapWithKeys(function ($column) {
            return [$column['name'] => true];
        });
    }

    public function mapCallbacks($paginatedCollection)
    {
        $paginatedCollection->getCollection()->map(function ($row, $i) {
            foreach ($row as $name => $value) {
                if (isset($this->editables[$name])) {
                    $row->$name = view('datatables::editable', [
                        'value' => $value,
                        'table' => $this->builder()->getModel()->getTable(),
                        'column' => Str::after($name, '.'),
                        'rowId' => $row->{$this->builder()->getModel()->getTable().'.'.$this->builder()->getModel()->getKeyName()},
                    ]);
                } elseif (isset($this->callbacks[$name]) && is_string($this->callbacks[$name])) {
                    $row->$name = $this->{$this->callbacks[$name]}($value, $row);
                } elseif (Str::startsWith($name, 'callback_')) {
                    $row->$name = $this->callbacks[$name](...explode(static::SEPARATOR, $value));
                } elseif (isset($this->callbacks[$name]) && is_callable($this->callbacks[$name])) {
                    $row->$name = $this->callbacks[$name]($value, $row);
                }

                if ($this->search && ! config('livewire-datatables.suppress_search_highlights') && $this->searchableColumns()->firstWhere('name', $name)) {
                    $row->$name = $this->highlight($row->$name, $this->search);
                }
            }

            return $row;
        });

        return $paginatedCollection;
    }

    public function getDisplayValue($index, $value)
    {
        return is_array($this->freshColumns[$index]['filterable']) && is_numeric($value)
            ? collect($this->freshColumns[$index]['filterable'])->firstWhere('id', '=', $value)['name'] ?? $value
            : $value;
    }

    /*  This can be called to apply highlting of the search term to some string.
     *  Motivation: Call this from your Column::Callback to apply highlight to a chosen section of the result.
     */
    public function highlightStringWithCurrentSearchTerm(string $originalString)
    {
        if (! $this->search) {
            return $originalString;
        } else {
            return static::highlightString($originalString, $this->search);
        }
    }

    /* Utility function for applying highlighting to given string */
    public static function highlightString(string $originalString, string $searchingForThisSubstring)
    {
        $searchStringNicelyHighlightedWithHtml = view(
            'datatables::highlight',
            ['slot' => $searchingForThisSubstring]
        )->render();
        $stringWithHighlightedSubstring = str_ireplace(
            $searchingForThisSubstring,
            $searchStringNicelyHighlightedWithHtml,
            $originalString
        );

        return $stringWithHighlightedSubstring;
    }

    public function highlight($value, $string)
    {
        $output = substr($value, stripos($value, $string), strlen($string));

        if ($value instanceof View) {
            return $value
                ->with(['value' => str_ireplace($string, view('datatables::highlight', ['slot' => $output]), $value->gatherData()['value'] ?? $value->gatherData()['slot'])]);
        }

        return str_ireplace($string, view('datatables::highlight', ['slot' => $output]), $value);
    }

    public function render()
    {
        return view('datatables::datatable');
    }

    public function export()
    {
        $this->forgetComputed();

        return Excel::download(new DatatableExport($this->getQuery()->get()), 'DatatableExport.xlsx');
    }

    public function getQuery()
    {
        $this->buildDatabaseQuery();

        return $this->query->toBase();
    }

    public function checkboxQuery()
    {
        $select = $this->resolveCheckboxColumnName(collect($this->freshColumns)->firstWhere('type', 'checkbox'));

        return $this->query->reorder()->get()->map(function ($row) {
            return (string) $row->checkbox_attribute;
        });
    }

    public function toggleSelectAll()
    {
        if (count($this->selected) === $this->getQuery()->count()) {
            $this->selected = [];
        } else {
            $this->selected = $this->checkboxQuery()->values()->toArray();
        }
        $this->forgetComputed();
    }
}