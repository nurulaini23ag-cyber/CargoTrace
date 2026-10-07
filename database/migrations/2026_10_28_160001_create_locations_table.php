<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. IDJKT, SGSIN, NLRTM, USLAX
            $table->string('name');
            $table->string('city');
            $table->string('country');
            $table->string('country_code', 5);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('type')->default('PORT'); // PORT, AIRPORT, WAREHOUSE, INLAND_HUB
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
