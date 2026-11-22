<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipment_items', function (Blueprint $table) {
            if (!Schema::hasColumn('shipment_items', 'product_category')) {
                $table->string('product_category', 50)->nullable()->after('shipment_id'); // COCOA, COFFEE
            }
            if (!Schema::hasColumn('shipment_items', 'product_name')) {
                $table->string('product_name', 255)->nullable()->after('product_category');
            }
            if (!Schema::hasColumn('shipment_items', 'variety')) {
                $table->string('variety', 100)->nullable()->after('product_name'); // Arabica Gayo, Robusta Lampung, Forastero, Criollo
            }
            if (!Schema::hasColumn('shipment_items', 'grade')) {
                $table->string('grade', 50)->nullable()->after('variety'); // Grade 1, Specialty, Fairtrade Organic, Fine Flavor
            }
            if (!Schema::hasColumn('shipment_items', 'package_count')) {
                $table->integer('package_count')->nullable()->after('package_type');
            }
            if (!Schema::hasColumn('shipment_items', 'net_weight')) {
                $table->decimal('net_weight', 10, 2)->nullable()->after('package_count');
            }
            if (!Schema::hasColumn('shipment_items', 'moisture_content')) {
                $table->decimal('moisture_content', 5, 2)->nullable()->after('net_weight'); // QC Moisture %
            }
            if (!Schema::hasColumn('shipment_items', 'temperature_target')) {
                $table->decimal('temperature_target', 5, 2)->nullable()->after('moisture_content'); // Target Reefer °C
            }
        });
    }

    public function down(): void
    {
        Schema::table('shipment_items', function (Blueprint $table) {
            $table->dropColumn([
                'product_category',
                'product_name',
                'variety',
                'grade',
                'package_count',
                'net_weight',
                'moisture_content',
                'temperature_target',
            ]);
        });
    }
};
