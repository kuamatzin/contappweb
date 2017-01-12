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

    $filename = ($request->tipo_consulta == 'Emitidos') ? 'emitidas.txt' : 'recibidas.txt';

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

Route::post('store_request', function(Request $request){
    $peticion = new RequestApp;
    $peticion->cliente_id = $request->cliente_id;
    $peticion->ejercicio_fiscal = $request->anio;
    $peticion->mes = $request->mes;
    $peticion->tipo_consulta = $request->tipo_consulta;
    $peticion->identificador = $request->identificador;
    $peticion->save();

    return "Exito";
});


//Aqui guarda cuando la petición es completada
Route::post('/comprobar', 'FacturaController@webhook');

Route::get('/request', 'FacturaController@guardarFacturas');

Route::auth();

Route::get('/home', 'HomeController@index');