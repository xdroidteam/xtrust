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
    
    protected $casts = [
        'custom_data' => 'array',
    ];

    public function sluggable() : array
    {
        return [
            'name' => [
                'source' => 'display_name'
            ]
        ];
    }

}
