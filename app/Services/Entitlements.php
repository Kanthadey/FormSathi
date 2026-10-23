<?php

namespace App\Services;

use App\Models\User;
use App\Support\Bangla;
use Illuminate\Validation\ValidationException;

/**
 * What a user's plan allows. Free: 10 papers and 3 form assists a month
 * (a form application or a job kit). A form pack adds one assist. Premium
 * removes the assist limit and raises storage.
 */
class Entitlements
{
    public function documentLimit(User $user): int
    {
        return $user->isPremium()
            ? config('formsathi.premium_plan.max_documents')
            : config('formsathi.free_plan.max_documents');
    }

    public function freeAssistsUsed(User $user): int
    {
        $since = now()->startOfMonth();

        return $user->applications()->where('funded_by', 'free')->where('created_at', '>=', $since)->count()
            + $user->jobKits()->where('funded_by', 'free')->where('created_at', '>=', $since)->count();
    }

    public function freeAssistsLeft(User $user): int
    {
        return max(0, config('formsathi.free_plan.form_assists_per_month') - $this->freeAssistsUsed($user));
    }

    /** Whether a new assist is allowed, without using anything up. */
    public function canAssist(User $user): bool
    {
        return $user->isPremium() || $this->freeAssistsLeft($user) > 0 || $user->form_credits > 0;
    }

    /**
     * Decides what pays for a new assist and takes a credit if needed.
     * Returns "premium", "free" or "credit"; throws when nothing is left.
     */
    public function useAssist(User $user, string $errorKey = 'form'): string
    {
        if ($user->isPremium()) {
            return 'premium';
        }

        if ($this->freeAssistsLeft($user) > 0) {
            return 'free';
        }

        if ($user->newQuery()->whereKey($user->id)->where('form_credits', '>', 0)->decrement('form_credits')) {
            $user->refresh();

            return 'credit';
        }

        throw ValidationException::withMessages([
            $errorKey => __('app.form_limit', ['max' => Bangla::localDigits(config('formsathi.free_plan.form_assists_per_month'))]),
        ]);
    }

    /** @return array<string, mixed> for the plan screen and shared props */
    public function summary(User $user): array
    {
        return [
            'premium' => $user->isPremium(),
            'premiumUntil' => $user->premium_until?->toDateString(),
            'credits' => $user->form_credits,
            'freeAssistsLeft' => $this->freeAssistsLeft($user),
            'freeAssistsPerMonth' => config('formsathi.free_plan.form_assists_per_month'),
            'documentLimit' => $this->documentLimit($user),
            'documentsUsed' => $user->documents()->count(),
        ];
    }
}
