<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('prenom')->after('name');
            $table->string('nom')->after('name');
            $table->string('societe');
            $table->string('gsm')->nullable();
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('pays')->nullable();
            $table->integer('cp')->nullable(); //code posstal
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nom');
            $table->dropColumn('prenom');
            $table->dropColumn('adresse');
            $table->dropColumn('ville');
            $table->dropColumn('pays');
            $table->dropColumn('cp');
        });
    }
}