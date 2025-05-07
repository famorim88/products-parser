<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportHistory extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'import_histories';

    protected $fillable = [
        'imported_at',
        'total_products'
    ];
}
