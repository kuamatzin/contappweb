<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = ['cliente_id', 'nombre', 'emisores'];

    protected $casts = [
        'emisores' => 'array', // Will convarted to (Array)
    ];

    public function cliente()
    {
        return $this->belongsTo('App\Cliente');
    }
}
