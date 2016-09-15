<?php

namespace App\Http\Controllers;

use App\Categoria;
use App\Cliente;
use App\Factura;
use App\Http\Requests;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function show(Categoria $categoria, Request $request)
    {
        if ($request->ajax()) {
            return $categoria;
        }
        $emisores = $categoria->emisores;
        $facturas = [];
        if (sizeof($emisores) > 0) {
            $facturas = Factura::where('cliente_id', $categoria->cliente_id)->where(function ($query) use($emisores) {
                foreach($emisores as $emisor) {
                    $query->orWhere('rfcDeEmisor', $emisor['rfc']);
                }
            })->get();
        }
        
        return view('categorias.show', compact('categoria', 'facturas'));
    }

    public function store(Request $request)
    {
        $cliente = Cliente::findOrFail($request->cliente_id);

        $request['emisores'] = [];

        $cliente->categorias()->create($request->all());
    }

    public function update(Request $request)
    {
        $categoria = Categoria::findOrFail($request->categoria_id);
        $emisores = $categoria->emisores;

        //Editar nombre de Categoria
        if ($request->editarCategoria) {
            $categoria->nombre = $request->nombre;
            $categoria->save();
            return $categoria;
        }

        //Editar emisor (nombre y rfc)
        if ($request->editarEmisor) {
            $key = array_search($request->rfcOriginal, array_column($emisores, 'rfc'));
            $emisores[$key]['rfc'] = $request->rfcEditar;
            $emisores[$key]['nombre'] = $request->nombreEditar;
            $categoria->emisores = $emisores;
            $categoria->save();
            return $categoria;
        }

        //Eliminar Emisor
        if ($request->eliminarEmisor) {
            $key = array_search($request->rfcEliminar, array_column($emisores, 'rfc'));
            array_splice($emisores, $key, 1);
            $categoria->emisores = $emisores;
            $categoria->save();
            return $categoria;
        }
        
        //Agregar emisor a categoría
        $key = array_search($request->rfc, array_column($emisores, 'rfc'));
        //RFC no ha sido agregado antes a los emisores
        if ($key === false) {
            $emisor = [
                'nombre' => $request->nombre,
                'rfc' => $request->rfc
            ];
            
            array_push($emisores, $emisor);
            $categoria->emisores = $emisores;
            $categoria->save();
            return $emisor;
        }
        else {
            return 0;
        }
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->delete();
    }
}
