<?php

namespace App\Http\Controllers\Sma\Setting;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Tec\Services\V3ImportService;

class V3ImportController extends Controller
{
    public function __construct(
        protected V3ImportService $importService
    ) {}

    public function index()
    {
        return Inertia::render('Sma/Setting/V3Import', [
            'defaultConnection' => [
                'host'     => 'localhost',
                'port'     => '3306',
                'database' => '',
                'username' => '',
                'password' => '',
            ],
        ]);
    }

    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'host'     => 'required|string',
            'port'     => 'required|integer',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        try {
            $this->importService->testConnection($validated);

            return response()->json([
                'success' => true,
                'message' => __('Connection successful! Database is accessible.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Connection failed: :error', ['error' => $e->getMessage()]),
            ], 422);
        }
    }

    public function processImport(Request $request)
    {
        $validated = $request->validate([
            'host'           => 'required|string',
            'port'           => 'required|integer',
            'database'       => 'required|string',
            'username'       => 'required|string',
            'password'       => 'nullable|string',
            'import_types'   => 'required|array',
            'import_types.*' => 'in:warehouses,categories,brands,units,tax_rates,products,customers,suppliers',
        ]);

        set_time_limit(300); // 5 minutes for large imports

        try {
            $results = $this->importService->import(
                $validated,
                $validated['import_types']
            );

            return response()->json([
                'success' => true,
                'message' => __('Import completed successfully.'),
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Import failed: :error', ['error' => $e->getMessage()]),
            ], 422);
        }
    }
}
