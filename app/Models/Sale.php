<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;
    protected $fillable = ['project_id', 'sales_count', 'new_sale_count', 'refund_count'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
