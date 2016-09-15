<?php

namespace App\Http\Controllers;

use App\Categoria;
use App\Cliente;
use App\Factura;
use App\Http\Requests;
use App\Services\ExcelGenerator;
use App\Services\FacturaPDF;
use App\Services\XML;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use RobbieP\CloudConvertLaravel\Facades\CloudConvert;
use Validator;

class FacturaController extends Controller
{
    public function index(){
        return view('facturas.index');
    }

    public function store(Request $request){
        return $this->addFacturaFile($request);
    }

    public function crearExcel()
    {
        $excel = new ExcelGenerator('Hoja de Trabajo', 'Carlos Cuamatzin Hernández', 'Nivel 6', 'Facturas', 'hola');
        $excel->crearExcel();
    }

    private function addFacturaFile($request)
    {
        $cliente = Cliente::findOrFail($request->cliente_id);
        //Validación de la factura
        $this->validate($request, [
            'factura' => 'required|mimes:xml'
        ]);
        $file = $request->file('factura');
        $contents = File::get($file);
        $xml = new \SimpleXMLElement($contents);
        $sello = (string)$xml['sello'];
        $fecha = Carbon::createFromFormat('Y-m-d\TH:i:s', (string)$xml['fecha']);
        //Seleccionamos un nombre único para la factura
        $name = time() . $file->getClientOriginalName();
        //Verificar si ya esta en la base de datos
        if (Factura::existe($sello, $fecha)->count() == 0) {
            $factura = XML::createFactura($xml, $name, $request->cliente_id, $fecha);
            
            if ($factura['rfcDeEmisor'] == $cliente->rfc) {
                //Factura emitida
                $factura = array_add($factura, 'tipoFactura', 1);
            }
            //Debería ser factura recibida
            else {
                //Factura recibida
                if ($factura['rfcDeReceptor'] == $cliente->rfc) {
                    $factura = array_add($factura, 'tipoFactura', 0);
                }
                //Factura no pertenece a este cliente
                else {
                    return "Factura no pertece a cliente";
                }
            }
            $factura_nueva = Auth::user()->facturas()->create($factura);
            //Agregar nueva factura
            //Guardamos en el sistema de archivos del servidor
            $file->move('facturas_clientes', $name);
            $filename_word = FacturaPDF::create($factura_nueva);
            $factura_nueva->archivo_word = $filename_word;
            return "Factura guardada";
        }
        else {
            return "Factura ya existe";
        }
    }

    public function busqueda($cliente_id)
    {
        $tipoBusqueda = Input::get('emisores');
        $tipoFactura = Input::get('tipo');
        $rfcBusqueda = Input::get('rfcBusqueda');
        //$fechaInicial = Input::get('fechaInicial') . 'T00:00:00';
        $fechaInicial = Input::get('ejercicio_fiscal') . '-' . Input::get('mes') . '-01T00:00:00';
        $fechaInicial = Carbon::createFromFormat('Y-m-d\TH:i:s', $fechaInicial);
        //$fechaFinal = Input::get('fechaFinal') . 'T23:59:59';
        $fechaFinal = Input::get('ejercicio_fiscal') . '-' . Input::get('mes') . '-31T23:59:59';
        $fechaFinal = Carbon::createFromFormat('Y-m-d\TH:i:s', $fechaFinal);
        $tipoDeComprobante = Input::get('comprobante');

        //Busqueda de Facturas por Categoría
        if ($tipoBusqueda == 1) {
            $categoria_id = Input::get('categoria_id');
            $categoria = Categoria::findOrFail($categoria_id);
            $emisores = $categoria->emisores;
            $rfc = Input::get('rfcBusqueda');
            $facturas = [];
            if ($rfc != '') {
                if (sizeof($emisores) > 0) {
                    $facturas = Factura::where('cliente_id', $cliente_id)->where('rfcDeEmisor', $rfc)->where(function ($query) use($emisores) {
                        foreach($emisores as $emisor) {
                            $query->orWhere('rfcDeEmisor', $emisor['rfc']);
                        }
                    })->whereBetween('fecha', [$fechaInicial, $fechaFinal])->get();
                }
            }
            else {
                if (sizeof($emisores) > 0) {
                    $facturas = Factura::where('cliente_id', $categoria->cliente_id)->where(function ($query) use($emisores) {
                        foreach($emisores as $emisor) {
                            $query->orWhere('rfcDeEmisor', $emisor['rfc']);
                        }
                    })->whereBetween('fecha', [$fechaInicial, $fechaFinal])->get();
                }
            }
            /*
            foreach ($facturas as $key => $factura) {
                $factura->setAppends(['is_admin']);
            }
            */
            return $facturas;
        }
        //Busqueda de facturas normal
        else {
            if ($rfcBusqueda != '') {
                if ($tipoDeComprobante == 'indiferente') {
                    if ($tipoFactura == 'indiferente') {
                        $facturas = Factura::where('cliente_id', $cliente_id)->whereBetween('fecha', [$fechaInicial, $fechaFinal])->where('rfcDeEmisor', $rfcBusqueda)->orWhere('rfcDeReceptor', $rfcBusqueda)->get();
                    }
                    elseif ($tipoFactura == '0') {
                        $facturas = Factura::where('cliente_id', $cliente_id)->whereBetween('fecha', [$fechaInicial, $fechaFinal])->where('rfcDeEmisor', $rfcBusqueda)->where('tipoFactura', $tipoFactura)->get();
                    }
                    else {
                        $facturas = Factura::where('cliente_id', $cliente_id)->whereBetween('fecha', [$fechaInicial, $fechaFinal])->where('tipoFactura', $tipoFactura)->where('rfcDeReceptor', $rfcBusqueda)->get();
                    }
                }
                else {
                    if ($tipoFactura == 'indiferente') {
                        $facturas = Factura::where('cliente_id', $cliente_id)->whereBetween('fecha', [$fechaInicial, $fechaFinal])->where('rfcDeEmisor', $rfcBusqueda)->orWhere('rfcDeReceptor', $rfcBusqueda)->where('tipoDeComprobante', $tipoDeComprobante)->get();
                    }
                    elseif ($tipoFactura == '0') {
                        $facturas = Factura::where('cliente_id', $cliente_id)->whereBetween('fecha', [$fechaInicial, $fechaFinal])->where('rfcDeEmisor', $rfcBusqueda)->where('tipoFactura', $tipoFactura)->where('tipoDeComprobante', $tipoDeComprobante)->get();
                    }
                    else {
                        $facturas = Factura::where('cliente_id', $cliente_id)->whereBetween('fecha', [$fechaInicial, $fechaFinal])->where('tipoFactura', $tipoFactura)->where('rfcDeReceptor', $rfcBusqueda)->where('tipoDeComprobante', $tipoDeComprobante)->get();
                    }
                }
            }
            else {
                if ($tipoFactura != 'indiferente') {
                    if ($tipoDeComprobante == 'indiferente') {
                        $facturas = Factura::where('cliente_id', $cliente_id)->whereBetween('fecha', [$fechaInicial, $fechaFinal])->where('tipoFactura', $tipoFactura)->get();
                    }
                    else {
                        $facturas = Factura::where('cliente_id', $cliente_id)->whereBetween('fecha', [$fechaInicial, $fechaFinal])->where('tipoFactura', $tipoFactura)->where('tipoDeComprobante', $tipoDeComprobante)->get();
                    }
                }
                else {
                    if ($tipoDeComprobante == 'indiferente') {
                        $facturas = Factura::where('cliente_id', $cliente_id)->whereBetween('fecha', [$fechaInicial, $fechaFinal])->get();
                    }
                    else {
                        $facturas = Factura::where('cliente_id', $cliente_id)->whereBetween('fecha', [$fechaInicial, $fechaFinal])->where('tipoDeComprobante', $tipoDeComprobante)->get();
                    }
                }
            }
            if (Input::get('excel') == '1') {
                $excel = new ExcelGenerator('Reporte', 'Carlos Cuamatzin', 'Nivel6', 'Reporte Facturas', $facturas);
                $excel->crearExcel();
            }
            else {
                return $facturas;
            }
        }
    }

    public function descargarPDF($factura_id)
    {
        $factura = Factura::findOrFail($factura_id);
        if ($factura->convertido) {
            return response()->download('facturas_pdf/' . $factura->archivo_pdf);
        }
        else {
            $nombre_factura = FacturaPDF::create($factura);
            CloudConvert::file("facturas_word/$nombre_factura" . ".docx")->to("facturas_pdf/$nombre_factura" . ".pdf");
            $factura->archivo_word = $nombre_factura . ".docx";
            $factura->archivo_pdf = $nombre_factura . ".pdf";
            $factura->convertido = true;
            $factura->save();
            return response()->download('facturas_pdf/' . $factura->archivo_pdf);
        }
    }
}