<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Timestamps are timed according to the datetime of CSV upload (Job created_at), not Laravel model creation now()
    protected $guarded = ['id'];
}
