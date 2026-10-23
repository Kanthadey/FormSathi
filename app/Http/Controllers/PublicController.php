<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Pages anyone can read: the landing page, pricing, privacy and terms. */
class PublicController extends Controller
{
    public function home(Request $request): Response|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Public/Home', ['products' => config('formsathi.products')]);
    }

    public function pricing(): Response
    {
        return Inertia::render('Public/Pricing', [
            'products' => config('formsathi.products'),
            'free' => config('formsathi.free_plan'),
            'premium' => config('formsathi.premium_plan'),
        ]);
    }

    public function privacy(): Response
    {
        return Inertia::render('Public/Privacy');
    }

    public function terms(): Response
    {
        return Inertia::render('Public/Terms');
    }
}
