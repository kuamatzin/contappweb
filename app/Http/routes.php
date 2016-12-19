<?php

use App\Factura;
use App\Services\ExcelGenerator;
use App\Services\FacturaPDF;

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

    /**
 * Created by Facturando.
 * Date: 2016.03.16 - 11:50 AM
 * Issue:
 *   Ejemplo que muestra como enviar una solicitud de descarga al web service
 */

header('Content-type: text/html; charset=utf-8');

/**
 * Archivo en formato JSON que contiene todos los datos de la petición de descarga
 *
 * Este archivo fue tomado de los ejemplos que trae la librería y que se encuentran
 * en la carpeta
 *   Documentación\Ejemplos
 */
$filename = 'ejemplo.txt';

/**
 * Leemos el archivo
 */
$jsonText = file_get_contents($filename);

/*
 * Convertimos el texto en un objeto JSON
 */
$json = json_decode($jsonText);

/*
 * Verificamos que se haya cargado correctamente
 */
if (json_last_error() != JSON_ERROR_NONE)
{
  echo "Se generó un error al leer el JSON: " . json_last_error_msg();
  return;
}

/**
 * Asignamos el RFC y la Clave CIEC ya que estos valores no están  en el archivo
 * Para pruebas se puede usar el RFC AAA010101AAA, de esta forma la petición
 * es recibida, se válida, pero nunca es procesada.
 *
 * De esta forma se puede probar la generación de JSON y la recepción del mismo
 */

$json->{"Contribuyente"}->{'Rfc'} = 'CUHC901208KQ8';
$json->{"Contribuyente"}->{'ClaveCiec'} = '11235813';

/**
 * Asignamos el objeto JSON a enviar
 * Siempre se debe enviar en una variable llamada data
 */

$post['data'] = json_encode($json);
$postdata = http_build_query($post);

/**
 * El servicio expone 2 métodos
 *
 * 1. Solicitar una descarga
 *        http://www.descargarcfdi.com/descarga
 *
 * 2. Consultar si la descarga solicitada ya fue procesada
 *        http://www.descargarcfdi.com/consulta
 */
$server = 'http://www.descargarcfdi.com/descarga';

/**
 * Ejecutamos la petición
 */
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $server);
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$respuesta = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

/**
 * Mostramos el resultado.
 */
dd($respuesta);
});

Route::auth();

Route::get('/home', 'HomeController@index');