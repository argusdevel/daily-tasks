<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'category_id',
        'title',
        'description'
    ];
}
