<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public function subcategories(){
        return $this->hasMany(Category::class)->with('subcategories');
    }

    // public function categories(){
    //     return $this->hasMany(Category::class)->with('root');
    // }

    // public function subcategories(){
    //     return $this->hasMany(Category::class)->with('categories');
    // }
}
