<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('code_facture')->unique();
            $table->bigInteger('mission_id')->unsigned();
            $table->unsignedBigInteger('owner');
            $table->unsignedBigInteger('client');
            $table->string('designation');
            $table->integer('unite')->nullable()->default(0);
            $table->integer('quantite')->nullable()->default(1);
            $table->float('pu_ht');
            $table->float('pu_ttc');
            $table->float('remise')->nullable()->default(0);
            $table->float('total_ht');
            $table->float('total_ttc');
            $table->string('taxe');
            $table->string('description');
            $table->string('mode_reglement');
            $table->string('commantaire')->nullable();
            $table->string('facture')->nullable();

            $table->timestamps();
            $table->foreign('owner')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('client')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('mission_id')->references('id')->on('missions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('factures');
    }
}