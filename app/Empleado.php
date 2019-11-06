<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'empleados';
    public $timestamps = false;

}
