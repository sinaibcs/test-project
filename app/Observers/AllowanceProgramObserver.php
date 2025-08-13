<?php

namespace App\Observers;

use App\Models\AllowanceProgram;
use Illuminate\Support\Facades\Cache;

class AllowanceProgramObserver
{
    /**
     * Handle the AllowanceProgram "created" event.
     */
    public function created(AllowanceProgram $allowanceProgram): void
    {
        $this->clearCache();
    }

    /**
     * Handle the AllowanceProgram "updated" event.
     */
    public function updated(AllowanceProgram $allowanceProgram): void
    {
        $this->clearCache();
    }

    /**
     * Handle the AllowanceProgram "deleted" event.
     */
    public function deleted(AllowanceProgram $allowanceProgram): void
    {
        $this->clearCache();
    }

    /**
     * Handle the AllowanceProgram "restored" event.
     */
    public function restored(AllowanceProgram $allowanceProgram): void
    {
        $this->clearCache();
    }

    /**
     * Handle the AllowanceProgram "forceDeleted" event.
     */
    public function forceDeleted(AllowanceProgram $allowanceProgram): void
    {
        $this->clearCache();
    }

    /**
     * Clear related cached data.
     */
    protected function clearCache(): void
    {
        Cache::tags(['allowance_programs'])->flush();
    }
}
