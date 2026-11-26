<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Carrier;
use App\Models\Country;
use App\Models\Location;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\TradeTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Show the commodity order and checkout form.
     */
    public function create(Request $request): View
    {
        $productId = $request->get('product_id');
        $productSlug = $request->get('product');

        $product = Product::with(['category', 'originCountry'])
            ->when($productId, fn($q) => $q->where('id', $productId))
            ->when($productSlug, fn($q) => $q->orWhere('slug', $productSlug))
            ->first() ?? Product::with(['category', 'originCountry'])->firstOrFail();

        // Origin ports (coffee & cocoa producing hubs)
        $originLocations = Location::whereIn('id', [1, 2, 3, 4, 5, 6, 7])->get();
        if ($originLocations->isEmpty()) {
            $originLocations = Location::take(5)->get();
        }

        // Destination ports (international import terminals)
        $destinationLocations = Location::whereIn('id', [8, 9, 10, 11, 12])->get();
        if ($destinationLocations->isEmpty()) {
            $destinationLocations = Location::skip(5)->take(5)->get();
        }

        $carriers = Carrier::where('is_active', true)->get();
        if ($carriers->isEmpty()) {
            $carriers = Carrier::all();
        }

        $user = Auth::user();
        $buyer = Buyer::where('user_id', $user->id)->first();

        return view('orders.create', compact('product', 'originLocations', 'destinationLocations', 'carriers', 'buyer'));
    }

    /**
     * Store a new trade transaction and create a pending payment record.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity_mt' => 'required|numeric|min:1|max:1000',
            'origin_id' => 'required|exists:locations,id',
            'destination_id' => 'required|exists:locations,id',
            'carrier_id' => 'required|exists:carriers,id',
            'consignee_company' => 'required|string|max:255',
            'consignee_address' => 'required|string|max:500',
            'contact_phone' => 'required|string|max:50',
            'payment_method' => 'required|string|in:VIRTUAL_ACCOUNT,CREDIT_CARD,LETTER_OF_CREDIT,QRIS',
            'bank_channel' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $product = Product::with('originCountry')->findOrFail($request->product_id);
        $origin = Location::findOrFail($request->origin_id);
        $destination = Location::findOrFail($request->destination_id);
        $carrier = Carrier::findOrFail($request->carrier_id);

        $quantityMt = (float) $request->quantity_mt;
        $pricePerMt = (float) $product->price;

        // CIF Cost Calculation
        $commoditySubtotal = $quantityMt * $pricePerMt;
        $containerCount = (int) ceil($quantityMt / 20.0);
        $oceanFreight = $containerCount * 1850.00; // $1,850 per 20ft container
        $marineInsurance = round(($commoditySubtotal + $oceanFreight) * 0.0035, 2); // 0.35%
        $portPhytoDocs = 250.00; // Export handling & phytosanitary clearance
        $totalCifAmount = $commoditySubtotal + $oceanFreight + $marineInsurance + $portPhytoDocs;

        // Ensure Buyer record exists for this customer
        $buyer = Buyer::firstOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => $request->consignee_company,
                'contact_person' => $user->name,
                'email' => $user->email,
                'phone' => $request->contact_phone,
                'address' => $request->consignee_address,
                'country_id' => 1, // Default Indonesia or mapped
                'buyer_type' => 'IMPORTER_WHOLESALER',
                'status' => 'ACTIVE',
            ]
        );

        // Map supplier
        $supplier = Supplier::where('country_id', $product->origin_country_id)->first() ?? Supplier::first();
        if (!$supplier) {
            $supplier = Supplier::create([
                'company_name' => 'PT Nusantara Agro Exporters',
                'contact_person' => 'Budi Santoso',
                'email' => 'export@nusantara-agro.id',
                'phone' => '+62-61-882910',
                'address' => 'Kawasan Industri Medan II, Belawan',
                'country_id' => 1,
                'rating' => 4.9,
                'status' => 'VERIFIED',
            ]);
        }

        // Create Trade Transaction
        $lastTrxId = TradeTransaction::max('id') ?? 0;
        $transactionNumber = 'TR-2026-' . str_pad($lastTrxId + 1, 4, '0', STR_PAD_LEFT);

        $destCountry = Country::where('name', $destination->country)
            ->orWhere('code', $destination->country_code)
            ->first();
        $destCountryId = $destCountry ? $destCountry->id : ($product->origin_country_id ?? 1);

        $transaction = TradeTransaction::create([
            'transaction_id' => $transactionNumber,
            'transaction_type' => 'import',
            'exporter_id' => $supplier->id,
            'importer_id' => $buyer->id,
            'product_id' => $product->id,
            'quantity' => $quantityMt,
            'unit' => 'MT',
            'unit_price' => $pricePerMt,
            'total_price' => $totalCifAmount,
            'currency' => 'USD',
            'origin_country_id' => $product->origin_country_id ?? 1,
            'destination_country_id' => $destCountryId,
            'departure_port' => $origin->name . ' (' . $origin->city . ')',
            'destination_port' => $destination->name . ' (' . $destination->city . ')',
            'transaction_date' => now(),
            'estimated_arrival_date' => now()->addDays(16),
            'incoterms' => 'CIF',
            'transaction_status' => 'draft',
            'user_id' => $user->id,
            'notes' => json_encode([
                'consignee_company' => $request->consignee_company,
                'consignee_address' => $request->consignee_address,
                'contact_phone' => $request->contact_phone,
                'origin_id' => $origin->id,
                'destination_id' => $destination->id,
                'carrier_id' => $carrier->id,
                'commodity_subtotal' => $commoditySubtotal,
                'ocean_freight' => $oceanFreight,
                'marine_insurance' => $marineInsurance,
                'port_docs' => $portPhytoDocs,
                'containers' => $containerCount,
            ]),
        ]);

        // Channel naming
        $method = $request->payment_method;
        $channel = $request->bank_channel;
        $vaNumber = null;

        if ($method === Payment::METHOD_VA) {
            $bankPrefix = match($channel) {
                'Bank BCA' => '80777',
                'Bank BNI' => '98801',
                'Bank BRI' => '88888',
                default => '88200', // Mandiri
            };
            $vaNumber = $bankPrefix . rand(10000000000, 99999999999);
            $channelName = ($channel ?: 'Bank Mandiri') . ' Escrow Virtual Account';
        } elseif ($method === Payment::METHOD_CC) {
            $channelName = 'International Credit / Debit Card (Visa / Mastercard)';
        } elseif ($method === Payment::METHOD_LC) {
            $channelName = ($channel ?: 'BNI Corporate Trade Finance') . ' - Irrevocable L/C SWIFT MT700';
        } else {
            $channelName = 'QRIS Instant Trade Escrow';
        }

        // Create Payment Record in PENDING status
        $payment = Payment::create([
            'trade_transaction_id' => $transaction->id,
            'user_id' => $user->id,
            'amount' => $totalCifAmount,
            'currency' => 'USD',
            'payment_method' => $method,
            'payment_channel' => $channelName,
            'virtual_account_number' => $vaNumber,
            'payment_status' => Payment::STATUS_PENDING,
            'expires_at' => now()->addHours(24),
            'gateway_response' => [
                'status' => 'AWAITING_PAYMENT',
                'order_id' => $transaction->transaction_id,
                'currency' => 'USD',
                'amount' => $totalCifAmount,
            ],
        ]);

        return redirect()->route('payments.show', $payment->id)
            ->with('success', 'Pesanan komoditas ' . $product->name . ' berhasil dibuat. Silakan selesaikan pembayaran melalui simulasi payment gateway berikut.');
    }

    /**
     * Display customer's list of purchase orders.
     */
    public function index(): View
    {
        $user = Auth::user();

        $query = TradeTransaction::with([
            'product.category',
            'exporter',
            'importer',
            'payment',
            'shipment',
        ]);

        if ($user->isCustomer()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('importer', fn($iq) => $iq->where('user_id', $user->id));
            });
        }

        $transactions = $query->latest('id')->paginate(10);

        return view('orders.index', compact('transactions'));
    }

    /**
     * Display order detail page.
     */
    public function show(int $id): View
    {
        $transaction = TradeTransaction::with([
            'product.category',
            'exporter.country',
            'importer',
            'payment',
            'shipment.origin',
            'shipment.destination',
            'shipment.carrier',
        ])->findOrFail($id);

        $orderMeta = json_decode($transaction->notes ?? '[]', true) ?: [];

        return view('orders.show', compact('transaction', 'orderMeta'));
    }
}
