<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\CareerItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/** Education, work history and skills: what the job kit is allowed to use. */
class CareerController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Career/Index', [
            'kinds' => config('formsathi.career'),
            'items' => $request->user()->careerItems()->get()->map(fn (CareerItem $item) => [
                'id' => $item->id,
                'kind' => $item->kind,
                'data' => $item->fields(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $kind = $request->validate(['kind' => ['required', Rule::in(CareerItem::KINDS)]])['kind'];
        $user = $request->user();

        $user->careerItems()->create([
            'user_id' => $user->id,
            'kind' => $kind,
            'data' => json_encode($this->validated($request, $kind), JSON_UNESCAPED_UNICODE),
            'position' => $user->careerItems()->where('kind', $kind)->count(),
        ]);

        AccessLog::record($user, 'career.update');

        return back()->with('status', __('app.profile_saved'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $item = $request->user()->careerItems()->findOrFail($id);
        $item->update(['data' => json_encode($this->validated($request, $item->kind), JSON_UNESCAPED_UNICODE)]);

        AccessLog::record($request->user(), 'career.update');

        return back()->with('status', __('app.profile_saved'));
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $request->user()->careerItems()->findOrFail($id)->delete();

        return back();
    }

    /** @return array<string, string> only this kind's fields, trimmed, empty ones dropped */
    private function validated(Request $request, string $kind): array
    {
        $rules = [];

        foreach (config("formsathi.career.{$kind}.fields") as $key => $field) {
            $rules["data.{$key}"] = [
                ($field['required'] ?? false) ? 'required' : 'nullable',
                ...match ($field['type']) {
                    'month' => ['date_format:Y-m'],
                    'number' => ['integer', 'between:1950,2100'],
                    'select' => [Rule::in(array_keys($field['options']))],
                    'textarea' => ['string', 'max:1500'],
                    default => ['string', 'max:150'],
                },
            ];
        }

        return array_filter(
            array_map(fn ($v) => is_string($v) ? trim($v) : $v, $request->validate($rules)['data'] ?? []),
            fn ($v) => $v !== null && $v !== '',
        );
    }
}
