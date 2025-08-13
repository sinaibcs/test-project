<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * App\Models\ActivityModel
 *
 * @property-read Model|\Eloquent $causer
 * @property-read \Illuminate\Support\Collection $changes
 * @property-read Model|\Eloquent $subject
 * @method static Builder|Activity causedBy(\Illuminate\Database\Eloquent\Model $causer)
 * @method static Builder|Activity forBatch(string $batchUuid)
 * @method static Builder|Activity forEvent(string $event)
 * @method static Builder|Activity forSubject(\Illuminate\Database\Eloquent\Model $subject)
 * @method static Builder|Activity hasBatch()
 * @method static Builder|Activity inLog(...$logNames)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel newQuery()
 * @method static \Illuminate\Database\Query\Builder|ActivityModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel query()
 * @method static \Illuminate\Database\Query\Builder|ActivityModel withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ActivityModel withoutTrashed()
 * @property int $id
 * @property string|null $log_name
 * @property string $description
 * @property string|null $subject_type
 * @property string|null $event
 * @property int|null $subject_id
 * @property string|null $causer_type
 * @property int|null $causer_id
 * @property \Illuminate\Support\Collection|null $properties
 * @property string|null $batch_uuid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel whereBatchUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel whereCauserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel whereCauserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel whereLogName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel whereUpdatedAt($value)
 * @property string|null $logName
 * @property string|null $subjectType
 * @property int|null $subjectId
 * @property string|null $causerType
 * @property int|null $causerId
 * @property string|null $batchUuid
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property \Illuminate\Support\Carbon|null $deletedAt
 * @mixin \Eloquent
 */
class ActivityModel extends Activity
{
    use SoftDeletes;

    // default ordering ASC for this model
    // protected $defaultOrderColumn = 'log_name';
    // protected $defaultOrderDirection = 'asc';
    public function newQuery($excludeDeleted = true)
    {
        return parent::newQuery($excludeDeleted)
            ->orderBy('created_at', 'desc');
    }
    public function subject(): MorphTo
    {
        if (config('activitylog.subject_returns_soft_deleted_models')) {
            return $this->morphTo()->withTrashed();
//            return $this->morphTo()->withoutGlobalScope(SoftDeletingScope::class);

        }

        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        if (config('activitylog.subject_returns_soft_deleted_models')) {
            return $this->morphTo()->withTrashed();
//            return $this->morphTo()->withoutGlobalScope(SoftDeletingScope::class);
        }
        return $this->morphTo();
    }

    /**
     */

}
