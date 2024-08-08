<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Labtest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('labs_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_id');
            $table->string('test_name')->unique();
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            // Add foreign key constraints
            $table->foreign('lab_id')->references('id')->on('labs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('labtests', function (Blueprint $table) {
            // Drop the foreign key constraints
            $table->dropForeign(['lab_id']);
        });
        Schema::dropIfExists('labs_tests');
    }
}
