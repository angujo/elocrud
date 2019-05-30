<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\DBColumn;
use Angujo\DBReader\Models\DBTable;

class Morpher
{
    private static $morphs = [];

    protected function __construct(DBColumn $table)
    {
    }
    
}