<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class blog
 * @package App\Models
 * @version July 21, 2017, 9:38 am UTC
 */
class blog extends Model
{
    use SoftDeletes;

    public $table = 'blogs';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'title',
        'content',
        'user_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'title' => 'string',
        'content' => 'string',
        'user_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'title' => 'required',
        'content' => 'required',
        'user_id' => 'numeric'
    ];

    
}
