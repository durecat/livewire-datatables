<?php

namespace Mediconesystems\LivewireDatatables\Traits;

trait WithColumnFilters
{
    public $activeDateFilters = [];
    public $activeTimeFilters = [];
    public $activeSelectFilters = [];
    public $activeBooleanFilters = [];
    public $activeTextFilters = [];
    public $activeNumberFilters = [];

    public function doBooleanFilter($index, $value)
    {
        $this->activeBooleanFilters[$index] = $value;
        $this->page = 1;
    }

    public function doSelectFilter($index, $value)
    {
        $this->activeSelectFilters[$index][] = $value;
        $this->page = 1;
    }

    public function doTextFilter($index, $value)
    {
        foreach (explode(' ', $value) as $val) {
            $this->activeTextFilters[$index][] = $val;
        }
        $this->page = 1;
    }

    public function doDateFilterStart($index, $start)
    {
        $this->activeDateFilters[$index]['start'] = $start;
        $this->page = 1;
    }

    public function doDateFilterEnd($index, $end)
    {
        $this->activeDateFilters[$index]['end'] = $end;
        $this->page = 1;
    }

    public function doTimeFilterStart($index, $start)
    {
        $this->activeTimeFilters[$index]['start'] = $start;
        $this->page = 1;
    }

    public function doTimeFilterEnd($index, $end)
    {
        $this->activeTimeFilters[$index]['end'] = $end;
        $this->page = 1;
    }

    public function doNumberFilterStart($index, $start)
    {
        $this->activeNumberFilters[$index]['start'] = (int) $start;
        $this->clearEmptyNumberFilter($index);
        $this->page = 1;
    }

    public function doNumberFilterEnd($index, $end)
    {
        $this->activeNumberFilters[$index]['end'] = $end ? (int) $end : null;
        $this->clearEmptyNumberFilter($index);
        $this->page = 1;
    }

    public function clearEmptyNumberFilter($index)
    {
        if ((!isset($this->activeNumberFilters[$index]['start']) || $this->activeNumberFilters[$index]['start'] == '') && (!isset($this->activeNumberFilters[$index]['end']) || $this->activeNumberFilters[$index]['end'] == '')) {
            $this->removeNumberFilter($index);
        }
    }

    public function removeSelectFilter($column, $key = null)
    {
        unset($this->activeSelectFilters[$column][$key]);
        if (count($this->activeSelectFilters[$column]) < 1) {
            unset($this->activeSelectFilters[$column]);
        }
    }

    public function clearDateFilter()
    {
        $this->dates = null;
    }

    public function clearTimeFilter()
    {
        $this->times = null;
    }

    public function clearFilters()
    {
        $this->activeSelectFilters = [];
        $this->activeBooleanFilters = [];
        $this->activeTextFilters = [];
        $this->activeNumberFilters = [];
    }

    public function clearAllFilters()
    {
        $this->clearDateFilter();
        $this->clearTimeFilter();
        $this->activeSelectFilters = [];
        $this->activeBooleanFilters = [];
        $this->activeTextFilters = [];
        $this->activeNumberFilters = [];
    }

    public function removeBooleanFilter($column)
    {
        unset($this->activeBooleanFilters[$column]);
    }

    public function removeTextFilter($column, $key = null)
    {
        if ($key) {
            unset($this->activeTextFilters[$column][$key]);
            if (count($this->activeTextFilters[$column]) < 1) {
                unset($this->activeTextFilters[$column]);
            }
        } else {
            unset($this->activeTextFilters[$column]);
        }
    }

    public function removeNumberFilter($column)
    {
        unset($this->activeNumberFilters[$column]);
    }

    public function getColumnField($index)
    {
        if ($this->freshColumns[$index]['scope']) {
            return 'scope';
        }

        if ($this->freshColumns[$index]['raw']) {
            return [(string) $this->freshColumns[$index]['sort']];
        }

        return [$this->getSelectStatements()[$index]];
    }

    public function addScopeSelectFilter($query, $index, $value)
    {
        if (!isset($this->freshColumns[$index]['scopeFilter'])) {
            return;
        }

        return $query->{$this->freshColumns[$index]['scopeFilter']}($value);
    }

    public function addScopeNumberFilter($query, $index, $value)
    {
        if (!isset($this->freshColumns[$index]['scopeFilter'])) {
            return;
        }

        return $query->{$this->freshColumns[$index]['scopeFilter']}($value);
    }

    public function addAggregateFilter($query, $index, $filter)
    {
        $column = $this->freshColumns[$index];
        $relation = Str::before($column['name'], '.');
        $aggregate = $this->columnAggregateType($column);
        $field = Str::before(explode('.', $column['name'])[1], ':');

        $query->when($column['type'] === 'boolean', function ($query) use ($filter, $relation, $field, $aggregate) {
            $query->where(function ($query) use ($filter, $relation, $field, $aggregate) {
                if ($filter) {
                    $query->hasAggregate($relation, $field, $aggregate);
                } else {
                    $query->hasAggregate($relation, $field, $aggregate, '<');
                }
            });
        })->when($aggregate === 'group_concat' && count($filter), function ($query) use ($filter, $relation, $field, $aggregate) {
            $query->where(function ($query) use ($filter, $relation, $field, $aggregate) {
                foreach ($filter as $value) {
                    $query->hasAggregate($relation, $field, $aggregate, 'like', '%' . $value . '%');
                }
            });
        })->when(isset($filter['start']), function ($query) use ($filter, $relation, $field, $aggregate) {
            $query->hasAggregate($relation, $field, $aggregate, '>=', $filter['start']);
        })->when(isset($filter['end']), function ($query) use ($filter, $relation, $field, $aggregate) {
            $query->hasAggregate($relation, $field, $aggregate, '<=', $filter['end']);
        });
    }

    public function getSelectFiltersProperty()
    {
        return collect($this->freshColumns)->filter->selectFilter;
    }

    public function getBooleanFiltersProperty()
    {
        return collect($this->freshColumns)->filter->booleanFilter;
    }

    public function getTextFiltersProperty()
    {
        return collect($this->freshColumns)->filter->textFilter;
    }

    public function getNumberFiltersProperty()
    {
        return collect($this->freshColumns)->filter->numberFilter;
    }

    public function getActiveFiltersProperty()
    {
        return count($this->activeDateFilters)
            || count($this->activeTimeFilters)
            || count($this->activeSelectFilters)
            || count($this->activeBooleanFilters)
            || count($this->activeTextFilters)
            || count($this->activeNumberFilters);
    }

    public function addSelectFilters()
    {
        if (count($this->activeSelectFilters) < 1) {
            return $this;
        }

        $this->query->where(function ($query) {
            foreach ($this->activeSelectFilters as $index => $activeSelectFilter) {
                $query->where(function ($query) use ($index, $activeSelectFilter) {
                    foreach ($activeSelectFilter as $value) {
                        if ($this->columnIsAggregateRelation($this->freshColumns[$index])) {
                            $this->addAggregateFilter($query, $index, $activeSelectFilter);
                        } else {
                            if (!$this->addScopeSelectFilter($query, $index, $value)) {
                                $query->orWhere(function ($query) use ($value, $index) {
                                    foreach ($this->getColumnField($index) as $column) {
                                        $query->orWhere($column, $value);
                                    }
                                });
                            }
                        }
                    }
                });
            }
        });

        return $this;
    }

    public function addBooleanFilters()
    {
        if (count($this->activeBooleanFilters) < 1) {
            return $this;
        }
        $this->query->where(function ($query) {
            foreach ($this->activeBooleanFilters as $index => $value) {
                if ($this->getColumnField($index) === 'scope') {
                    $this->addScopeSelectFilter($query, $index, $value);
                } elseif ($this->columnIsAggregateRelation($this->freshColumns[$index])) {
                    $this->addAggregateFilter($query, $index, $value);
                } elseif ($value == 1) {
                    $query->where(DB::raw($this->getColumnField($index)[0]), '>', 0);
                } elseif (strlen($value)) {
                    $query->whereNull(DB::raw($this->getColumnField($index)[0]))
                        ->orWhere(DB::raw($this->getColumnField($index)[0]), 0);
                }
            }
        });

        return $this;
    }

    public function addTextFilters()
    {
        if (!count($this->activeTextFilters)) {
            return $this;
        }

        $this->query->where(function ($query) {
            foreach ($this->activeTextFilters as $index => $activeTextFilter) {
                $query->where(function ($query) use ($index, $activeTextFilter) {
                    foreach ($activeTextFilter as $value) {
                        if ($this->columnIsRelation($this->freshColumns[$index])) {
                            $this->addAggregateFilter($query, $index, $activeTextFilter);
                        } else {
                            $query->orWhere(function ($query) use ($index, $value) {
                                foreach ($this->getColumnField($index) as $column) {
                                    $column = is_array($column) ? $column[0] : $column;
                                    $query->orWhereRaw('LOWER(' . $column . ') like ?', [strtolower("%$value%")]);
                                }
                            });
                        }
                    }
                });
            }
        });

        return $this;
    }

    public function addNumberFilters()
    {
        if (!count($this->activeNumberFilters)) {
            return $this;
        }
        $this->query->where(function ($query) {
            foreach ($this->activeNumberFilters as $index => $filter) {
                if ($this->columnIsAggregateRelation($this->freshColumns[$index])) {
                    $this->addAggregateFilter($query, $index, $filter);
                } else {
                    $this->addScopeNumberFilter($query, $index, [
                        isset($filter['start']) ? $filter['start'] : 0,
                        isset($filter['end']) ? $filter['end'] : 9999999,
                    ])
                        ?? $query->whereRaw($this->getColumnField($index)[0] . ' BETWEEN ? AND ?', [
                            isset($filter['start']) ? $filter['start'] : 0,
                            isset($filter['end']) ? $filter['end'] : 9999999,
                        ]);
                }
            }
        });

        return $this;
    }

    public function addDateRangeFilter()
    {
        if (!count($this->activeDateFilters)) {
            return $this;
        }

        $this->query->where(function ($query) {
            foreach ($this->activeDateFilters as $index => $filter) {
                if (!((isset($filter['start']) && $filter['start'] != '') || (isset($filter['end']) && $filter['end'] != ''))) {
                    break;
                }
                $query->whereBetween($this->getColumnField($index)[0], [
                    isset($filter['start']) && $filter['start'] != '' ? $filter['start'] : '0000-00-00',
                    isset($filter['end']) && $filter['end'] != '' ? $filter['end'] : now()->format('Y-m-d'),
                ]);
            }
        });

        return $this;
    }

    public function addTimeRangeFilter()
    {
        if (!count($this->activeTimeFilters)) {
            return $this;
        }

        $this->query->where(function ($query) {
            foreach ($this->activeTimeFilters as $index => $filter) {
                $start = isset($filter['start']) && $filter['start'] != '' ? $filter['start'] : '00:00:00';
                $end = isset($filter['end']) && $filter['end'] != '' ? $filter['end'] : '23:59:59';

                if ($end < $start) {
                    $query->where(function ($subQuery) use ($index, $start, $end) {
                        $subQuery->whereBetween($this->getColumnField($index)[0], [$start, '23:59'])
                            ->orWhereBetween($this->getColumnField($index)[0], ['00:00', $end]);
                    });
                } else {
                    $query->whereBetween($this->getColumnField($index)[0], [$start, $end]);
                }
            }
        });

        return $this;
    }

}
