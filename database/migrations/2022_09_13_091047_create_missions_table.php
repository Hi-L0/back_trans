<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('user_id');
            $table->string('matricule');
            $table->bigInteger('nb_colis');
            $table->float('poids');
            $table->string('num_cmra');
            $table->string('num_declaration_transit');
            $table->string('destinataire');
            $table->integer('commis');
            $table->string('photo_chargement');
            $table->string('bon_scaner')->nullable();
            $table->string("num_mrn")->nullable();
            $table->string('bl_maritime')->nullable();
            $table->string('matricule_european')->nullable();
            $table->string('photo_dechargement')->nullable();

            $table->integer('etat')->default(1);
            $table->foreign('user_id')->references('id')->on('agents')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');

            $table->softDeletes();
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
        Schema::dropIfExists('missions');
    }
}