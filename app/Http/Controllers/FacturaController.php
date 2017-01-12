<?php

namespace App\Http\Controllers;

use App\Categoria;
use App\Cliente;
use App\Factura;
use App\Http\Requests;
use App\Request as RequestApp;
use App\Services\ExcelGenerator;
use App\Services\FacturaPDF;
use App\Services\XML;
use App\Services\ZIP;
use Carbon\Carbon;
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
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
        //Aqui empieza
        $contents = File::get($file);
        $xml = new \SimpleXMLElement($contents);
        $sello = (string)$xml['sello'];
        $fecha = Carbon::createFromFormat('Y-m-d\TH:i:s', (string)$xml['fecha']);
        //Seleccionamos un nombre único para la factura
        //Si pasa entonces la request viene de la carga manual de facturas
        if(strpos($file->getRealPath(), '/private/var/tmp/') !== false){
            $name = time() . $file->getClientOriginalName();
            $cliente_id = $request->cliente_id;
        }
        else {
            $name = time() . $file->getFileName();
            $cliente_id = Cliente::select('id')->where('rfc', $rfc)->first();
        }
        //Verificar si ya esta en la base de datos
        if (Factura::existe($sello, $fecha)->count() == 0) {
            $factura = XML::createFactura($xml, $name, $cliente_id, $fecha);
            
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
            $user = Cliente::findOrFail($cliente_id)->user;
            $factura_nueva = $user->facturas()->create($factura);
            //Agregar nueva factura
            //Guardamos en el sistema de archivos del servidor
            $file->move('facturas_clientes', $name);
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
        $mes = Input::get('mes');
        $rfc_cliente = Cliente::findOrFail($cliente_id)->rfc;
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
            else if (Input::get('zip') == '1') {
                $zip = new ZIP($facturas);
                $zip->zipFiles($mes, $rfc_cliente);
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

    public function webhook(Request $request)
    {
        $info = $request->all();
        $json = json_decode($info['data']);
        $identificador = $json->Contribuyente->Identificador;
        $peticion = RequestApp::where('identificador', $identificador)->first();
        $peticion->request = $info;
        $peticion->completado = false;
        $peticion->save();

        if ($json->Solicitud->Resumen->Resultado->Documentos == 0){
            $peticion->completado = true;
            $peticion->save();
            $client = new Client(new Version1X('https://calm-plateau-72045.herokuapp.com'));
            //$client = new Client(new Version1X('http://localhost:3000'));
            $client->initialize();
            $client->emit('no_documents', ['data' => $peticion->request]);
            $client->close();
            return 0;
        }

        $client = new Client(new Version1X('https://calm-plateau-72045.herokuapp.com'));
        //$client = new Client(new Version1X('http://localhost:3000'));
        $client->initialize();
        $client->emit('request_updated', ['data' => $info]);
        $client->close();

        $this->guardarFacturas($identificador);
    }


    public function pruebaWebhook($identificador){
        $this->guardarFacturas($identificador);
    }

    public function guardarFacturas($identificador)
    {
        //Ocupar esto cuando se desean hacer pruebas. CAMBIAR METODO A GET
        /*
        $peticion = RequestApp::all()->last();
        $json = json_decode($peticion->request['data']);
        $identificador = $json->Contribuyente->Identificador;
        $peticion = RequestApp::where('identificador', $identificador)->first();
        $json = json_decode($peticion->request['data']);
        */

        //Para hacer la petición de descarga y guardado de facturas
        $identificador_request = $identificador;
        $peticion = RequestApp::where('identificador', $identificador_request)->first();
        $json = json_decode($peticion->request['data']);

        //1. DOWNLOAD THE FILE
        $link_download = $json->Solicitud->Resumen->Archivo;
        $password = $json->Solicitud->Resumen->Password;
        $identificador = $json->Contribuyente->Identificador;
        $archivo = $json->Solicitud->Resumen->Archivo;
        $rfc = substr($archivo, strpos($archivo, $identificador),  strlen($archivo));
        $rfc = str_replace($identificador, "", $rfc);
        $rfc = str_replace(".zip", "", $rfc);
        $rfc = str_replace("_", "", $rfc);
        $path = public_path() . "/descargas/$identificador/";

        file_put_contents(public_path() . "/descargas/$identificador.zip", fopen($link_download, 'r'));
        //2. UNZIP THE FILE ON SERVER
        $zip = new  \ZipArchive();
        $zip_status = $zip->open(public_path() . "/descargas/$identificador.zip");

        if ($zip_status === true)
        {
            if ($zip->setPassword($password))
            {
                for( $i = 0 ; $i < $zip->numFiles ; $i++ ) {
                    if (\DateTime::createFromFormat('Y-m/', $zip->getNameIndex($i)) !== FALSE) {
                      $fecha_folder = $zip->getNameIndex($i);
                    }
                    $size = strlen($zip->getNameIndex($i));
                    if ( $size > 8)  {
                        $zip->extractTo($path, array($zip->getNameIndex($i)));
                    }
                }
            }
            $zip->close();
        }
        else
        {
            dd("Failed opening archive: ". @$zip->getStatusString() . " (code: ". $zip_status .")");
        }

        File::delete(public_path() . "/descargas/$identificador.zip");
        //3. VERIFY IF THE SERVER HAS ALREADY THE XML IF SO THEN VERIFY IF THE STATUS HAS CHANGED
        $files = File::allFiles(public_path() . "/descargas/$identificador/");
        foreach ($files as $key => $file) {
            //Para no perder el valor de la variable (SUPER RARO)
            //$peticion = RequestApp::where('identificador', Input::get('identificador'))->first();
            //$peticion = RequestApp::where('identificador', $identificador)->first();
            //$json = json_decode($peticion->request['data']);

            //Han pasado más de 100 facturas, se debe poner un timer para que descanse el servidor Dreamhost
            if ($key % 100 == 0){
                usleep(2000000);
            }
            $extension = File::extension($file->getFilename());
            if ($extension == 'xml') {
                $contents = File::get($file);
                $xml = new \SimpleXMLElement($contents);
                $complemento = $xml->children('cfdi', true)->Complemento->children('tfd', true)->attributes();
                $uuid = (string)$complemento['UUID'];
                
                //Algunas facturas tenian un caracter raro al final y por eso se implemento esta parte de codigo
                $date = (string)$xml['fecha'];
                $last_char_date = substr($date, -1);
                if (ctype_alpha($last_char_date)) {
                    $date = substr($date, 0, -1);
                }
                $fecha = Carbon::createFromFormat('Y-m-d\TH:i:s', $date);

                //Seleccionamos un nombre único para la factura
                //Si pasa entonces la request viene de la carga manual de facturas
                if(strpos($file->getRealPath(), '/private/var/tmp/') !== false){
                    $name = time() . $file->getClientOriginalName();
                    $cliente_id = $request->cliente_id;
                }
                else {
                    $nombre_original = $file->getFileName();
                    $name = time() . $file->getFileName();
                    $cliente_id = Cliente::select('id')->where('rfc', $rfc)->first()->id;
                }
                //Verificar si ya esta en la base de datos
                if (Factura::existe($uuid)->count() == 0) {
                    $factura = XML::createFactura($xml, $name, $cliente_id, $fecha, $key);
                    
                    if ($factura['rfcDeEmisor'] == $rfc) {
                        //Factura emitida
                        $factura = array_add($factura, 'tipoFactura', 1);
                    }
                    //Debería ser factura recibida
                    else {
                        //Factura recibida
                        if ($factura['rfcDeReceptor'] == $rfc) {
                            $factura = array_add($factura, 'tipoFactura', 0);
                        }
                        //Factura no pertenece a este cliente
                        else {
                            echo "Factura no pertece a cliente";
                        }
                    }
                    $user = Cliente::findOrFail($cliente_id)->user;
                    $factura_nueva = $user->facturas()->create($factura);
                    //Agregar nueva factura
                    //Guardamos en el sistema de archivos del servidor
                    Storage::move("/descargas/$identificador/$fecha_folder" . $nombre_original, "/facturas_clientes/$name");
                    usleep(100000);
                    //$file->move('facturas_clientes', $name);
                }
                else {
                    echo "Factura ya existe: $uuid<br>";
                }
            }
        }

        $peticion->completado = true;
        $peticion->save();

        //3. RUN FUNCTION FOR CREATE FACTURAS
        //SEND TO SOCKET TO SEND TO THE CLIENT THE DOWNLOAD HAS FINISHED
        $client = new Client(new Version1X('https://calm-plateau-72045.herokuapp.com'));
        //$client = new Client(new Version1X('http://localhost:3000'));
        $client->initialize();
        $client->emit('new', ['data' => $peticion->request]);
        $client->close();

        return "Factutas almacenandas correctamente";
    }
}