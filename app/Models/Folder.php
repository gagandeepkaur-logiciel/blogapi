<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};
use App\Models\User;

class Folder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'userid',
        'name',
        'folder_id',
        'path',
        'facebookPage_id',
        'album_id',
        'created_by',
    ];

    public function subfolders()
    {
        return $this->hasMany(Folder::class)->with('subfolders');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $dates = ['deleted_at'];
}
