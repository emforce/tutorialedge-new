<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    /*
     * protecting vars from mass assignment
     */
    protected $guarded = array(
        'course_id' ,
        'id',
        'created_at',
        'updated_at',
    );
}
