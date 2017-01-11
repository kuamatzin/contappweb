@extends('app')
@section('content')
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="container">
            <div class="row">
                <div class="col-md-11">
                    <ul class="listrap">
                        <li>
                            <div class="listrap-toggle">
                                <span></span>
                                <img src="http://image.flaticon.com/icons/svg/123/123415.svg" class="img-circle" />
                            </div>
                            <strong>Mis clientes</strong>
                            <a href="/clientes/create" class="pull-right">
                                <button type="button" class="btn btn-success">Agregar cliente <i class="fa fa-plus-circle" aria-hidden="true"></i></button>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nombre</th>
                        <th>RFC</th>
                        <th>Administrar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clientes as $key => $cliente)
                    <tr>
                        <td>{{$key+1}}</td>
                        <td>{{$cliente->nombre}}</td>
                        <td>{{$cliente->rfc}}</td>
                        <td>
                            <a href="/clientes/{{$cliente->id}}">
                                <button type="button" class="btn btn-primary">Administrar <i class="fa fa-pencil" aria-hidden="true"></i></button>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@endsection