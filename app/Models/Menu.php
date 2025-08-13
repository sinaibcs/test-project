<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Permission;

/**
 * App\Models\Menu
 *
 * @property int $id
 * @property int $pageLinkId
 * @property int|null $parentId
 * @property string $labelNameEn
 * @property string $labelNameBn
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $deletedAt
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Menu> $children
 * @property-read int|null $childrenCount
 * @property-read Permission $pageLink
 * @property-read Menu|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereLabelNameBn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereLabelNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu wherePageLinkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu withoutTrashed()
 * @property string $linkType
 * @property string|null $link
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereLinkType($value)
 * @mixin \Eloquent
 */
class Menu extends Model
{
    use HasFactory,SoftDeletes;

//    public function newQuery($excludeDeleted = true)
//    {
//        return parent::newQuery($excludeDeleted)
//            ->orderBy('order', 'asc');
//            // ->orderBy('label_name_en', 'asc');
//    }
    protected $fillable = [
        'page_link_id',
        'parent_id',
        'label_name_en',
        'label_name_bn',
        'link_type',
        'link',
        'order',
    ];
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }


    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function pageLink()
    {
        return $this->belongsTo(Permission::class, 'page_link_id');
    }
}
