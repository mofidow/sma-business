<?php

namespace App\Http\Controllers\Sma\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Tec\Actions\SavePromotion;
use App\Http\Controllers\Controller;
use App\Models\Sma\Product\Promotion;
use App\Http\Requests\Sma\Product\PromotionRequest;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Inertia::render('Sma/Product/Promotion/Index', [
            'types'      => Promotion::$types,
            'stores'     => Store::all(['id', 'name']),
            'promotions' => new Collection(
                Promotion::with([
                    'categories:id,name', 'products:id,code,name', 'stores:id,name',
                ])->latest()->orderBy('name')->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PromotionRequest $request)
    {
        $promotion = (new SavePromotion)->execute($request->validated());

        return redirect()->route('promotions.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Promotion'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Promotion $promotion)
    {
        return $promotion->load(['categories:id,name', 'products:id,code,name', 'stores:id,name']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PromotionRequest $request, Promotion $promotion)
    {
        $promotion = (new SavePromotion)->execute($request->validated(), $promotion);

        return redirect()->route('promotions.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Promotion'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Promotion $promotion)
    {
        if ($promotion->{$promotion->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Promotion'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Promotion'),
            'action' => __('deleted'),
        ]));
    }
}
