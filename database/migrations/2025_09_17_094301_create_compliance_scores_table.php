<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('compliance_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('manager_id');
            $table->unsignedBigInteger('pub_id');

            $table->foreign('manager_id')
                ->references('id')->on('managers')
                ->onDelete('restrict')->onUpdate('cascade');

            $table->foreign('pub_id')
                ->references('id')->on('pub_numbers')
                ->onDelete('restrict')->onUpdate('cascade');

            $table->integer('score');
            $table->date('week_start');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_scores');
    }
};
