<?php

namespace Mediconesystems\LivewireDatatables\Traits;

use Illuminate\Support\Arr;

trait WithCreateAction
{
    public $openCreateModal = false;
    public $fields = [];

    public function initiateFields()
    {
        foreach ($this->columns as $column) {
            $this->fields = Arr::add($this->fields, "{$column['name']}", null );
        }
    }

    public function showCreateModal()
    {
        $this->openCreateModal = true;
    }

    public function enabledCreate()
    {
        return in_array('create', $this->actions);
    }

    public function create()
    {
        dd($this->fields);
    }
}
