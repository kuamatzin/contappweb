<?php namespace App\Services;

/**
 * XML class
 */
class XML
{
    /**
     * summary
     */
    public function __construct()
    {
        
    }

    public static function createFactura($xml, $name, $cliente_id, $fecha)
    {
        $atributos_faltantes = $xml->children('http://www.sat.gob.mx/cfd/3');
    
        $emisor = $xml->children('cfdi', true)->Emisor;
        $receptor = $xml->children('cfdi', true)->Receptor;
        $conceptos = $xml->children('cfdi', true)->Conceptos;
        $impuestos = $xml->children('cfdi', true)->Impuestos;
        $complemento = $xml->children('cfdi', true)->Complemento->children('tfd', true)->attributes();
        $traslados = $impuestos->children('cfdi', true)->Traslados->xpath('cfdi:Traslado');
        $retenciones = $impuestos->children('cfdi', true)->Retenciones;
        $retenciones = json_encode($retenciones);
        $retenciones = json_decode($retenciones,TRUE);
        if (sizeof($retenciones) > 0) {
            $retenciones = $impuestos->children('cfdi', true)->Retenciones->xpath('cfdi:Retencion');
        }

        $factura = [];

        //Datos Factura
        $factura = array_add($factura, 'noCertificado', (string)$xml['noCertificado']);
        $factura = array_add($factura, 'condicionesDePago', (string)$xml['condicionesDePago']);
        $factura = array_add($factura, 'descuento', (string)$xml['descuento']);
        $factura = array_add($factura, 'folio', (string)$xml['folio']);
        $factura = array_add($factura, 'serie', (string)$xml['serie']);
        $factura = array_add($factura, 'sello', (string)$xml['sello']);
        $factura = array_add($factura, 'certificado', (string)$xml['certificado']);
        $factura = array_add($factura, 'fecha', $fecha);
        $factura = array_add($factura, 'lugarExpedicion', (string)$xml['LugarExpedicion']);
        $factura = array_add($factura, 'tipoDeComprobante', (string)$xml['tipoDeComprobante']);
        $factura = array_add($factura, 'moneda', (string)$xml['Moneda']);
        $factura = array_add($factura, 'formaDePago', (string)$xml['formaDePago']);
        $factura = array_add($factura, 'metodoDePago', (string)$xml['metodoDePago']);
        $factura = array_add($factura, 'subTotal', (string)$xml['subTotal']);
        $factura = array_add($factura, 'total', (string)$xml['total']);
        $factura = array_add($factura, 'version', (string)$xml['version']);
        //Emisor
        $factura = array_add($factura, 'rfcDeEmisor', (string)$xml->xpath('cfdi:Emisor')[0]['rfc']);
        $factura = array_add($factura, 'nombreDeEmisor', (string)$xml->xpath('cfdi:Emisor')[0]['nombre']);

        //Domicilio Emisor
        $factura = array_add($factura, 'calleDeEmisor', (string)$emisor->xpath('cfdi:DomicilioFiscal')[0]['calle']);
        $factura = array_add($factura, 'noExteriorDeEmisor', (string)$emisor->xpath('cfdi:DomicilioFiscal')[0]['noExterior']);
        $factura = array_add($factura, 'noInteriorDeEmisor', (string)$emisor->xpath('cfdi:DomicilioFiscal')[0]['noInterior']);
        $factura = array_add($factura, 'coloniaDeEmisor', (string)$emisor->xpath('cfdi:DomicilioFiscal')[0]['colonia']);
        $factura = array_add($factura, 'localidadDeEmisor', (string)$emisor->xpath('cfdi:DomicilioFiscal')[0]['localidad']);
        $factura = array_add($factura, 'municipioDeEmisor', (string)$emisor->xpath('cfdi:DomicilioFiscal')[0]['municipio']);
        $factura = array_add($factura, 'estadoDeEmisor', (string)$emisor->xpath('cfdi:DomicilioFiscal')[0]['estado']);
        $factura = array_add($factura, 'paisDeEmisor', (string)$emisor->xpath('cfdi:DomicilioFiscal')[0]['pais']);
        $factura = array_add($factura, 'codigoPostalDeEmisor', (string)$emisor->xpath('cfdi:DomicilioFiscal')[0]['codigoPostal']);
        
        //Receptor
        $factura = array_add($factura, 'rfcDeReceptor', (string)$xml->xpath('cfdi:Receptor')[0]['rfc']);
        $factura = array_add($factura, 'nombreDeReceptor', (string)$xml->xpath('cfdi:Receptor')[0]['nombre']);

        //Domicilio Receptor
        $factura = array_add($factura, 'calleDeReceptor', (string)$receptor->xpath('cfdi:Domicilio')[0]['calle']);
        $factura = array_add($factura, 'noExteriorDeReceptor', (string)$receptor->xpath('cfdi:Domicilio')[0]['noExterior']);
        $factura = array_add($factura, 'noInteriorDeReceptor', (string)$receptor->xpath('cfdi:Domicilio')[0]['noInterior']);
        $factura = array_add($factura, 'coloniaDeReceptor', (string)$receptor->xpath('cfdi:Domicilio')[0]['colonia']);
        $factura = array_add($factura, 'localidadDeReceptor', (string)$receptor->xpath('cfdi:Domicilio')[0]['localidad']);
        $factura = array_add($factura, 'municipioDeReceptor', (string)$receptor->xpath('cfdi:Domicilio')[0]['municipio']);
        $factura = array_add($factura, 'estadoDeReceptor', (string)$receptor->xpath('cfdi:Domicilio')[0]['estado']);
        $factura = array_add($factura, 'paisDeReceptor', (string)$receptor->xpath('cfdi:Domicilio')[0]['pais']);
        $factura = array_add($factura, 'codigoPostalDeReceptor', (string)$receptor->xpath('cfdi:Domicilio')[0]['codigoPostal']);

        //Conceptos
        $conceptos_totales = $conceptos->xpath('cfdi:Concepto');
        $array_conceptos_totales = array();
        foreach ($conceptos_totales as $key => $concepto) {
            $concepto_array = array();
            $concepto_array['cantidad'] = (string)$concepto['cantidad'];
            $concepto_array['descripcion'] = (string)$concepto['descripcion'];
            $concepto_array['importe'] = (string)$concepto['importe'];
            $concepto_array['noIdentificacion'] = (string)$concepto['noIdentificacion'];
            $concepto_array['unidad'] = (string)$concepto['unidad'];
            $concepto_array['valorUnitario'] = (string)$concepto['valorUnitario'];
            array_push($array_conceptos_totales, $concepto_array);
        }

        $factura = array_add($factura, 'conceptos', $array_conceptos_totales);

        //Impuestos
        $factura = array_add($factura, 'totalImpuestosRetenidos', (string)$xml->xpath('cfdi:Impuestos')[0]['totalImpuestosRetenidos']);
        $factura = array_add($factura, 'totalImpuestosTrasladados', (string)$xml->xpath('cfdi:Impuestos')[0]['totalImpuestosTrasladados']);

        //Traslados
        $traslados_array = array();
        foreach ($traslados as $key => $traslado) {
            $traslado_array = array();
            $traslado_array['impuesto'] = (string)$traslado['impuesto'];
            $traslado_array['tasa'] = (string)$traslado['tasa'];
            $traslado_array['importe'] = (string)$traslado['importe'];
            array_push($traslados_array, $traslado_array);
        }

        $factura = array_add($factura, 'impuestos_trasladados', $traslados_array);

        //Retenciones
        $retenciones_array = array();
        foreach ($retenciones as $key => $traslado) {
            $traslado_array = array();
            $traslado_array['impuesto'] = (string)$traslado['impuesto'];
            $traslado_array['tasa'] = (string)$traslado['tasa'];
            $traslado_array['importe'] = (string)$traslado['importe'];
            array_push($retenciones_array, $traslado_array);
        }

        $factura = array_add($factura, 'impuestos_retenidos', $retenciones_array);

        //Complementos
        $factura = array_add($factura, 'selloSAT', (string)$complemento['selloSAT']);
        $factura = array_add($factura, 'noCertificadoSAT', (string)$complemento['noCertificadoSAT']);
        $factura = array_add($factura, 'selloCFD', (string)$complemento['selloCFD']);
        $factura = array_add($factura, 'FechaTimbrado', (string)$complemento['FechaTimbrado']);
        $factura = array_add($factura, 'uuid', (string)$complemento['UUID']);
        $factura = array_add($factura, 'version_complemento', (string)$complemento['version']);


        $factura = array_add($factura, 'ruta', $name);
        $factura = array_add($factura, 'cliente_id', $cliente_id);

        return $factura;
    }
}