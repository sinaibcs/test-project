<?php

namespace App\Jobs;

use App\Http\Services\Admin\BudgetAllotment\BudgetService;
use App\Models\Budget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class ProcessBudget implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    public function uniqueId()
    {
        return $this->budget->budget_id;
    }

    private Budget $budget;

    /**
     * Create a new job instance.
     */
    public function __construct(Budget $budget)
    {
        $this->budget = $budget->withoutRelations();
    }

    /**
     * Execute the job.
     * @throws \Throwable
     */
    public function handle(BudgetService $budgetService): void
    {
        $budgetService->processBudget($this->budget);
    }
}
