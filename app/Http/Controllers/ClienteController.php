<?php

namespace App\Http\Controllers;

use App\Cliente;
use App\Http\Requests;
use App\Http\Requests\ClienteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $clientes = Auth::user()->clientes;
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(ClienteRequest $request)
    {
        Auth::user()->clientes()->create($request->all());

        return redirect('clientes');
    }

    public function show(Cliente $cliente)
    {   
        return view('clientes.show', compact('cliente'));
    }
}
