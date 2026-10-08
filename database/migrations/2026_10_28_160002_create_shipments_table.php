<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number')->unique()->index();
            $table->foreignId('customer_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('carrier_id')->constrained('carriers')->onDelete('restrict');
            $table->foreignId('origin_id')->constrained('locations')->onDelete('restrict');
            $table->foreignId('destination_id')->constrained('locations')->onDelete('restrict');
            
            $table->string('cargo_type'); // e.g. ELECTRONICS, PERISHABLES, GARMENTS, GENERAL_CARGO, PHARMACEUTICALS
            $table->decimal('weight', 10, 2); // kg
            $table->decimal('volume', 10, 2); // cbm
            
            // Lifecycle status: CREATED -> BOOKED -> PICKED_UP -> IN_TRANSIT -> DELIVERED (or CANCELLED)
            $table->string('current_status')->default('CREATED')->index();
            
            // Scheduling & ETA
            $table->dateTime('expected_departure');
            $table->dateTime('expected_arrival');
            $table->dateTime('estimated_arrival')->nullable(); // Current calculated ETA
            $table->dateTime('actual_departure')->nullable();
            $table->dateTime('actual_arrival')->nullable();
            
            // Delay detection & metrics
            $table->boolean('is_delayed')->default(false)->index();
            $table->decimal('delay_hours', 8, 2)->default(0);
            $table->string('delay_severity')->default('NONE'); // NONE, LOW, MEDIUM, HIGH
            $table->text('delay_reason')->nullable();
            
            $table->text('special_instructions')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
