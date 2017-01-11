<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->string('ejercicio_fiscal')->after('id');
            $table->string('mes')->after('id');
            $table->string('identificador')->after('id');
            $table->integer('cliente_id')->unsigned()->after('id');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->string('tipo_consulta')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn(['ejercicio_fiscal', 'mes', 'identificador', 'tipo_consulta']);
            $table->dropForeign('requests_cliente_id_foreign');
        });
    }
}
