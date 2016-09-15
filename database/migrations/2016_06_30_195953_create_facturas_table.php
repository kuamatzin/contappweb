<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('cliente_id')->unsigned();
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->integer('tipoFactura');
            $table->string('noCertificado');
            $table->string('condicionesDePago');
            $table->string('descuento');
            $table->string('folio');
            $table->string('serie');
            $table->text('sello');
            $table->text('certificado');
            $table->dateTime('fecha');
            $table->string('lugarExpedicion');
            $table->string('tipoDeComprobante');
            $table->string('moneda');
            $table->string('formaDePago');
            $table->string('metodoDePago');
            $table->double('subTotal');
            $table->double('total');
            $table->string('version');
            //Emisor
            $table->string('rfcDeEmisor');
            $table->string('nombreDeEmisor');
            $table->string('calleDeEmisor');
            $table->string('noExteriorDeEmisor');
            $table->string('noInteriorDeEmisor');
            $table->string('coloniaDeEmisor');
            $table->string('localidadDeEmisor');
            $table->string('municipioDeEmisor');
            $table->string('estadoDeEmisor');
            $table->string('paisDeEmisor');
            $table->string('codigoPostalDeEmisor');
            $table->string('regimenDeEmisor');
            //Receptor
            $table->string('rfcDeReceptor');
            $table->string('nombreDeReceptor');
            $table->string('calleDeReceptor');
            $table->string('noExteriorDeReceptor');
            $table->string('noInteriorDeReceptor');
            $table->string('coloniaDeReceptor');
            $table->string('localidadDeReceptor');
            $table->string('municipioDeReceptor');
            $table->string('estadoDeReceptor');
            $table->string('paisDeReceptor');
            $table->string('codigoPostalDeReceptor');
            //Conceptos
            $table->text('conceptos');
            //TODO ESTO ES DINAMICO
            //Impuestos
            $table->double('totalImpuestosRetenidos');
            $table->double('totalImpuestosTrasladados');
            //Retenciones
            $table->text('impuestos_retenidos');
            //Traslados
            $table->text('impuestos_trasladados');
            //Complementos
            $table->string('uuid')->unique();
            $table->date('fechaTimbrado');
            $table->text('selloCFD');
            $table->string('noCertificadoSAT');
            $table->text('selloSAT');
            $table->string('version_complemento');
            //Archivos
            $table->string('ruta');
            $table->string('archivo_word');
            $table->string('archivo_pdf');
            $table->boolean('convertido');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('facturas');
    }
}
