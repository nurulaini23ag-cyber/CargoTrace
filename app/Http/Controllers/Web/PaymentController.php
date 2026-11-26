<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\TrackingEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Show high-fidelity Payment Gateway Simulator.
     */
    public function show(Payment $payment): View
    {
        $user = Auth::user();
        if ($user->isCustomer() && $payment->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this payment transaction.');
        }

        $payment->load([
            'tradeTransaction.product.category',
            'tradeTransaction.exporter',
            'tradeTransaction.importer',
            'user',
        ]);

        $transaction = $payment->tradeTransaction;
        $orderMeta = json_decode($transaction->notes ?? '[]', true) ?: [];

        // Pre-configured Virtual Account numbers for top 4 Indonesian commercial banks
        $bankVAs = [
            'Mandiri' => [
                'name' => 'Bank Mandiri',
                'va' => '88200' . substr(str_repeat((string) $payment->id, 10), 0, 11),
                'channel' => 'Bank Mandiri Escrow Virtual Account',
                'fee' => 0,
            ],
            'BCA' => [
                'name' => 'Bank Central Asia (BCA)',
                'va' => '80777' . substr(str_repeat((string) $payment->id, 10), 0, 11),
                'channel' => 'BCA Virtual Account',
                'fee' => 0,
            ],
            'BNI' => [
                'name' => 'Bank Negara Indonesia (BNI)',
                'va' => '98801' . substr(str_repeat((string) $payment->id, 10), 0, 11),
                'channel' => 'BNI Virtual Account',
                'fee' => 0,
            ],
            'BRI' => [
                'name' => 'Bank Rakyat Indonesia (BRI)',
                'va' => '88888' . substr(str_repeat((string) $payment->id, 10), 0, 11),
                'channel' => 'BRI Virtual Account (BRIVA)',
                'fee' => 0,
            ],
        ];

        return view('payments.show', compact('payment', 'transaction', 'orderMeta', 'bankVAs'));
    }

    /**
     * Process simulated payment execution and auto-dispatch shipment.
     */
    public function process(Request $request, Payment $payment): RedirectResponse
    {
        $user = Auth::user();
        if ($user->isCustomer() && $payment->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        if ($payment->isSuccess()) {
            return redirect()->route('payments.receipt', $payment->id)
                ->with('info', 'Pembayaran transaksi ini sudah selesai sebelumnya.');
        }

        $method = $request->input('payment_method', Payment::METHOD_VA);
        $channel = $request->input('payment_channel', 'Bank Mandiri Escrow Virtual Account');
        $cardBrand = null;
        $cardLastFour = null;

        // Credit Card 3D Secure Verification Simulator
        if ($method === Payment::METHOD_CC) {
            $isOtpStep = $request->boolean('is_otp_step');
            $otpCode = $request->input('otp_code');

            if ($isOtpStep) {
                if ($otpCode !== '123456') {
                    return back()->withErrors(['otp_code' => 'Kode OTP tidak valid! Masukkan kode simulasi 123456.'])->withInput();
                }
            } else {
                $cardNumber = preg_replace('/\s+/', '', $request->input('card_number', ''));
                $requires3ds = $request->boolean('requires_3ds') || str_ends_with($cardNumber, '2');

                if ($requires3ds) {
                    return back()->with([
                        'show_3ds_modal' => true,
                        'temp_card_number' => $cardNumber,
                        'temp_card_name' => $request->input('card_name'),
                        'temp_card_expiry' => $request->input('card_expiry'),
                    ])->withInput();
                }
            }

            $rawCard = $request->input('card_number', '4000 0000 0000 0001');
            $cleanCard = preg_replace('/\s+/', '', $rawCard);
            $cardLastFour = substr($cleanCard, -4) ?: '0001';
            $cardBrand = str_starts_with($cleanCard, '5') ? 'Mastercard' : 'Visa';
            $channel = "{$cardBrand} ending in •••• {$cardLastFour} (3D Secure Verified)";
        }

        // 1. Mark Payment as SUCCESS
        $refId = 'CT-GW-' . strtoupper(Str::random(10)) . '-' . date('Ymd');
        $payment->update([
            'payment_status' => Payment::STATUS_SUCCESS,
            'payment_method' => $method,
            'payment_channel' => $channel,
            'card_brand' => $cardBrand,
            'card_last_four' => $cardLastFour,
            'transaction_reference' => $refId,
            'paid_at' => now(),
            'gateway_response' => [
                'status' => 'SETTLED',
                'approval_code' => strtoupper(Str::random(6)),
                'reference_id' => $refId,
                'method' => $method,
                'channel' => $channel,
                'paid_amount' => $payment->amount,
                'settled_at' => now()->toIso8601String(),
            ],
        ]);

        // 2. Mark Trade Transaction as CONFIRMED
        $transaction = $payment->tradeTransaction;
        if ($transaction) {
            $transaction->update([
                'transaction_status' => 'confirmed',
            ]);
        }

        // 3. AUTOMATIC SHIPMENT DISPATCH: Create shipment & initial tracking event
        $shipment = Shipment::where('trade_transaction_id', $transaction->id)->first();

        if (!$shipment) {
            $orderMeta = json_decode($transaction->notes ?? '[]', true) ?: [];
            $originId = $orderMeta['origin_id'] ?? 1;
            $destinationId = $orderMeta['destination_id'] ?? 8;
            $carrierId = $orderMeta['carrier_id'] ?? 1;

            $origin = Location::find($originId) ?? Location::first();
            $destination = Location::find($destinationId) ?? Location::skip(7)->first();
            $carrier = Carrier::find($carrierId) ?? Carrier::first();

            $nextShipmentSeq = Shipment::max('id') + 1;
            $shipmentNumber = 'CTC-2026-' . str_pad($nextShipmentSeq, 4, '0', STR_PAD_LEFT);
            $trackingNumber = 'TRC-2026-' . strtoupper(Str::random(6));

            $carrierVesselNames = [
                'Maersk' => 'MV Maersk ' . ($origin->city ?: 'Maritime'),
                'MSC' => 'MSC ' . ($origin->city ?: 'Ocean'),
                'CMA CGM' => 'CMA CGM ' . ($destination->city ?: 'Express'),
                'Hapag-Lloyd' => 'Hapag-Lloyd ' . ($destination->city ?: 'Trader'),
                'Evergreen' => 'Evergreen ' . ($origin->city ?: 'Voyager'),
            ];

            $vesselName = 'MV ' . $carrier->name . ' ' . $origin->city . ' Express';
            foreach ($carrierVesselNames as $key => $vName) {
                if (stripos($carrier->name, $key) !== false) {
                    $vesselName = $vName;
                    break;
                }
            }

            $shipment = Shipment::create([
                'shipment_number' => $shipmentNumber,
                'tracking_number' => $trackingNumber,
                'customer_id' => $payment->user_id,
                'carrier_id' => $carrier->id,
                'origin_id' => $origin->id,
                'destination_id' => $destination->id,
                'cargo_type' => $transaction->product?->name ?? 'Specialty Commodity Batch',
                'weight' => $transaction->quantity * 1000.0,
                'volume' => $transaction->quantity * 2.0,
                'current_status' => Shipment::STATUS_CREATED,
                'vessel_carrier' => $vesselName,
                'expected_departure' => now()->addDays(2),
                'expected_arrival' => now()->addDays(16),
                'estimated_arrival' => now()->addDays(16),
                'trade_transaction_id' => $transaction->id,
                'product_id' => $transaction->product_id,
                'exporter_id' => $transaction->exporter_id,
                'importer_id' => $transaction->importer_id,
                'origin_country_id' => $transaction->origin_country_id,
                'destination_country_id' => $transaction->destination_country_id,
                'special_instructions' => 'Grade ' . ($transaction->product?->grade ?? 'A') . ' Certified Export. Maintain dry container ventilation at 12.5% max moisture.',
            ]);

            // Add Shipment Item
            ShipmentItem::create([
                'shipment_id' => $shipment->id,
                'item_description' => $transaction->product?->name ?? 'Commodity Cargo',
                'product_name' => $transaction->product?->name ?? 'Commodity Cargo',
                'product_category' => $transaction->product?->category?->name ?? 'Agriculture',
                'grade' => $transaction->product?->grade ?? '1',
                'package_type' => '20ft FCL Dry Food-Grade Container',
                'package_count' => (int) ceil($transaction->quantity / 20.0),
                'quantity' => (int) ceil($transaction->quantity / 20.0),
                'weight_kg' => $transaction->quantity * 1000.0,
                'net_weight' => $transaction->quantity * 1000.0,
                'is_hazardous' => false,
            ]);

            // Add Initial Tracking Event
            TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'event_type' => 'ORDER_CONFIRMED',
                'status' => Shipment::STATUS_CREATED,
                'location_id' => $origin->id,
                'location_name' => $origin->name . ' (' . $origin->city . ')',
                'description' => "Pembayaran transaksi komoditas diverifikasi lunas via {$channel}. Kontainer kargo ekspor telah didaftarkan dan dialokasikan untuk armada {$vesselName}.",
                'latitude' => (float) $origin->latitude,
                'longitude' => (float) $origin->longitude,
                'created_by' => $user->id,
                'event_time' => now(),
            ]);

            // Send in-app notification to customer
            Notification::create([
                'user_id' => $payment->user_id,
                'shipment_id' => $shipment->id,
                'title' => 'Pembayaran Sukses & Kargo Didaftarkan',
                'message' => "Pembayaran senilai $" . number_format($payment->amount, 2) . " berhasil diverifikasi. Kargo {$shipment->shipment_number} ({$vesselName}) telah dibuat dan siap dilacak secara real-time di Peta Global.",
                'type' => 'SHIPMENT_UPDATE',
                'is_read' => false,
            ]);

            // Record in Audit Log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'PAYMENT_AND_SHIPMENT_DISPATCH',
                'entity_type' => 'Payment',
                'entity_id' => $payment->id,
                'description' => "Pembayaran {$payment->payment_number} diverifikasi via {$channel}. Pengiriman {$shipment->shipment_number} berhasil dibuat.",
                'new_values' => [
                    'payment_number' => $payment->payment_number,
                    'amount' => $payment->amount,
                    'shipment_id' => $shipment->id,
                    'shipment_number' => $shipment->shipment_number,
                    'tracking_number' => $shipment->tracking_number,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return redirect()->route('payments.receipt', $payment->id)
            ->with('success', 'Pembayaran berhasil diverifikasi secara real-time! Pengiriman armada kapal Anda telah otomatis didaftarkan dan dapat langsung dilacak di peta.');
    }

    /**
     * Show official printable Commercial Invoice & Payment Receipt.
     */
    public function receipt(Payment $payment): View
    {
        $user = Auth::user();
        if ($user->isCustomer() && $payment->user_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $payment->load([
            'tradeTransaction.product.category',
            'tradeTransaction.exporter.country',
            'tradeTransaction.importer',
            'user',
        ]);

        $transaction = $payment->tradeTransaction;
        $shipment = Shipment::with(['origin', 'destination', 'carrier'])
            ->where('trade_transaction_id', $transaction?->id)
            ->first();

        $orderMeta = json_decode($transaction?->notes ?? '[]', true) ?: [];

        return view('payments.receipt', compact('payment', 'transaction', 'shipment', 'orderMeta'));
    }
}
