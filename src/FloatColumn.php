<?php

namespace Mediconesystems\LivewireDatatables;

class FloatColumn extends Column
{
    public $type = 'number';
    public $callback;

    public function __construct()
    {
        $this->format();
    }

    public function format($decimals = 2)
    {
        $this->callback = function ($value) use ($decimals) {
            return $value ? number_format($value, $decimals) : null;
        };

        return $this;
    }
}
