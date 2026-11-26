<?php

namespace Tests\Feature;

use App\Models\Buyer;
use App\Models\Carrier;
use App\Models\Category;
use App\Models\Country;
use App\Models\Location;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\Supplier;
use App\Models\TradeTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommodityPurchasingAndPaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $operator;
    protected Product $product;
    protected Location $originPort;
    protected Location $destPort;
    protected Carrier $carrier;
    protected Supplier $supplier;
    protected Buyer $buyer;
    protected Country $destCountry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'name' => 'John Consignee',
            'email' => 'customer@cargotrace.test',
        ]);

        $this->operator = User::factory()->create([
            'role' => User::ROLE_OPERATOR,
            'name' => 'Operator Logistics',
            'email' => 'operator@cargotrace.test',
        ]);

        $country = Country::firstOrCreate(['code' => 'ID'], [
            'name' => 'Indonesia',
            'code' => 'ID',
            'region' => 'Southeast Asia',
            'default_port' => 'Belawan Port, Medan',
            'latitude' => 3.7833,
            'longitude' => 98.6833,
            'is_origin' => true,
            'is_destination' => false,
        ]);

        $this->destCountry = Country::firstOrCreate(['code' => 'NL'], [
            'name' => 'Netherlands',
            'code' => 'NL',
            'region' => 'Europe',
            'default_port' => 'Port of Rotterdam',
            'latitude' => 51.9540,
            'longitude' => 4.0530,
            'is_origin' => false,
            'is_destination' => true,
        ]);

        $category = Category::firstOrCreate(['slug' => 'coffee'], [
            'name' => 'Coffee Beans',
            'slug' => 'coffee',
            'description' => 'Arabica & Robusta coffee beans',
        ]);

        $this->supplier = Supplier::create([
            'company_name' => 'PT Nusantara Agri Exporters',
            'contact_person' => 'Budi Santoso',
            'country_id' => $country->id,
            'email' => 'supplier@nusantara.test',
            'phone' => '+62-61-882910',
            'address' => 'Medan, Indonesia',
            'status' => 'VERIFIED',
        ]);

        $this->buyer = Buyer::create([
            'company_name' => 'Amsterdam Coffee Roasters BV',
            'contact_person' => 'Jan de Boer',
            'email' => 'buyer@roasters.test',
            'phone' => '+31-20-882910',
            'address' => 'Keizersgracht 122, Amsterdam',
            'country_id' => $this->destCountry->id,
            'status' => 'ACTIVE',
            'user_id' => $this->customer->id,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sumatra Mandheling Specialty Grade 1',
            'slug' => 'sumatra-mandheling-specialty-grade-1',
            'origin_country_id' => $country->id,
            'grade' => '1',
            'weight_per_unit' => 60,
            'unit' => 'MT',
            'price' => 4800.00,
            'currency' => 'USD',
            'stock_availability' => 250,
            'product_status' => 'available',
            'description' => 'Premium volcanic soil Arabica coffee beans.',
            'specifications' => [
                'moisture' => '11.5%',
                'defect_rate' => '2.5%',
                'screen_size' => '18/19',
                'certifications' => ['Rainforest Alliance', 'Fairtrade'],
            ],
            'is_featured' => true,
        ]);

        $this->originPort = Location::create([
            'code' => 'IDBLW',
            'name' => 'Belawan Port, Medan',
            'city' => 'Medan',
            'country' => 'Indonesia',
            'country_code' => 'ID',
            'latitude' => 3.7833,
            'longitude' => 98.6833,
            'type' => 'PORT',
        ]);

        $this->destPort = Location::create([
            'code' => 'NLRTM',
            'name' => 'Port of Rotterdam',
            'city' => 'Rotterdam',
            'country' => 'Netherlands',
            'country_code' => 'NL',
            'latitude' => 51.9540,
            'longitude' => 4.0530,
            'type' => 'PORT',
        ]);

        $this->carrier = Carrier::create([
            'name' => 'Maersk Line',
            'code' => 'MSK',
            'is_active' => true,
        ]);
    }

    public function test_customer_can_browse_commodity_catalog(): void
    {
        $response = $this->actingAs($this->customer)->get(route('catalog.index'));

        $response->assertStatus(200);
        $response->assertSee('Sumatra Mandheling Specialty Grade 1');
        $response->assertSee('$4,800.00');
        $response->assertSee('Beli Batch Ini');
    }

    public function test_customer_can_view_product_detail_and_specifications(): void
    {
        $response = $this->actingAs($this->customer)->get(route('catalog.show', $this->product->slug));

        $response->assertStatus(200);
        $response->assertSee('Sumatra Mandheling Specialty Grade 1');
        $response->assertSee('Kadar Air (Moisture)');
        $response->assertSee('11.5%');
        $response->assertSee('Rainforest Alliance');
        $response->assertSee('Beli Sekarang');
    }

    public function test_customer_can_view_order_checkout_form(): void
    {
        $response = $this->actingAs($this->customer)->get(route('orders.create', ['product_id' => $this->product->id]));

        $response->assertStatus(200);
        $response->assertSee('Formulir Pemesanan');
        $response->assertSee('Belawan Port, Medan');
        $response->assertSee('Port of Rotterdam');
        $response->assertSee('Maersk Line');
    }

    public function test_customer_can_create_order_and_pending_payment(): void
    {
        $response = $this->actingAs($this->customer)->post(route('orders.store'), [
            'product_id' => $this->product->id,
            'quantity_mt' => 20,
            'origin_id' => $this->originPort->id,
            'destination_id' => $this->destPort->id,
            'carrier_id' => $this->carrier->id,
            'consignee_company' => 'Amsterdam Coffee Roasters BV',
            'consignee_address' => 'Keizersgracht 122, Amsterdam',
            'contact_phone' => '+31-20-882910',
            'payment_method' => Payment::METHOD_VA,
            'bank_channel' => 'Bank Mandiri',
        ]);

        $this->assertDatabaseHas('trade_transactions', [
            'product_id' => $this->product->id,
            'quantity' => 20,
            'currency' => 'USD',
            'transaction_status' => 'draft',
        ]);

        $transaction = TradeTransaction::where('product_id', $this->product->id)->latest('id')->first();
        $this->assertNotNull($transaction);

        $this->assertDatabaseHas('payments', [
            'trade_transaction_id' => $transaction->id,
            'payment_method' => Payment::METHOD_VA,
            'payment_status' => Payment::STATUS_PENDING,
        ]);

        $payment = Payment::where('trade_transaction_id', $transaction->id)->first();
        $response->assertRedirect(route('payments.show', $payment->id));
    }

    public function test_payment_gateway_process_via_virtual_account_dispatches_shipment(): void
    {
        $transaction = TradeTransaction::create([
            'transaction_id' => 'TR-2026-TEST01',
            'transaction_type' => 'import',
            'exporter_id' => $this->supplier->id,
            'importer_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
            'unit' => 'MT',
            'unit_price' => 4800.00,
            'total_price' => 98442.25,
            'currency' => 'USD',
            'origin_country_id' => $this->product->origin_country_id,
            'destination_country_id' => $this->destCountry->id,
            'departure_port' => 'Belawan Port, Medan',
            'destination_port' => 'Port of Rotterdam',
            'transaction_status' => 'draft',
            'user_id' => $this->customer->id,
            'notes' => json_encode([
                'origin_id' => $this->originPort->id,
                'destination_id' => $this->destPort->id,
                'carrier_id' => $this->carrier->id,
                'consignee_company' => 'Amsterdam Coffee Roasters BV',
            ]),
        ]);

        $payment = Payment::create([
            'payment_number' => 'PAY-2026-TEST01',
            'trade_transaction_id' => $transaction->id,
            'user_id' => $this->customer->id,
            'amount' => 98442.25,
            'currency' => 'USD',
            'payment_method' => Payment::METHOD_VA,
            'payment_channel' => 'Bank Mandiri Escrow Virtual Account',
            'virtual_account_number' => '8820019283749201',
            'payment_status' => Payment::STATUS_PENDING,
        ]);

        // Process simulated VA payment
        $response = $this->actingAs($this->customer)->post(route('payments.process', $payment->id), [
            'payment_method' => Payment::METHOD_VA,
            'payment_channel' => 'Bank Mandiri Escrow Virtual Account',
        ]);

        $response->assertRedirect(route('payments.receipt', $payment->id));

        // 1. Payment status should be SUCCESS
        $this->assertEquals(Payment::STATUS_SUCCESS, $payment->fresh()->payment_status);
        $this->assertNotNull($payment->fresh()->paid_at);

        // 2. Trade transaction status should be CONFIRMED
        $this->assertEquals('confirmed', $transaction->fresh()->transaction_status);

        // 3. Shipment must be AUTOMATICALLY CREATED
        $this->assertDatabaseHas('shipments', [
            'trade_transaction_id' => $transaction->id,
            'customer_id' => $this->customer->id,
            'current_status' => Shipment::STATUS_CREATED,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->originPort->id,
            'destination_id' => $this->destPort->id,
        ]);

        $shipment = Shipment::where('trade_transaction_id', $transaction->id)->first();
        $this->assertNotNull($shipment);
        $this->assertStringStartsWith('CTC-2026-', $shipment->shipment_number);
        $this->assertStringStartsWith('TRC-2026-', $shipment->tracking_number);

        // 4. Initial TrackingEvent must exist
        $this->assertDatabaseHas('tracking_events', [
            'shipment_id' => $shipment->id,
            'event_type' => 'ORDER_CONFIRMED',
            'status' => Shipment::STATUS_CREATED,
        ]);

        // 5. In-app Notification created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->customer->id,
            'shipment_id' => $shipment->id,
        ]);
    }

    public function test_payment_gateway_process_via_credit_card_3ds_otp(): void
    {
        $transaction = TradeTransaction::create([
            'transaction_id' => 'TR-2026-TEST02',
            'transaction_type' => 'import',
            'exporter_id' => $this->supplier->id,
            'importer_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit' => 'MT',
            'unit_price' => 4800.00,
            'total_price' => 50000.00,
            'currency' => 'USD',
            'origin_country_id' => $this->product->origin_country_id,
            'destination_country_id' => $this->destCountry->id,
            'departure_port' => 'Belawan Port, Medan',
            'destination_port' => 'Port of Rotterdam',
            'transaction_status' => 'draft',
            'user_id' => $this->customer->id,
            'notes' => json_encode([
                'origin_id' => $this->originPort->id,
                'destination_id' => $this->destPort->id,
                'carrier_id' => $this->carrier->id,
            ]),
        ]);

        $payment = Payment::create([
            'payment_number' => 'PAY-2026-TEST02',
            'trade_transaction_id' => $transaction->id,
            'user_id' => $this->customer->id,
            'amount' => 50000.00,
            'currency' => 'USD',
            'payment_method' => Payment::METHOD_CC,
            'payment_status' => Payment::STATUS_PENDING,
        ]);

        // Step 1: Submit card ending in 2 triggers 3DS OTP modal
        $step1 = $this->actingAs($this->customer)->post(route('payments.process', $payment->id), [
            'payment_method' => Payment::METHOD_CC,
            'card_number' => '4000 0000 0000 0002',
            'card_name' => 'John Doe',
            'card_expiry' => '12/28',
            'card_cvv' => '123',
            'requires_3ds' => 1,
        ]);

        $step1->assertSessionHas('show_3ds_modal', true);

        // Step 2: Submit valid 6-digit OTP code 123456
        $step2 = $this->actingAs($this->customer)->post(route('payments.process', $payment->id), [
            'payment_method' => Payment::METHOD_CC,
            'is_otp_step' => 1,
            'otp_code' => '123456',
            'card_number' => '4000 0000 0000 0002',
            'card_name' => 'John Doe',
            'card_expiry' => '12/28',
        ]);

        $step2->assertRedirect(route('payments.receipt', $payment->id));
        $this->assertEquals(Payment::STATUS_SUCCESS, $payment->fresh()->payment_status);
        $this->assertEquals('Visa', $payment->fresh()->card_brand);
        $this->assertEquals('0002', $payment->fresh()->card_last_four);
    }

    public function test_customer_can_view_commercial_invoice_and_receipt_with_tracking_link(): void
    {
        $transaction = TradeTransaction::create([
            'transaction_id' => 'TR-2026-TEST03',
            'transaction_type' => 'import',
            'exporter_id' => $this->supplier->id,
            'importer_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
            'unit' => 'MT',
            'unit_price' => 4800.00,
            'total_price' => 98442.25,
            'currency' => 'USD',
            'origin_country_id' => $this->product->origin_country_id,
            'destination_country_id' => $this->destCountry->id,
            'departure_port' => 'Belawan Port, Medan',
            'destination_port' => 'Port of Rotterdam',
            'transaction_status' => 'confirmed',
            'user_id' => $this->customer->id,
            'notes' => json_encode([]),
        ]);

        $payment = Payment::create([
            'payment_number' => 'PAY-2026-TEST03',
            'trade_transaction_id' => $transaction->id,
            'user_id' => $this->customer->id,
            'amount' => 98442.25,
            'currency' => 'USD',
            'payment_method' => Payment::METHOD_VA,
            'payment_channel' => 'Bank Mandiri Escrow Virtual Account',
            'virtual_account_number' => '8820019283749201',
            'payment_status' => Payment::STATUS_SUCCESS,
            'paid_at' => now(),
        ]);

        $shipment = Shipment::create([
            'shipment_number' => 'CTC-2026-0099',
            'tracking_number' => 'TRC-2026-XYZ123',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->originPort->id,
            'destination_id' => $this->destPort->id,
            'cargo_type' => $this->product->name,
            'weight' => 20000,
            'volume' => 40,
            'current_status' => Shipment::STATUS_CREATED,
            'expected_departure' => now()->addDays(2),
            'expected_arrival' => now()->addDays(16),
            'estimated_arrival' => now()->addDays(16),
            'trade_transaction_id' => $transaction->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->customer)->get(route('payments.receipt', $payment->id));

        $response->assertStatus(200);
        $response->assertSee('COMMERCIAL INVOICE', false);
        $response->assertSee('PAY-2026-TEST03');
        $response->assertSee('$98,442.25');
        $response->assertSee('CTC-2026-0099');
        $response->assertSee('TRC-2026-XYZ123');
        $response->assertSee(route('map.index', ['q' => 'CTC-2026-0099']));
    }

    public function test_customer_can_view_orders_index(): void
    {
        $response = $this->actingAs($this->customer)->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Pesanan', false);
        $response->assertSee('Beli Komoditas Baru');
    }
}
