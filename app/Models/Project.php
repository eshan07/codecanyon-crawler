<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['uid', 'project_name', 'price'];

    public function sales()
    {
        return $this->hasMany(Sale::class,'project_id', 'id');
    }
}
