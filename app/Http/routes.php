<?php

use App\Cliente;
use App\Factura;
use App\Request as RequestApp;
use App\Services\ExcelGenerator;
use App\Services\FacturaPDF;
use App\Services\XML;
use Carbon\Carbon;
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;

Route::singularResourceParameters();

Route::get('facturas/busquedaFacturas/{cliente_id}', 'FacturaController@busqueda');

Route::get('facturas/descargarPDF/{factura_id}', 'FacturaController@descargarPDF');

Route::resource('facturas', 'FacturaController');

Route::resource('clientes', 'ClienteController');

Route::resource('categorias', 'CategoriaController');

Route::get('/', function () {
    return redirect('clientes');
});

Route::post('/descargar', function(Request $request){
    header('Content-type: text/html; charset=utf-8');

    $filename = ($request->tipo_consulta == 'Emitidos') ? 'emitidas.txt' : 'rexibidas.txt';

    $jsonText = file_get_contents($filename);

    $json = json_decode($jsonText);

    if (json_last_error() != JSON_ERROR_NONE)
    {
      dd("Error en json");
    }
    
    //$json->{"Contribuyente"}->{'Rfc'} = 'CUHC901208KQ8';
    //$json->{"Contribuyente"}->{'ClaveCiec'} = '11235813';
    $json->{"Contribuyente"}->{'Rfc'} = $request->rfc;
    $json->{"Contribuyente"}->{'ClaveCiec'} = $request->password;
    if ($request->tipo_consulta == 'Emitidos') {
        $json->{'FiltradoRangoEmitidos'}->{'FechaInicial'} = $request->anio . '-' . $request->mes . '-1 00:00:00';
        $json->{'FiltradoRangoEmitidos'}->{'FechaFinal'} = $request->anio . '-' . $request->mes . '-' . cal_days_in_month(CAL_GREGORIAN, $request->mes, $request->anio)  . ' 23:59:59';
    }
    else {
        $json->{'FiltradoRangoRecibidos'}->{'Anio'} = $request->anio;
        $json->{'FiltradoRangoRecibidos'}->{'Mes'} = $request->mes;
    }
    
    $json->{'Configuracion'}->{'TipoConsulta'} = $request->tipo_consulta;

    $post['data'] = json_encode($json);

    $postdata = http_build_query($post);

    $server = 'http://www.descargarcfdi.com/descarga';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $server);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $respuesta = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $respuesta;
});

//Aqui se lanza la request para que descargue
Route::get('/consulta', function(){
    header('Content-type: text/html; charset=utf-8');

    $filename = 'consulta.txt';

    $jsonText = file_get_contents($filename);
    $identificador = Input::get('id');
    $rfc = Input::get('rfc');
    
    $json = json_decode($jsonText);

    if (json_last_error() != JSON_ERROR_NONE)
    {
      dd("Error en json");
    }
    $json->{"Contribuyente"}->{'Rfc'} = $rfc;
    $json->{"Contribuyente"}->{'Identificador'} = $identificador;

    $post['data'] = json_encode($json);
    $postdata = http_build_query($post);

    $server = 'http://www.descargarcfdi.com/consulta';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $server);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $respuesta = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $respuesta;
});

//Aqui guarda cuando la petición es completada
Route::post('comprobar', function(Request $request){
    $peticion = new RequestApp;
    $peticion->request = $request->all();
    $peticion->save();
    $json = json_decode($peticion->request['data']);
    //SI EN LA RESPUESTA HAY 0 FACTURAS
    
    if ($json->Solicitud->Resumen->Resultado->Documentos == 0){
        $client = new Client(new Version1X('https://calm-plateau-72045.herokuapp.com'));
        //$client = new Client(new Version1X('http://localhost:3000'));
        $client->initialize();
        $client->emit('no_documents', ['data' => $peticion->request]);
        $client->close();
        return 0;
    }
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
    $zip = new ZipArchive();
    $zip_status = $zip->open(public_path() . "/descargas/$identificador.zip");

    if ($zip_status === true)
    {
        if ($zip->setPassword($password))
        {
            for( $i = 0 ; $i < $zip->numFiles ; $i++ ) {
                if (DateTime::createFromFormat('Y-m/', $zip->getNameIndex($i)) !== FALSE) {
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
        $extension = File::extension($file->getFilename());
        if ($extension == 'xml') {
            $contents = File::get($file);
            $xml = new \SimpleXMLElement($contents);
            $complemento = $xml->children('cfdi', true)->Complemento->children('tfd', true)->attributes();
            $uuid = (string)$complemento['UUID'];

            $fecha = Carbon::createFromFormat('Y-m-d\TH:i:s', (string)$xml['fecha']);
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
                $factura = XML::createFactura($xml, $name, $cliente_id, $fecha);
                
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
                //$file->move('facturas_clientes', $name);
            }
            else {
                echo "Factura ya existe";
            }
        }
    }
    //3. RUN FUNCTION FOR CREATE FACTURAS
    //SEND TO SOCKET TO SEND TO THE CLIENT THE DOWNLOAD HAS FINISHED
    $peticion = RequestApp::all()->last();
    $client = new Client(new Version1X('https://calm-plateau-72045.herokuapp.com'));
    //$client = new Client(new Version1X('http://localhost:3000'));
    $client->initialize();
    $client->emit('new', ['data' => $peticion->request]);
    $client->close();
});

Route::get('request', function(){
    $peticion = RequestApp::all()->last();
    $json = json_decode($peticion->request['data']);
    if ($json->Solicitud->Resumen->Resultado->Documentos == 0){
        $client = new Client(new Version1X('https://calm-plateau-72045.herokuapp.com'));
        //$client = new Client(new Version1X('http://localhost:3000'));
        $client->initialize();
        $client->emit('no_documents', ['data' => $peticion->request]);
        $client->close();
        return 0;
    }
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
    $zip = new ZipArchive();
    $zip_status = $zip->open(public_path() . "/descargas/$identificador.zip");

    if ($zip_status === true)
    {
        if ($zip->setPassword($password))
        {
            for( $i = 0 ; $i < $zip->numFiles ; $i++ ) {
                if (DateTime::createFromFormat('Y-m/', $zip->getNameIndex($i)) !== FALSE) {
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
        $extension = File::extension($file->getFilename());
        if ($extension == 'xml') {
            $contents = File::get($file);
            $xml = new \SimpleXMLElement($contents);
            $complemento = $xml->children('cfdi', true)->Complemento->children('tfd', true)->attributes();
            $uuid = (string)$complemento['UUID'];

            $fecha = Carbon::createFromFormat('Y-m-d\TH:i:s', (string)$xml['fecha']);
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
                //$file->move('facturas_clientes', $name);
            }
            else {
                echo "Factura ya existe";
            }
        }
    }
    //3. RUN FUNCTION FOR CREATE FACTURAS
    //SEND TO SOCKET TO SEND TO THE CLIENT THE DOWNLOAD HAS FINISHED
    $client = new Client(new Version1X('https://calm-plateau-72045.herokuapp.com'));
    //$client = new Client(new Version1X('http://localhost:3000'));
    $client->initialize();
    $client->emit('new', ['data' => $peticion->request]);
    $client->close();
});

Route::get('testSocket', function(){
    $peticion = RequestApp::all()->last();
    $client = new Client(new Version1X('https://calm-plateau-72045.herokuapp.com'));
    //$client = new Client(new Version1X('http://localhost:3000'));
    $client->initialize();
    $client->emit('new', ['data' => $peticion->request]);
    $client->close();
});

Route::auth();

Route::get('/home', 'HomeController@index');