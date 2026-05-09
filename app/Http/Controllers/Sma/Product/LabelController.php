<?php

namespace App\Http\Controllers\Sma\Product;

use Inertia\Inertia;
use App\Http\Controllers\Controller;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Sma/Product/Label');
    }
}
