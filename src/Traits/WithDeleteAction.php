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

    public function enabledDelete()
    {
        return in_array('delete', $this->actions);
    }

    public function delete()
    {
        $this->model::destroy($this->deleteTargetID);
        $this->deleteTargetID = null;
        $this->confirmDeletion = false;
    }
    
}
