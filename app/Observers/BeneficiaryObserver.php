<?php

namespace App\Observers;

use App\Models\Beneficiary;
use Illuminate\Support\Facades\Cache;

class BeneficiaryObserver
{
    /**
     * Handle the Beneficiary "created" event.
     */
    public function created(Beneficiary $beneficiary): void
    {
        Cache::tags(['user:' . auth()->id() . ':beneficiaries'])->flush();
        Cache::forget('duplicate_beneficiaries');
    }

    /**
     * Handle the Beneficiary "updated" event.
     */
    public function updated(Beneficiary $beneficiary): void
    {
        Cache::tags(['user:' . auth()->id() . ':beneficiaries'])->flush();
        Cache::forget('duplicate_beneficiaries');
    }

    /**
     * Handle the Beneficiary "deleted" event.
     */
    public function deleted(Beneficiary $beneficiary): void
    {
        Cache::tags(['user:' . auth()->id() . ':beneficiaries'])->flush();
        Cache::forget('duplicate_beneficiaries');
    }

    /**
     * Handle the Beneficiary "restored" event.
     */
    public function restored(Beneficiary $beneficiary): void
    {
        Cache::tags(['user:' . auth()->id() . ':beneficiaries'])->flush();
        Cache::forget('duplicate_beneficiaries');
    }

    /**
     * Handle the Beneficiary "force deleted" event.
     */
    public function forceDeleted(Beneficiary $beneficiary): void
    {
        Cache::tags(['user:' . auth()->id() . ':beneficiaries'])->flush();
        Cache::forget('duplicate_beneficiaries');
    }
}
