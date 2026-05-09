<?php

namespace App\Http\Controllers\Sma\Order;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Nnjeim\World\Models\Country;
use App\Http\Resources\Collection;
use App\Models\Sma\Order\GiftCard;
use App\Http\Controllers\Controller;
use App\Models\Sma\Setting\CustomField;
use App\Http\Requests\Sma\Order\GiftCardRequest;

class GiftCardController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('store')->only(['create', 'store']);
    // }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Order/GiftCard/Index', [
            'customer_fields' => CustomField::ofModel('customer')->get(),
            'countries'       => Country::with('states:id,name,country_id')->get(),

            'pagination' => new Collection(
                GiftCard::filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GiftCardRequest $request)
    {
        $gift_card = GiftCard::create($request->validated());

        if ($request->acceptsJson()) {
            return $gift_card;
        }

        return to_route('gift_cards.index')
            ->with('message', __('{record} has been {action}.', ['record' => 'GiftCard', 'action' => 'created']));
    }

    /**
     * Display the specified resource.
     */
    public function show(GiftCard $gift_card)
    {
        return response()->json($gift_card);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GiftCardRequest $request, GiftCard $gift_card)
    {
        $gift_card->update($request->validated());

        return back()->with('message', __('{record} has been {action}.', ['record' => 'GiftCard', 'action' => 'updated']));
    }

    /**
     * Display the specified resource by number.
     */
    public function details($number)
    {
        $gift_card = GiftCard::where('number', $number)->firstOrFail();

        return response()->json($gift_card);
    }

    /**
     * Display a usage logs of the resource.
     */
    public function topup(Request $request, GiftCard $gift_card)
    {
        $form = $request->validate([
            'description'      => 'nullable',
            'amount'           => 'required|numeric|min:0',
            'use_award_points' => 'nullable|boolean',
            'award_points'     => 'nullable|integer|min:0' . ($gift_card->customer?->points ? '|max:' . $gift_card->customer->points : ''),
        ]);

        $gift_card->increaseBalance($form['amount'], [
            'reference'   => auth()->user(),
            'description' => $form['description'] ?? 'Gift card topup',
        ]);

        if ($gift_card->customer && ($form['award_points'] ?? null) && ($form['use_award_points'] ?? null)) {
            $gift_card->customer?->awardPoints()->create([
                'gift_card_id' => $gift_card->id,
                'store_id'     => $gift_card->store_id,
                'value'        => 0 - $form['award_points'],
                'details'      => 'Award points used to top up gift card #' . $gift_card->number,
            ]);
        }

        return back()->with('message', __('{record} has been {action}.', ['record' => 'GiftCard', 'action' => 'updated']));
    }

    /**
     * Display a usage logs of the resource.
     */
    public function logs(GiftCard $gift_card)
    {
        return Inertia::render('Sma/Order/GiftCard/Logs', [
            'gift_card'  => $gift_card,
            'pagination' => new Collection(
                $gift_card->tracks()->orderByDesc('id')->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GiftCard $gift_card)
    {
        if ($gift_card->{$gift_card->deleted_at ? 'forceDelete' : 'delete'}()) {
            return to_route('gift_cards.index')->with('message', __('{record} has been {action}.', ['record' => 'Gift card', 'action' => 'deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function restore(GiftCard $gift_card)
    {
        $gift_card->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Gift card', 'action' => 'restored']));
    }

    public function destroyPermanently(GiftCard $gift_card)
    {
        if ($gift_card->forceDelete()) {
            return to_route('gift_cards.index')->with('message', __('{record} has been {action}.', ['record' => 'Gift card', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
