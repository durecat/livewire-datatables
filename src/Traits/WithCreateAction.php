<?php

namespace Mediconesystems\LivewireDatatables\Traits;

trait WithCreateAction
{
    public $openCreateModal = false;

    public function showCreateModal()
    {
        $this->openCreateModal = true;
    }

    public function create()
    {
    }
}
