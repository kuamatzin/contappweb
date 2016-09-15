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
        'impuestos_retenidos',
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
        'impuestos_trasladados' => 'array',
        'impuestos_retenidos' => 'array'
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

    public function total_iva()
    {
        $total_iva = 0;
        foreach ($this->impuestos_trasladados as $key => $traslado) {
            if ($traslado['impuesto'] == 'IVA') {
                $total_iva = $total_iva + $traslado['importe'];
            }
        }
        return $total_iva;
    }

    public function iva_retencion()
    {
        foreach ($this->impuestos_retenidos as $key => $retencion) {
            if ($retencion['impuesto'] == 'IVA') {
                return $retencion['importe'];
            }
        }
    }

    public function isr_retencion()
    {
        foreach ($this->impuestos_retenidos as $key => $retencion) {
            if ($retencion['impuesto'] == 'ISR') {
                return $retencion['importe'];
            }
        }
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
