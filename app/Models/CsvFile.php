<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsvFile extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $appends = ['created_at_formatted'];

    public function getCreatedAtFormattedAttribute()
    {
        return Carbon::parse($this->created_at)->format('Y-m-d h:m:ia');
    }
}
