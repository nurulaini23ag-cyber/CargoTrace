<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->string('item_description');
            $table->string('hs_code', 20)->nullable();
            $table->integer('quantity')->default(1);
            $table->string('package_type')->default('BOX'); // PALLET, BOX, CONTAINER, DRUM, CRATE
            $table->decimal('weight_kg', 10, 2)->default(0);
            $table->decimal('volume_cbm', 10, 2)->default(0);
            $table->boolean('is_hazardous')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};
