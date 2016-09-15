<?php namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
* Genera Facturas en PDF
*/
class FacturaPDF
{
    
    function __construct()
    {
            
    }

    private static function setImageValueAlt($searchAlt, $replace){
        $this->setImageValue($this->getImgFileName($this->seachImagerId($searchAlt)),$replace);
    }

    private static function download($document, $filename = "reporte.docx")
    {
        $document->saveAs($filename);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        flush();
        readfile($filename);
        unlink($filename);
    }

    public static function create($factura)
    {
        $document = new \PhpOffice\PhpWord\TemplateProcessor('templates/factura.docx');
        $document->setValue('emisor', htmlspecialchars($factura->nombreDeEmisor));
        $document->setValue('direccion_emisor', htmlspecialchars("$factura->calleDeEmisor No. Ext: $factura->noExteriorDeEmisor No. Int: $factura->noInteriorDeEmisor Colonia: $factura->coloniaDeEmisor $factura->localidadDeEmisor $factura->municipioDeEmisor $factura->estadoDeEmisor $factura->paisDeEmisor"));
        $document->setValue('receptor', htmlspecialchars($factura->nombreDeReceptor));
        $document->setValue('direccion_receptor', htmlspecialchars("$factura->calleDeReceptor No. Ext: $factura->noExteriorDeReceptor No. Int: $factura->noInteriorDeReceptor Colonia: $factura->coloniaDeReceptor $factura->localidadDeReceptor $factura->municipioDeReceptor $factura->estadoDeReceptor $factura->paisDeReceptor"));
        $document->setValue('lugar_expedicion', htmlspecialchars($factura->lugarExpedicion));
        $document->setValue('regimen_fiscal', htmlspecialchars('Régimen General de Ley Persona Moral'));
        $document->setValue('folio_fiscal', htmlspecialchars($factura->uuid));
        $document->setValue('fecha_emision', htmlspecialchars($factura->fecha));
        $document->setValue('certificado_digital', htmlspecialchars($factura->noCertificado));

        $document->cloneRow('cantidad', sizeof($factura->conceptos));

        foreach ($factura->conceptos as $key => $concepto) {
            $value = $key + 1;
            $document->setValue("cantidad#$value", htmlspecialchars($concepto['cantidad']));
            $document->setValue("unidad#$value", htmlspecialchars($concepto['unidad']));
            $document->setValue("conceptos#$value", htmlspecialchars($concepto['descripcion']));
            $document->setValue("precio_unitario#$value", htmlspecialchars($concepto['valorUnitario']));
            $document->setValue("importe#$value", htmlspecialchars($concepto['importe']));
        }



        $document->setValue('subtotal', htmlspecialchars('$' . $factura->subTotal));
        $document->setValue('iva', htmlspecialchars($factura->impuesto));
        $document->setValue('total', htmlspecialchars('$' . $factura->total));
        $document->setValue('forma_pago', htmlspecialchars($factura->formaDePago));
        $document->setValue('metodo_pago', htmlspecialchars($factura->metodoDePago));
        $document->setValue('banco', htmlspecialchars('SANTANDER'));
        $document->setValue('cuenta', htmlspecialchars('Cuenta'));
        $document->setValue('condiciones', htmlspecialchars($factura->condicionesDePago));
        $document->setValue('cadena_original', htmlspecialchars($factura->certificado));
        $document->setValue('sello_cdfi', htmlspecialchars($factura->selloCFD));
        $document->setValue('sello_sat', htmlspecialchars($factura->selloSAT));
        $document->setValue('fecha_certificacion', htmlspecialchars($factura->fecha));
        $document->setValue('numero_certificado_sat', htmlspecialchars($factura->folio));

        $qrcode = QrCode::format('png')->size(360)->generate('?re=' . $factura->rfcDeEmisor . '&rr=' .  $factura->rfcDeReceptor . '&tt=' . $factura->total . '&id=' . $factura->folio, 'qrcodes/qrcode.png');
        $document->setImageValue($document->getImgFileName($document->seachImagerId("image1.png")), 'qrcodes/qrcode.png');

        $filename = time().'-'.mt_rand();
        $document->saveAs("facturas_word/$filename" . ".docx");
        return $filename;
    }
}