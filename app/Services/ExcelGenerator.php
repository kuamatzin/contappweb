<?php namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;

/**
* Generador de Excel
*/
class ExcelGenerator
{
    protected $title;
    protected $creator;
    protected $company;
    protected $description;
    protected $datos;

    function __construct($title, $creator, $company, $description, $datos)
    {
        $this->title = $title;
        $this->creator = $creator;
        $this->company = $company;
        $this->description = $description;
        $this->datos = $datos;
    }

    private function datos_de_archivo($excel)
    {
        // Set the title
        $excel->setTitle($this->title);

        // Chain the setters
        $excel->setCreator($this->creator)
              ->setCompany($this->company);

        // Call them separately
        $excel->setDescription($this->description);

        return $excel;
    }

    private function crear_datos()
    {
        $datos_array = array();

        $facturas = $this->datos;

        foreach ($facturas as $key => $factura) {
            $datos_factura = [$factura->fecha, $factura->tipoDeComprobante, $factura->rfcDeEmisor, $factura->nombreDeEmisor, $factura->uuid, $factura->folio, $factura->subTotal, $factura->descuento, $factura->totalImpuestosTrasladados, '', '', $factura->total];

            array_push($datos_array, $datos_factura);
        }

        $nombres_columnas = ['FECHA DE EMISIÓN', 'TIPO DE COMPROBANTE', 'RFC EMISOR', 'NOMBRE, DENOMINACIÓN O RAZÓN SOCIAL', 'FOLIO FISCAL', 'FOLIO INTERNO', 'IMPORTE', 'DESCUENTO', 'IVA', 'RETENCIÓN DE IVA', 'RETENCIÓN DE ISR', 'TOTAL'];

        array_unshift($datos_array, $nombres_columnas);

        return $datos_array;
    }

    private function crear_hoja($excel, $datos)
    {
        $excel->sheet('Datos', function($sheet) use($datos){
            $sheet->with($datos);
        });

        return $excel;
    }

    public function crearExcel()
    {
        Excel::create('reporteFacturas', function($excel) {

            $excel = $this->datos_de_archivo($excel);

            $datos = $this->crear_datos();

            $excel = $this->crear_hoja($excel, $datos);

        })->store('xlsx', 'reportes');;
    }
}