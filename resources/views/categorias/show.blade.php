@extends('app')
@section('content')
<div id="contadores">
    <h1>{{$categoria->nombre}}</h1>
    <hr>
    <h3>Emisores</h3>
    <a class="btn btn-primary" data-toggle="modal" href='#emisores'>Agregar Emisor</a>
    <hr>
    <div class="table-responsive">
        <table class="table table-hover" id="table-emisores">
            <thead>
                <tr>
                    <th></th>
                    <th>Nombre</th>
                    <th>RFC</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categoria->emisores as $key => $emisor)
                    <tr id="id_{{$emisor['rfc']}}">
                        <td>{{$key+1}}</td>
                        <td id="nombre_{{$emisor['rfc']}}">{{$emisor['nombre']}}</td>
                        <td id="rfc_{{$emisor['rfc']}}">{{$emisor['rfc']}}</td>
                        <td>
                            <a class="btn btn-warning" data-toggle="modal" href='#editar-categoria' name="{{$emisor['nombre']}}" id="{{$emisor['rfc']}}" v-on:click="setEditarEmisor($event)">Editar</a>
                        </td>
                        <td>
                            <a type="button" name="{{$emisor['rfc']}}" class="btn btn-danger" v-on:click="eliminarEmisor($event)">Eliminar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <hr>
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title">Buscador</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-4">
                    <p>Filtrar por fecha</p>
                    <input type="hidden" value="{{$categoria->id}}" v-model="cliente_id">
                    <input type="text" class="form-control" id="daterange">
                </div>
                <div class="col-md-4">
                    <p>Filtrar por RFC</p>
                    <input type="text" name="rfcDeEmisor" id="inputRfcDeEmisor" class="form-control" placeholder="Buscar por RFC" v-model="rfcBusqueda">
                </div>
                <div class="col-md-4">
                    <br>
                    <button type="button" class="btn btn-primary" id="buscar" v-on:click="busquedaFacturas">Buscar</button>
                </div>
            </div>
            <hr>
            <h3>Facturas</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Nombre Emisor</th>
                            <th>RFC Emisor</th>
                            <th>Nombre Receptor</th>
                            <th>RFC Receptor</th>
                            <th>Fecha</th>
                            <th>Descargar XML</th>
                            <th>Descargar PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="factura in facturas">
                            <td>@{{$index + 1}}</td>
                            <td>@{{factura.nombreDeEmisor}}</td>
                            <td>@{{factura.rfcDeEmisor}}</td>
                            <td>@{{factura.nombreDeReceptor}}</td>
                            <td>@{{factura.rfcDeReceptor}}</td>
                            <td>@{{factura.fecha}}</td>
                            <td>
                                <a href="/facturas_clientes/@{{factura.ruta}}" download>
                                    <button type="button" class="btn btn-primary center-block">
                                        <i class="fa fa-file-text" aria-hidden="true"></i>
                                    </button>
                                </a>
                            </td>
                            <td>
                                <a href="/facturas/descargarPDF/@{{factura.id}}">
                                    <button type="button" class="btn btn-success center-block">
                                        <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                                    </button>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODALS -->
    <div class="modal fade" id="emisores">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Agregar Emisor</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" id="categoria_id" value="{{$categoria->id}}">
                    <div class="form-group{{ $errors->has('nombre') ? ' has-error' : '' }}">
                        {!! Form::label('nombre', 'Nombre') !!}
                        {!! Form::text('nombre', null, ['class' => 'form-control', 'required' => 'required', 'v-model' => 'nombre']) !!}
                        <small class="text-danger">{{ $errors->first('nombre') }}</small>
                    </div>
                    <div class="form-group{{ $errors->has('rfc') ? ' has-error' : '' }}">
                        {!! Form::label('rfc', 'RFC') !!}
                        {!! Form::text('rfc', null, ['class' => 'form-control', 'required' => 'required', 'v-model' => 'rfc']) !!}
                        <small class="text-danger">{{ $errors->first('rfc') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="guardarEmisor" v-on:click="guardarEmisor">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editar-categoria">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Editar Emisor</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="categoria_id" value="{{$categoria->id}}">
                    <div class="form-group{{ $errors->has('nombre') ? ' has-error' : '' }}">
                        {!! Form::label('nombre', 'Nombre') !!}
                        {!! Form::text('nombre', null, ['class' => 'form-control', 'required' => 'required', 'v-model' => 'nombreEditar']) !!}
                        <small class="text-danger">{{ $errors->first('nombre') }}</small>
                    </div>
                    <div class="form-group{{ $errors->has('rfc') ? ' has-error' : '' }}">
                        {!! Form::label('rfc', 'RFC') !!}
                        {!! Form::text('rfc', null, ['class' => 'form-control', 'required' => 'required', 'v-model' => 'rfcEditar']) !!}
                        <small class="text-danger">{{ $errors->first('rfc') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" v-on:click="editarEmisor">Guardar</button>
                </div>
            </div>
        </div>
    </div>


    

</div>
@endsection

@section('scripts')
<script>
    $('#daterange').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD'
        },
        startDate: moment().format('YYYY-MM-DD'),
        endDate: moment().format('YYYY-MM-DD')
    },function(start, end, label){
        vm.setDate(start, end);
    });
    Vue.http.headers.common['X-CSRF-TOKEN'] = $('input[name="_token"]').val();
    var vm = new Vue({
        el: "#contadores",
        data: {
            facturas: '',
            categoria_id: '',
            cliente_id: '',
            nombre: '',
            rfc: '',
            rfcBusqueda: '',
            start: moment().format('YYYY-MM-DD'),
            end: moment().format('YYYY-MM-DD'),
            nombreEditar: '',
            rfcEditar: '',
            rfcOriginal: '',
        },
        ready: function(){
            
        },
        methods: {
            guardarEmisor: function(){
                var data = { 
                    categoria_id: $('#categoria_id').val(), 
                    nombre: this.nombre, 
                    rfc: this.rfc
                };
                this.$http.put('/categorias/update', data).then(function(response){
                    if (response.status == 200) {
                        if (response.data == 0) {
                            swal("No se pudo agregar emisor, RFC duplicado", '', 'error');
                        }
                        else {
                            $('#emisores').modal('hide')
                            //$('#table-emisores > tbody:last-child').append('<tr id="id_" ' + response.data.rfc + '><td>3</td><td id="nombre_' + response.data.rfc + '">' + response.data.nombre + '</td><td id="rfc_' + response.data.rfc + '">' + response.data.rfc + '</td><td><a class="btn btn-warning" data-toggle="modal" href="#editar-categoria" name="' + response.data.nombre + '" id="' + response.data.rfc + '" v-on:click="setEditarEmisor($event)">Editar</a></td><td><a type="button" name="' + response.data.rfc + '" class="btn btn-danger" v-on:click="eliminarEmisor($event)">Eliminar</a></td></tr>');
                            location.reload();
                        }
                    }
                }, function(){
                    alert("ERROR")
                });
            },
            setEditarEmisor: function(event){
                this.rfcOriginal = event.currentTarget.id;
                this.nombreEditar = event.currentTarget.name;
                this.rfcEditar = event.currentTarget.id;
            },
            editarEmisor: function(){
                var that = this;
                var data = {
                    categoria_id: $('#categoria_id').val(),
                    editarEmisor: true,
                    rfcOriginal: this.rfcOriginal,
                    rfcEditar: this.rfcEditar,
                    nombreEditar: this.nombreEditar
                };
                this.$http.put('/categorias/update', data).then(function(response){
                    if (response.status == 200) {
                        $('#editar-categoria').modal('hide');
                        $('#rfc_' + that.rfcOriginal).html(that.rfcEditar);
                        $('#nombre_' + that.rfcOriginal).html(that.nombreEditar);
                    }
                }, function(error){
                    console.log(error)
                });
            },
            eliminarEmisor: function(event){
                var rfcEliminar = event.currentTarget.name;
                var that = this;

                var data = {
                    categoria_id: $('#categoria_id').val(),
                    eliminarEmisor: true,
                    rfcEliminar: rfcEliminar,
                };

                swal({
                    title: '¿Estas segur@?',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Estoy segur@",
                    closeOnConfirm: true
                }, function(){
                    that.$http.put('/categorias/update', data).then(function(response){
                        if (response.status == 200) {
                            $('#id_' + rfcEliminar).hide();
                        }
                    }, function(){

                    })
                });
            },
            busquedaFacturas: function(){
                var that = this;
                this.$http.get('/facturas/busquedaFacturas/' + this.cliente_id + '?fechaInicial=' + this.start + '&fechaFinal=' + this.end + '&rfc=' + this.rfcBusqueda + '&emisores=1&categoria_id=' + $('#categoria_id').val()).then(function(facturas){
                    that.facturas = facturas.data;
                }, function(error){
                    console.log(error)
                });
            },
            setDate: function(start, end){
                this.start = start.format('YYYY-MM-DD')
                this.end = end.format('YYYY-MM-DD')
            }
        }
    })
</script>
@endsection