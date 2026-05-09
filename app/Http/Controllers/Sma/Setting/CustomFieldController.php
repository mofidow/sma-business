<?php

namespace App\Http\Controllers\Sma\Setting;

use Inertia\Inertia;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Setting\CustomField;
use App\Http\Requests\Sma\Setting\CustomFieldRequest;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Sma/Setting/CustomField/Index', [
            'types'  => CustomField::$types,
            'models' => CustomField::$models,

            'custom_fields' => new Collection(CustomField::latest()->orderBy('name')->paginate()->withQueryString()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomFieldRequest $request)
    {
        $custom_field = CustomField::create($request->validated());

        return redirect()->route('custom_fields.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Custom field'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomField $custom_field)
    {
        return $custom_field;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomFieldRequest $request, CustomField $custom_field)
    {
        $custom_field->update($request->validated());

        return redirect()->route('custom_fields.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Custom field'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomField $custom_field)
    {
        if ($custom_field->{$custom_field->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Custom field'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Custom field'),
            'action' => __('deleted'),
        ]));
    }
}
