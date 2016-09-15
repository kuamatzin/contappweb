@extends('app')

@section('content')
<h3>Dat de alta nuevo cliente</h3>
<hr>
{!! Form::open(['url' => 'clientes']) !!}
    <div class="row">
        <div class="col-md-4">
            @include('clientes.form', ['submitButtonText' => 'Guardar'])  
        </div>
    </div>
{!! Form::close() !!}
@endsection

@section('scripts')

@endsection