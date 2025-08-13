<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\PMTScore
 *
 * @property int $id
 * @property int $location_id
 * @property float|null $score
 * @property \Illuminate\Support\Carbon|null $deletedAt
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PMTScore> $children
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore query()
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore whereLabelNameBn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore whereLabelNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore wherePageLinkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore withoutTrashed()
 * @property string $linkType
 * @property string|null $link
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore whereLinkType($value)
 * @property int|null $type
 * @property int|null $locationId
 * @property int|null $financialYearId
 * @property int $default
 * @property-read \App\Models\Location|null $assignLocation
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore whereFinancialYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PMTScore whereType($value)
 * @mixin \Eloquent
 */

class PMTScore extends Model
{
    use HasFactory;
    public function newQuery($excludeDeleted = true)
    {
        return parent::newQuery($excludeDeleted)
            ->orderBy('score', 'asc');
    }
    protected $table = 'poverty_score_cut_offs';

    protected $fillable = [
        'location_id',
        'score',
    ];

    
    public function assign_location()
    {
        return $this->belongsTo(Location::class, 'id');
    }
}
