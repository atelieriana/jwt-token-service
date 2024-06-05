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
        Schema::create('detail_module', function (Blueprint $table) {
            $table->foreignId('client_id')->references('id')->on('client')->onDelete('cascade')->index();
            $table->integer('module_id');
            $table->integer('access_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_module');
    }
};
