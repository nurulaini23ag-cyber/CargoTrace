<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->string('exception_type'); // CUSTOMS_HOLD, WEATHER_DELAY, PORT_CONGESTION, DAMAGED, MISROUTED, DOCUMENTATION_ISSUE
            $table->string('severity')->default('MEDIUM'); // LOW, MEDIUM, HIGH, CRITICAL
            $table->string('status')->default('OPEN')->index(); // OPEN, INVESTIGATING, RESOLVED
            $table->dateTime('detected_at');
            $table->dateTime('resolved_at')->nullable();
            $table->integer('impact_hours')->default(0); // Estimated delay hours caused by this exception
            $table->text('description');
            $table->text('resolution_notes')->nullable();
            $table->foreignId('logged_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_exceptions');
    }
};
