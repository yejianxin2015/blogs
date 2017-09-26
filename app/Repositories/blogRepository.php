<?php

namespace App\Repositories;

use App\Models\blog;
use InfyOm\Generator\Common\BaseRepository;

class blogRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'title',
        'content',
        'user_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return blog::class;
    }
}
