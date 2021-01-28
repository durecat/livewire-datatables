<?php

namespace Mediconesystems\LivewireDatatables\Traits;

trait WithEditAction
{
    // public $openCreateModal = false;

    // public function showCreateModal()
    // {
    //     $this->openCreateModal = true;
    // }

    public function enabledEdit()
    {
        return in_array('edit', $this->actions);
    }

    public function edit()
    {
    }
}
