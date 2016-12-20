<?php

use App\Factura;
use App\Request as RequestApp;
use App\Services\ExcelGenerator;
use App\Services\FacturaPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;

Route::singularResourceParameters();

Route::get('facturas/busquedaFacturas/{cliente_id}', 'FacturaController@busqueda');

Route::get('facturas/descargarPDF/{factura_id}', 'FacturaController@descargarPDF');

Route::resource('facturas', 'FacturaController');

Route::resource('clientes', 'ClienteController');

Route::resource('categorias', 'CategoriaController');

Route::get('/', function () {
    return redirect('clientes');
});

Route::get('/descargar', function(){
    header('Content-type: text/html; charset=utf-8');

    $filename = 'ejemplo.txt';

    $jsonText = file_get_contents($filename);

    $json = json_decode($jsonText);

    if (json_last_error() != JSON_ERROR_NONE)
    {
      dd("Error en json");
    }

    $json->{"Contribuyente"}->{'Rfc'} = 'CUHC901208KQ8';
    $json->{"Contribuyente"}->{'ClaveCiec'} = '11235813';

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

    dd($respuesta);
});


Route::get('/consulta', function(){
    header('Content-type: text/html; charset=utf-8');

    $filename = 'consulta.txt';

    $jsonText = file_get_contents($filename);
    $identificador = Input::get('id');
    
    $json = json_decode($jsonText);

    if (json_last_error() != JSON_ERROR_NONE)
    {
      dd("Error en json");
    }

    $json->{"Contribuyente"}->{'Rfc'} = 'CUHC901208KQ8';
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
    $respuesta = json_decode($respuesta);
    dd($respuesta->Solicitud);
});

Route::post('comprobar', function(Request $request){
    $request = new RequestApp;
    $request->request = 'HOLA';
    $request->save();

    $request2 = new RequestApp;
    $request2->request = $request->all();
    $request2->save();
});

Route::get('socket', function(){
    $client = new Client(new Version1X('http://localhost:3000'));
    $client->initialize();
    $client->emit('new', ['foo' => 'bar']);
    $client->close();
});

Route::auth();

Route::get('/home', 'HomeController@index');