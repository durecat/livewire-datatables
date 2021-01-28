<?php

namespace Mediconesystems\LivewireDatatables\Traits;

trait WithDeleteAction
{
    public $confirmDeletion = false;
    public $deleteTargetID;

    public function showDeleteModal($id)
    {
        $this->deleteTargetID = $id;
        $this->confirmDeletion = true;
    }

    public function delete()
    {
        $this->model::destroy($this->deleteTargetID);
        $this->deleteTargetID = null;
        $this->confirmDeletion = false;
    }
}
