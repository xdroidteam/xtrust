<?php namespace XdroidTeam\XTrust\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use XdroidTeam\XTrust\Traits\XTrustRoleTrait;

class XTrustRole extends Model
{
    use Sluggable;
    use XTrustRoleTrait;

    protected $table = 'roles';
    protected $guarded = [];

    public function sluggable()
    {
        return [
            'name' => [
                'source' => 'display_name'
            ]
        ];
    }

}
