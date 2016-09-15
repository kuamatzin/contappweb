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

Route::auth();

Route::get('/home', 'HomeController@index');