<?php

namespace PabloFerrari\TestPackage;
use ReflectionClass;

trait Models
{

    public $exclude = [
        'Illuminate\Database\Eloquent\Model',
        'Illuminate\Database\Eloquent\Relations\Pivot',
        'getRelationships'
    ];

    public function getTable()
    {
        return $this->table;
    }

    public function getRelationships($model){

        $class = new ReflectionClass($this);
        $res = [];
        $methods = $class->getMethods();

        foreach($methods AS $m){
            if($m->class === "App\Models\\$model" && $m->name !== 'getTable' && $m->name !== 'getRelationships'){
                $res[] = $m->name;
            }
        }
        $this->relationships = $res;

    }


}
?>
