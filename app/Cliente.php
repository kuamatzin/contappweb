<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = "clientes";

    protected $fillable = [
        'nombre',
        'rfc'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function facturas()
    {
        return $this->hasMany('App\Factura');
    }

    public function facturasEmitidas()
    {
        return $this->facturas->where('tipoFactura', 1);
    }

    public function facturasRecibidas()
    {
        return $this->facturas->where('tipoFactura', 0);
    }

    public function categorias()
    {
        return $this->hasMany('App\Categoria');
    }
}
