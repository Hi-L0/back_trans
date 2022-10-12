<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToFacturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->float('price_change')->after('description')->nullable(false);
            $table->float('taux_change')->after('description')->nullable(false);
            $table->string('delivery_note')->after('taxe')->nullable();
            $table->string('po_number')->nullable()->unique();
            $table->string('invoiceNum')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn('price_change');
            $table->dropColumn('delivery_note');
            $table->dropColumn('po_number');
            $table->dropColumn('invoiceNum');
        });
    }
}