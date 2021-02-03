<?php

namespace Mediconesystems\LivewireDatatables\Traits;

use Illuminate\Support\Facades\Validator;

trait WithCreateOrEditAction
{
    public $openCreateModal = false;
    public $editingMode;

    public function showCreateModal($id=null)
    {
        if($id){
            $this->editingMode = "edit";
            $this->{$this->table} = $this->model::findOrFail($id);
        } else {
            $this->editingMode = "create";
            // $this->{$this->table} = new $this->model;
        }
        $this->openCreateModal = true;
    }

    public function enabledCreate()
    {
        return in_array('create', $this->actions);
    }

    public function enabledEdit()
    {
        return in_array('edit', $this->actions);
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function create()
    {
        $this->validate();
        $this->{$this->table}->save();
        $this->cancel();
    }

    public function cancel()
    {
        $this->openCreateModal = false;
        $this->resetErrorBag();
        $this->{$this->table} = new $this->model;
    }

    public function rules()
    {
        return $this->{$this->table}->rules();
    }
}
