<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\FinancialYear
 *
 * @property int $id
 * @property string $financialYear
 * @property string $startDate
 * @property string $endDate
 * @property int $status
 * @property int $version
 * @property \Illuminate\Support\Carbon|null $deletedAt
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialYear newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialYear newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialYear onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialYear query()
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialYear whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialYear whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialYear whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialYear whereFinancialYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialYear whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialYear whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialYear whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialYear whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialYear whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialYear withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|FinancialYear withoutTrashed()
 * @mixin \Eloquent
 */
class FinancialYear extends Model
{
    use HasFactory,SoftDeletes;
     protected $fillable = [
        'financial_year','start_date','end_date','status'
        // Add other fillable attributes here if any
    ];
    public function newQuery($excludeDeleted = true)
    {
        return parent::newQuery($excludeDeleted)
            ->orderBy('start_date', 'desc');
    }
}
