<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Countries Table
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('code', 3)->unique()->index(); // e.g. ID, BR, VN, CO, ET, GH, EC, DE, IT, JP, NL, US
                $table->string('name');
                $table->string('region'); // Southeast Asia, South America, West Africa, Europe, East Asia, North America
                $table->string('default_port');
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->string('flag_emoji')->nullable();
                $table->boolean('is_origin')->default(false);
                $table->boolean('is_destination')->default(false);
                $table->timestamps();
            });
        }

        // 2. Categories Table
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Coffee, Cocoa
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->timestamps();
            });
        }

        // 3. Products Table
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
                $table->string('name'); // e.g. Arabica Green Coffee Beans, Raw Cocoa Beans
                $table->string('slug')->unique();
                $table->foreignId('origin_country_id')->constrained('countries')->onDelete('restrict');
                $table->string('grade'); // Grade 1, Specialty, Fairtrade Organic, Fine Flavor
                $table->decimal('weight_per_unit', 10, 2)->default(1.00); // 1.00
                $table->string('unit')->default('MT'); // MT, KG, Bags (60kg)
                $table->decimal('price', 12, 2); // USD price per unit
                $table->string('currency', 5)->default('USD');
                $table->decimal('stock_availability', 10, 2)->default(500.00); // in unit (e.g. MT)
                $table->string('product_status')->default('Available'); // Available, Low Stock, Reserved, Out of Stock
                $table->text('description')->nullable();
                $table->json('specifications')->nullable(); // moisture, screen size, bean count, defects
                $table->string('product_image')->nullable();
                $table->boolean('is_featured')->default(false);
                $table->timestamps();
            });
        }

        // 4. Suppliers Table
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('company_name');
                $table->string('contact_person');
                $table->foreignId('country_id')->constrained('countries')->onDelete('restrict');
                $table->string('email')->unique();
                $table->string('phone');
                $table->text('address');
                $table->text('products_supplied')->nullable();
                $table->string('certifications')->nullable(); // Rainforest Alliance, Fairtrade, UTZ, USDA Organic
                $table->decimal('rating', 3, 2)->default(4.85);
                $table->string('status')->default('ACTIVE'); // ACTIVE, PENDING, SUSPENDED
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 5. Buyers Table
        if (!Schema::hasTable('buyers')) {
            Schema::create('buyers', function (Blueprint $table) {
                $table->id();
                $table->string('company_name');
                $table->string('contact_person');
                $table->foreignId('country_id')->constrained('countries')->onDelete('restrict');
                $table->string('email')->unique();
                $table->string('phone');
                $table->text('address');
                $table->string('buyer_type')->default('Roaster'); // Roaster, Chocolatier, Trader, Retailer, Distributor
                $table->decimal('annual_volume_mt', 10, 2)->default(350.00);
                $table->string('status')->default('ACTIVE'); // ACTIVE, PENDING, INACTIVE
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 6. Trade Transactions Table
        if (!Schema::hasTable('trade_transactions')) {
            Schema::create('trade_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_id')->unique()->index(); // TR-2026-0001
                $table->string('transaction_type')->default('EXPORT'); // EXPORT, IMPORT
                $table->foreignId('exporter_id')->constrained('suppliers')->onDelete('restrict');
                $table->foreignId('importer_id')->constrained('buyers')->onDelete('restrict');
                $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
                $table->decimal('quantity', 10, 2);
                $table->string('unit')->default('MT');
                $table->decimal('unit_price', 12, 2);
                $table->decimal('total_price', 14, 2);
                $table->string('currency', 5)->default('USD');
                $table->foreignId('origin_country_id')->constrained('countries')->onDelete('restrict');
                $table->foreignId('destination_country_id')->constrained('countries')->onDelete('restrict');
                $table->string('departure_port');
                $table->string('destination_port');
                $table->date('transaction_date');
                $table->date('estimated_arrival_date');
                $table->string('incoterms', 10)->default('FOB'); // FOB, CIF, CFR, EXW
                $table->string('transaction_status')->default('Confirmed'); // Pending, Confirmed, Contract Signed, Processing, In Transit, Completed, Cancelled
                $table->text('notes')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 7. Enhance / Add columns to Shipments Table if not present
        Schema::table('shipments', function (Blueprint $table) {
            if (!Schema::hasColumn('shipments', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->unique()->index(); // CT-2026-000001
            }
            if (!Schema::hasColumn('shipments', 'trade_transaction_id')) {
                $table->foreignId('trade_transaction_id')->nullable()->constrained('trade_transactions')->nullOnDelete();
            }
            if (!Schema::hasColumn('shipments', 'product_id')) {
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            }
            if (!Schema::hasColumn('shipments', 'quantity')) {
                $table->decimal('quantity', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('shipments', 'unit')) {
                $table->string('unit')->nullable()->default('MT');
            }
            if (!Schema::hasColumn('shipments', 'exporter_id')) {
                $table->foreignId('exporter_id')->nullable()->constrained('suppliers')->nullOnDelete();
            }
            if (!Schema::hasColumn('shipments', 'importer_id')) {
                $table->foreignId('importer_id')->nullable()->constrained('buyers')->nullOnDelete();
            }
            if (!Schema::hasColumn('shipments', 'origin_country_id')) {
                $table->foreignId('origin_country_id')->nullable()->constrained('countries')->nullOnDelete();
            }
            if (!Schema::hasColumn('shipments', 'destination_country_id')) {
                $table->foreignId('destination_country_id')->nullable()->constrained('countries')->nullOnDelete();
            }
            if (!Schema::hasColumn('shipments', 'shipping_method')) {
                $table->string('shipping_method')->nullable()->default('Ocean Freight');
            }
            if (!Schema::hasColumn('shipments', 'vessel_carrier')) {
                $table->string('vessel_carrier')->nullable()->default('MV Maersk Semarang');
            }
            if (!Schema::hasColumn('shipments', 'departure_port')) {
                $table->string('departure_port')->nullable();
            }
            if (!Schema::hasColumn('shipments', 'destination_port')) {
                $table->string('destination_port')->nullable();
            }
            if (!Schema::hasColumn('shipments', 'departure_date')) {
                $table->dateTime('departure_date')->nullable();
            }
            if (!Schema::hasColumn('shipments', 'current_location_name')) {
                $table->string('current_location_name')->nullable();
            }
            if (!Schema::hasColumn('shipments', 'current_latitude')) {
                $table->decimal('current_latitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('shipments', 'current_longitude')) {
                $table->decimal('current_longitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('shipments', 'container_number')) {
                $table->string('container_number')->nullable();
            }
            if (!Schema::hasColumn('shipments', 'temperature_celsius')) {
                $table->decimal('temperature_celsius', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('shipments', 'humidity_percent')) {
                $table->decimal('humidity_percent', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('shipments', 'simulated_route')) {
                $table->json('simulated_route')->nullable();
            }
        });

        // 8. Create Dedicated shipment_tracking_events table or table view
        if (!Schema::hasTable('shipment_tracking_events')) {
            Schema::create('shipment_tracking_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->string('status'); // Order Confirmed, Processing, Ready for Shipment, Shipped, In Transit, Customs Clearance, Arrived at Destination, Delivered
                $table->string('location');
                $table->dateTime('event_time')->index();
                $table->text('description');
                $table->string('updated_by')->nullable()->default('CargoTrace Automated EDI');
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_tracking_events');
        Schema::dropIfExists('trade_transactions');
        Schema::dropIfExists('buyers');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('countries');
    }
};
