<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->string('event_type'); // CREATED, BOOKED, PICKED_UP, DEPARTED, IN_TRANSIT, CUSTOMS_INSPECTION, ARRIVED_HUB, OUT_FOR_DELIVERY, DELIVERED, EXCEPTION_OCCURRED, etc.
            $table->string('status'); // Shipment status at this event
            $table->dateTime('event_time')->index();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('location_name');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('description');
            $table->boolean('is_exception_event')->default(false);
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
    }
};
