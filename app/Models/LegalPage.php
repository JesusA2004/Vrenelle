<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    protected $fillable = ['slug', 'title', 'content', 'meta_title', 'meta_description'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
