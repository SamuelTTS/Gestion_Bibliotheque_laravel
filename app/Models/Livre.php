<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Livre extends Model
{
    protected $fillable = ['titre', 'auteur', 'prix', 'nb_pages', 'id_discipline'];

    public function discipline()
    {
        return $this->belongsTo(Discipline::class, 'id_discipline', 'id');
    }
}
