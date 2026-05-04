<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discipline extends Model
{
    //
    public function livres()
    {
        return $this->hasMany(Livre::class, 'id_discipline', 'id');
    }
}
