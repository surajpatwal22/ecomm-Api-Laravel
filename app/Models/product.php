<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'file',
        'price',
        'category',
    ];

    protected $appends = ['file_data'];


    public function getFileDataAttribute(){
        return url($this->file);
    }
}
