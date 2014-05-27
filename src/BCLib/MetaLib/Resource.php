<?php

namespace BCLib\MetaLib;

class Resource
{
    public $internal_number;
    public $number;
    public $name;
    public $short_name;
    public $searchable;

    public function __toString()
    {
        return $this->name;
    }
} 