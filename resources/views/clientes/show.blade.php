@extends('app')
@section('content')
<div id="contadores">
    <div class="row">
        <div class="col-lg-6">
            <div class="media">
                <a class="pull-left" href="#">
                    <img class="media-object dp img-circle" src="http://image.flaticon.com/icons/svg/129/129522.svg" style="width: 100px;height:100px;">
                </a>
                <div class="media-body">
                    <h4 class="media-heading">{{$cliente->nombre}}</h4>
                    <h5>{{$cliente->rfc}}</h5>
                    <hr style="margin:8px auto">
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-success">
        <div class="panel-heading">
            <div class="container">
                <div class="row">
                    <ul class="listrap">
                        <li>
                            <div class="listrap-toggle">
                                <span></span>
                                <img src="http://image.flaticon.com/icons/svg/148/148764.svg" class="img-circle" />
                            </div>
                            <strong>Agregar Facturas</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="panel-body">
            {!! Form::open(['method' => 'POST', 'class' => 'dropzone', 'url' => 'facturas', 'id' => 'facturas']) !!}
                {!! Form::hidden('cliente_id', $cliente->id) !!}
            {!! Form::close() !!}
        </div>
    </div>
    <br><br>

    <div class="panel panel-success">
        <div class="panel-heading">
            <div class="container">
                <div class="row">
                    <ul class="listrap">
                        <li>
                            <div class="listrap-toggle">
                                <span></span>
                                <img src="http://image.flaticon.com/icons/svg/148/148764.svg" class="img-circle" />
                            </div>
                            <strong>Descargar Facturas</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div class="row" v-if="descargar_sat_form">
                <div class="col-md-3">
                    <div class="form-group{{ $errors->has('ejercicio_fiscal') ? ' has-error' : '' }}">
                        {!! Form::label('ejercicio_fiscal', 'Ejercicio Fiscal') !!}
                        {!! Form::select('ejercicio_fiscal', [2016 => '2016', 2017 => '2017'], 2016, ['id' => 'ejercicio_fiscal', 'class' => 'form-control', 'v-model' => 'd_ejercicio_fiscal']) !!}
                        <small class="text-danger">{{ $errors->first('ejercicio_fiscal') }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group{{ $errors->has('mes') ? ' has-error' : '' }}">
                        {!! Form::label('mes', 'Mes') !!}
                        {!! Form::select('mes', ['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'], '01', ['id' => 'mes', 'class' => 'form-control', 'v-model' => 'd_mes']) !!}
                        <small class="text-danger">{{ $errors->first('mes') }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group{{ $errors->has('consulta') ? ' has-error' : '' }}">
                        {!! Form::label('consulta', 'Tipo Consulta') !!}
                        {!! Form::select('consulta', ['Recibidos' => 'Recibidas', 'Emitidos' => 'Emitidas'], 'Recibidos', ['id' => 'consulta', 'class' => 'form-control', 'v-model' => 'd_consulta']) !!}
                        <small class="text-danger">{{ $errors->first('consulta') }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                        {!! Form::label('password', 'Password') !!}
                        {!! Form::password('password', ['class' => 'form-control', 'required' => 'required', 'v-model' => 'd_password']) !!}
                        <small class="text-danger">{{ $errors->first('password') }}</small>
                    </div>
                    <br>
                    <button type="button" class="btn btn-warning" v-on:click="descargarSat">Descargar</button>
                </div>
            </div>
            <div class="row" v-if="!descargar_sat_form">
                <img src="http://www.nameacronym.net/images/loading.gif" alt="" class="img-responsive center-block">
                <h3 v-if="descargar_sat_text">Descarga completada, almacenando facturas en el sistema...</h3>
            </div>
        </div>
    </div>

    <br><br>
    <div class="panel panel-warning">
        <div class="panel-heading">
            <div class="container">
                <div class="row">
                    <ul class="listrap">
                        <li>
                            <div class="listrap-toggle">
                                <span></span>
                                <img src="http://image.flaticon.com/icons/svg/148/148955.svg" />
                            </div>
                            <strong>Mis categorías</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <a class="btn btn-warning" data-toggle="modal" href='#agregar_categoria'>Agregar Categoría</a>
            <hr>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Nombre</th>
                            <th>Administrar</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cliente->categorias as $key => $categoria)
                        <tr id="tr_{{$categoria->id}}">
                            <td>{{$key+1}}</td>
                            <td id="name_{{$categoria->id}}">{{$categoria->nombre}}</td>
                            <td>
                                <a class="btn btn-info" data-toggle="modal" href='/categorias/{{$categoria->id}}'>Administrar</a>
                            </td>
                            <td>
                                <a class="btn btn-warning" data-toggle="modal" href='#editar-categoria' v-on:click="editar({{$categoria->id}})">Editar</a>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger" v-on:click="eliminar({{$categoria->id}})">Eliminar</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <br><br>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="container">
                <div class="row">
                    <ul class="listrap">
                        <li>
                            <div class="listrap-toggle">
                                <span></span>
                                <img src="http://image.flaticon.com/icons/svg/148/148928.svg" />
                            </div>
                            <strong>Buscador</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-1">
                    <p>E. Fiscal</p>
                    <select class="form-control" v-model="ejercicio_fiscal">
                        <option value="2013">2013</option>
                        <option value="2014">2014</option>
                        <option value="2015">2015</option>
                        <option value="2016" selected>2016</option>
                    </select>
                    <!--
                    <p>Filtrar por fecha</p>
                    <input type="hidden" value="{{$cliente->id}}" v-model="cliente_id">
                    <input type="text" class="form-control" id="daterange">
                    -->
                </div>
                <div class="col-md-2">
                    <p>Mes</p>
                    <select class="form-control" required="required" v-model="mes">
                        <option value="01" selected>Enero</option>
                        <option value="02">Febrero</option>
                        <option value="03">Marzo</option>
                        <option value="04">Abril</option>
                        <option value="05">Mayo</option>
                        <option value="06">Junio</option>
                        <option value="07">Julio</option>
                        <option value="08">Agosto</option>
                        <option value="09">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <p>Filtrar por RFC</p>
                    <input type="text" name="rfcBusqueda" id="inputRfcDeEmisor" class="form-control" placeholder="Buscar por RFC" v-model="rfcBusqueda">
                </div>
                <div class="col-md-3">
                    <p>Filtrar por tipo</p>
                    <select class="form-control" v-model="tipo">
                        <option value="indiferente" selected>Indiferente</option>
                        <option value="0">Recibidas</option>
                        <option value="1">Emitidas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <p>Filtar por Ingreso/Egreso</p>
                    <select class="form-control" v-model="comprobante">
                        <option value="indiferente" selected>Indiferente</option>
                        <option value="ingreso">Ingreso</option>
                        <option value="egreso">Egreso</option>
                    </select>
                    <br>
                </div>
                <div class="row">
                    <div class="col-md-6 col-md-offset-6">
                        <div class="row">
                            <div class="col-md-4">
                                <button type="button" class="btn btn-success" v-on:click="descargarReporte" download>Generar Excel &nbsp;&nbsp;<i class="fa fa-file-excel-o" aria-hidden="true"></i></button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-warning" v-on:click="descargarFacturas" download>Descargar Carpeta&nbsp;&nbsp;<i class="fa fa-file-excel-o" aria-hidden="true"></i></button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-primary" v-on:click="busquedaFacturas">Buscar &nbsp;&nbsp;<i class="fa fa-search" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="container">
                <div class="row">
                       <div id="custom-search-input">
                        <div class="input-group col-md-5">
                            <input type="text" class="  search-query form-control" placeholder="Filtrar" />
                            <span class="input-group-btn">
                                <button class="btn btn-danger" type="button">
                                    <span class=" glyphicon glyphicon-search"></span>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
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
                            <th>Ver</th>
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
                                    <img src="http://image.flaticon.com/icons/svg/136/136526.svg" width="40px">
                                </a>
                            </td>
                            <td>
                                <a href="/facturas/descargarPDF/@{{factura.id}}">
                                    <img src="http://image.flaticon.com/icons/svg/179/179483.svg" width="40px">
                                </a>
                            </td>
                            <td>
                                <img src="http://image.flaticon.com/icons/svg/123/123377.svg" width="40px" data-toggle="modal" href='#ver-factura' v-on:click="verFactura(factura)" style="cursor: pointer">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <h1 class="text-center" v-show="sin_resultados">Búsqueda sin resultados</h1>
                <br>
            </div>
        </div>
    </div>
    <!-- VER FACTURA -->
    <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" id="ver-factura">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title">@{{detalle_factura.nombreDeEmisor}}</h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel panel-primary">
                                        <div class="panel-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover">
                                                            <tbody>
                                                                <tr>
                                                                    <td>RFC:</td>
                                                                    <td>@{{detalle_factura.rfcDeEmisor}}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Dirección:</td>
                                                                    <td>@{{detalle_factura.calleDeEmisor}}, @{{detalle_factura.noExteriorDeEmisor}} @{{detalle_factura.noInteriorDeEmisor}}, @{{detalle_factura.coloniaDeEmisor}}, @{{detalle_factura.localidadDeEmisor}}, @{{detalle_factura.municipioDeEmisor}} @{{detalle_factura.estadoDeEmisor}} @{{detalle_factura.paisDeEmisor}}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Expedido en:</td>
                                                                    <td>@{{detalle_factura.lugarExpedicion}}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Regimen Fiscal</td>
                                                                    <td>@{{detalle_factura.regimenDeEmisor}}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover">
                                                            <tbody>
                                                                <tr>
                                                                    <td>Folio Fiscal</td>
                                                                    <td>@{{detalle_factura.uuid}}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Fecha</td>
                                                                    <td>@{{detalle_factura.fecha}}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Serie</td>
                                                                    <td>@{{detalle_factura.serie}}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Folio</td>
                                                                    <td>@{{detalle_factura.folio}}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Forma de pago</td>
                                                                    <td>@{{detalle_factura.formaDePago}}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Método de pago</td>
                                                                    <td>@{{detalle_factura.metodoDePago}}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>No. Cuenta</td>
                                                                    <td>@{{detalle_factura.cuenta}}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel panel-primary">
                                        <div class="panel-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <tbody>
                                                        <tr>
                                                            <td>Cliente</td>
                                                            <td>@{{detalle_factura.nombreDeReceptor}}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>RFC</td>
                                                            <td>@{{detalle_factura.rfcDeReceptor}}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Dirección</td>
                                                            <td>@{{detalle_factura.calleDeReceptor}}, @{{detalle_factura.noExteriorDeReceptor}} @{{detalle_factura.noInteriorDeReceptor}}, @{{detalle_factura.coloniaDeReceptor}}, @{{detalle_factura.localidadDeReceptor}}, @{{detalle_factura.municipioDeReceptor}} @{{detalle_factura.estadoDeReceptor}} @{{detalle_factura.paisDeReceptor}}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Cantidad</th>
                                                    <th>Descripción</th>
                                                    <th>Unidad</th>
                                                    <th>P. Unitario</th>
                                                    <th>Importe</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="concepto in detalle_factura.conceptos">
                                                    <td>@{{concepto.cantidad}}</td>
                                                    <td>@{{concepto.descripcion}}</td>
                                                    <td>@{{concepto.unidad}}</td>
                                                    <td>@{{concepto.valorUnitario}}</td>
                                                    <td>@{{concepto.importe}}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="panel panel-primary">
                                        <div class="panel-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <tbody>
                                                        <tr>
                                                            <td>Moneda</td>
                                                            <td>@{{detalle_factura.moneda}}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Tipo de Cambio</td>
                                                            <td>@{{detalle_factura.tipoDeCambio}}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-md-offset-2">
                                    <div class="panel panel-primary">
                                        <div class="panel-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <tbody>
                                                        <tr>
                                                            <td>Subtotal</td>
                                                            <td>@{{detalle_factura.subTotal}}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Descuento</td>
                                                            <td>@{{detalle_factura.descuento}}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Impuestos Retenidos</td>
                                                            <td>@{{detalle_factura.totalImpuestosRetenidos}}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Impuestos Trasladados</td>
                                                            <td>@{{detalle_factura.totalImpuestosTrasladados}}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Total</td>
                                                            <td>@{{detalle_factura.total}}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- MODALS -->
    <div class="modal fade" id="agregar_categoria">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Agregar Categoría</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group{{ $errors->has('nombre') ? ' has-error' : '' }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" id="cliente_id" value="{{$cliente->id}}" v-model="cliente_id">
                        {!! Form::label('nombre', 'Nombre') !!}
                        {!! Form::text('nombre', null, ['class' => 'form-control', 'required' => 'required', 'v-model' => 'nombreCategoria']) !!}
                        <small class="text-danger">{{ $errors->first('nombre') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="guardarEmisor" v-on:click="crearCategoria">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editar-categoria">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Editar Categoría</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group{{ $errors->has('nombre') ? ' has-error' : '' }}">
                        {!! Form::label('nombre', 'Nombre') !!}
                        {!! Form::text('nombre', null, ['class' => 'form-control', 'required' => 'required', 'v-model' => 'nombreCategoriaEditar']) !!}
                        <small class="text-danger">{{ $errors->first('nombre') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" v-on:click="guardarEditar">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/1.7.2/socket.io.js"></script>
<script>

    var socket = io.connect("https://calm-plateau-72045.herokuapp.com");
    //var socket = io.connect("http://localhost:3000");

    socket.on('saludo', function(msg){
        //console.log(msg.data)
        var data = JSON.parse(msg.data.data);
        var descargas = data.Solicitud.Resumen.Resultado.Descargados;
        var documentos = data.Solicitud.Resumen.Resultado.Documentos;
        var vigentes = data.Solicitud.Resumen.Resultado.Vigentes;
        var cancelados = data.Solicitud.Resumen.Resultado.Cancelados;
        var acuses = data.Solicitud.Resumen.Resultado.Acuses;
        vm.descargar_sat_form = true,
        vm.descargar_sat_text = false
        if (vm.identificador == data.Contribuyente.Identificador) {
            swal({
              title: "Solicitud Completada",
              text: "<p><strong>Documentos: " + documentos + "</strong></p>" + "<p><strong>Descargados: " + descargas + "</strong></p>" + "<p><strong>Acuses: " + acuses + "</strong></p>" + "<p><strong>Cancelados: " + cancelados + "</strong></p>" + "<p><strong>Vigentes: " + vigentes + "</strong></p>",
              html: true
            });
        }
    });

    socket.on('no_docs', function(msg){
        //console.log(msg.data)
        var data = JSON.parse(msg.data.data);
        //console.log(data)
        console.log(data.Contribuyente.Identificador)
        console.log(vm.identificador)
        vm.descargar_sat_form = true,
        vm.descargar_sat_text = false
        if (vm.identificador == data.Contribuyente.Identificador) {
            swal("No se encontraron facturas");
        }
    });

    Dropzone.options.facturas = {
      paramName: 'factura',
      acceptedFiles: '.xml',
      dictDefaultMessage: 'Agrega aquí tus facturas',
      parallelUploads: 1
    };

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
            categoria_id_editar: '',
            cliente_id: '',
            rfc_cliente: '{{$cliente->rfc}}',
            nombreCategoria: '',
            nombreCategoriaEditar: '',
            rfcBusqueda: '',
            tipo: '',
            start: moment().format('YYYY-MM-DD'),
            end: moment().format('YYYY-MM-DD'),
            detalle_factura: '',
            descargar_sat_form: true,
            descargar_sat_text: false,
            identificador: '',
            sin_resultados: false
        },
        ready: function(){
            
        },
        methods: {
            editar: function(id){
                var that = this;
                this.$http.get('/categorias/' + id).then(function(category){
                    that.categoria_id_editar = category.data.id;
                    that.nombreCategoriaEditar = category.data.nombre;
                }, function(error){
                    console.log(error)
                });
            },
            guardarEditar: function(){
                var data = { categoria_id: this.categoria_id_editar ,nombre: this.nombreCategoriaEditar, editarCategoria: true }
                this.$http.put('/categorias/update', data).then(function(response){
                    if (response.status == 200) {
                        $('#name_' + this.categoria_id_editar).html(response.data.nombre);
                        $('#editar-categoria').modal('hide');
                    }
                }, function(){

                });
            },
            eliminar: function(id){
                var that = this;
                swal({
                    title: '¿Estas segur@?',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Estoy segur@",
                    closeOnConfirm: true
                }, function(){
                    that.$http.delete('/categorias/' + id).then(function(response){
                        if (response.status == 200) {
                            $('#tr_' + id).hide('slow/400/fast');
                        }
                    }, function(){

                    })
                });
            },
            crearCategoria: function(){
                var that = this;
                var data = {
                    nombre: this.nombreCategoria,
                    cliente_id: this.cliente_id
                };
                this.$http.post('/categorias', data).then(function(response){
                    //Respuesta exitosa
                    if (response.status == 200) {
                        that.nombreCategoria = '';
                        $('#agregar_categoria').modal('hide');
                        location.reload();
                        //Agregar al DOM nueva categoría
                    }
                }, function(error){

                });
            },
            busquedaFacturas: function(){
                var that = this;
                this.$http.get('/facturas/busquedaFacturas/' + this.cliente_id + '?ejercicio_fiscal=' + this.ejercicio_fiscal + '&mes=' + this.mes + '&rfcBusqueda=' + this.rfcBusqueda + '&tipo=' + this.tipo + '&comprobante=' + this.comprobante + '&excel=0').then(function(facturas){
                    if (facturas.data.length == 0) {
                        that.facturas = '';
                        that.sin_resultados = true;
                    } 
                    else {
                        that.facturas = facturas.data;
                        that.sin_resultados = false;
                    }
                }, function(error){
                    console.log(error)
                });
            },
            setDate: function(start, end){
                this.start = start.format('YYYY-MM-DD')
                this.end = end.format('YYYY-MM-DD')
            },
            verFactura: function(factura){
                this.detalle_factura = factura;
            },
            descargarReporte: function(){
                var that = this;
                this.$http.get('/facturas/busquedaFacturas/' + this.cliente_id + '?ejercicio_fiscal=' + this.ejercicio_fiscal + '&mes=' + this.mes + '&rfcBusqueda=' + this.rfcBusqueda + '&tipo=' + this.tipo + '&comprobante=' + this.comprobante + '&excel=1').then(function(facturas){
                    window.location.href = 'http://contador.dev/reportes/reporteFacturas.xlsx';
                }, function(error){
                    console.log(error)
                });
            },
            descargarFacturas: function(){
                var that = this;
                this.$http.get('/facturas/busquedaFacturas/' + this.cliente_id + '?ejercicio_fiscal=' + this.ejercicio_fiscal + '&mes=' + this.mes + '&rfcBusqueda=' + this.rfcBusqueda + '&tipo=' + this.tipo + '&comprobante=' + this.comprobante + '&zip=1').then(function(facturas){
                    window.location.href = 'http://contador.dev/' + this.rfc_cliente + '_' + this.mes + '.zip';
                }, function(error){
                    console.log(error)
                });
            },
            descargarSat: function(){
                var that = this;
                if (this.d_password == '') {
                    sweetAlert("Oops...", "Ingresa la contraseña", "error");
                    return 0;
                }
                var data = {
                    anio: this.d_ejercicio_fiscal,
                    mes: this.d_mes,
                    tipo_consulta: this.d_consulta,
                    rfc: this.rfc_cliente,
                    password: this.d_password
                };
                this.$http.post('/descargar', data).then(function(response){
                    that.descargar_sat_form = false;
                    //Respuesta exitosa
                    if (response.status == 200) {
                        var data = JSON.parse(response.data);
                        var status = data.Respuesta.Status;
                        var error = data.Respuesta.Error;
                        var id = data.Contribuyente.Identificador;
                        that.identificador = id;
                        if (error) {
                            console.log(error);
                            that.descargar_sat_form = true;
                        }
                        //Si no hay error se procede a comprobar el estadod e la solicitud
                        else {
                            if (id) {
                                //Esperamos para darle chance al web service de procesar la solicitud
                                setTimeout(function() {
                                    that.$http.get('/consulta?id=' + id + '&rfc=' + that.rfc_cliente).then(function(response_consulta){
                                        if (response_consulta.status == 200) {
                                            var data = JSON.parse(response_consulta.data);
                                            if (data.Solicitud.Error.Numero == 0) {
                                                sweetAlert("Oops...", "Constraseña Incorrecta", "error");
                                                that.descargar_sat_form = true;
                                            }
                                            if (data.Solicitud.Resumen.Resultado) {
                                                that.descargar_sat_text = true;
                                            }
                                        }
                                    }, function(error){

                                    });
                                }, 9000);
                            }
                        }
                    }
                }, function(error){

                });
            }
        }
    })
</script>
@endsection