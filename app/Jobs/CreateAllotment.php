<?php

namespace App\Jobs;

use App\Http\Services\Admin\BudgetAllotment\BudgetService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateAllotment implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $budget_id;

    /**
     * Create a new job instance.
     */
    public function __construct($budget_id)
    {
        $this->budget_id = $budget_id;
    }

    /**
     * Execute the job.
     * @throws \Throwable
     */
    public function handle(BudgetService $budgetService): void
    {
        $budgetService->createAllotment($this->budget_id);
    }
}
