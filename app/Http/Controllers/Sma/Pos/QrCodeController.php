<?php

namespace App\Http\Controllers\Sma\Pos;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Sma\Pos\Hall;
use App\Models\Sma\Pos\Table;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class QrCodeController extends Controller
{
    /**
     * Display QR codes for all halls and tables.
     */
    public function index(): Response
    {
        $halls = Hall::with(['tables' => fn ($q) => $q->select(['id', 'hall_id', 'name', 'seats', 'qr_token'])])
            ->active()->ordered()->ofStore()->active()->ordered()->get(['id', 'name']);

        $halls->each(function ($hall) {
            $hall->tables->each(fn ($table) => $table->append('menu_url'));
        });

        return Inertia::render('Sma/Pos/QrCode/Index', [
            'halls'    => $halls,
            'app_name' => get_settings('name') ?? config('app.name'),
        ]);
    }

    /**
     * Regenerate QR token for a specific table.
     */
    public function regenerate(Table $table): JsonResponse
    {
        $table->regenerateQrToken();

        return response()->json([
            'success'  => true,
            'qr_token' => $table->qr_token,
            'menu_url' => $table->menu_url,
            'message'  => __('QR code has been regenerated.'),
        ]);
    }
}
