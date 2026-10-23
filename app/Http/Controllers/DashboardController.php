<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ProfileService $profile): Response
    {
        $user = $request->user();
        $overview = collect($profile->overview($user));

        $expiring = $user->documents()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addMonths(6))
            ->orderBy('expires_at')
            ->get(['id', 'user_id', 'type', 'title', 'expires_at']);

        $draft = $user->applications()->where('status', 'draft')->with('template:id,name_bn,name_en')->latest()->first();

        return Inertia::render('Dashboard', [
            'completeness' => $profile->completeness($user),
            'documentCount' => $user->documents()->count(),
            'needsReview' => $overview->where('needsReview', true)->count(),
            'expiring' => $expiring->map(fn ($d) => [
                'id' => $d->id,
                'type' => $d->type,
                'title' => $d->title,
                'expiresAt' => $d->expires_at->toDateString(),
                'expired' => $d->expires_at->isPast(),
            ]),
            'types' => config('formsathi.document_types'),
            'draft' => $draft ? [
                'id' => $draft->id,
                'name' => ['bn' => $draft->template->name_bn, 'en' => $draft->template->name_en],
            ] : null,
        ]);
    }
}
