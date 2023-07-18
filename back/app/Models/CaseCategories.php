<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseCategories extends Model
{
    use HasFactory;
    protected $table = 'case_categories';
    protected $fillable = [
        'name_ar','name_en'
    ];

    public function Case()
    {
        return $this->hasMany(Cases::class, 'case_categores_id', 'id');
    }
}
