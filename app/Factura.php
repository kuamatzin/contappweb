<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $table = "facturas";

    //condicionesdepago
    //descuent
    //folio
    protected $fillable = [
        'cliente_id',
        'noCertificado',
        'sello',
        'certificado',
        'condicionesDePago',
        'descuento',
        'fecha',
        'serie',
        'folio',
        'lugarExpedicion',
        'tipoDeComprobante',
        'moneda',
        'formaDePago',
        'metodoDePago',
        'subTotal',
        'total',
        'version',
        'rfcDeEmisor',
        'nombreDeEmisor',
        'calleDeEmisor',
        'noExteriorDeEmisor',
        'noInteriorDeEmisor',
        'coloniaDeEmisor',
        'localidadDeEmisor',
        'municipioDeEmisor',
        'estadoDeEmisor',
        'paisDeEmisor',
        'codigoPostalDeEmisor',
        'regimenDeEmisor',
        'rfcDeReceptor',
        'nombreDeReceptor',
        'calleDeReceptor',
        'noExteriorDeReceptor',
        'noInteriorDeReceptor',
        'coloniaDeReceptor',
        'localidadDeReceptor',
        'municipioDeReceptor',
        'estadoDeReceptor',
        'paisDeReceptor',
        'codigoPostalDeReceptor',
        'conceptos',
        'totalImpuestosRetenidos',
        'totalImpuestosTrasladados',
        'impuestos_trasladados',
        'fechaTimbrado',
        'selloCFD',
        'noCertificadoSAT',
        'selloSAT',
        'uuid',
        'version_complemento',
        'ruta',
        'tipoFactura'
    ];

    protected $casts = [
        'conceptos' => 'array',
        'impuestos_trasladados' => 'array'
    ];

    protected $dates = ['fecha'];

    //protected $appends = ['is_admin'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function cliente()
    {
        return $this->belongsTo('App\Cliente');
    }

    public function scopeExiste($query, $sello, $fecha)
    {
        return $query->where('sello', $sello)->where('fecha', $fecha)->get();
    }

    public function getUuidAttribute($value){
        return strtoupper($value);
    }

    public function getIsAdminAttribute()
    {
        return $this->cliente;
    }
}
