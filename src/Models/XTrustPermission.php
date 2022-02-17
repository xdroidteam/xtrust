<?php namespace XdroidTeam\XTrust\Models;

use Illuminate\Database\Eloquent\Model;
use XdroidTeam\XTrust\Traits\XTrustPermissionTrait;

class XTrustPermission extends Model
{
    use XTrustPermissionTrait;
    
    protected $table = 'permissions';
    protected $guarded = [];

    protected $casts = [
        'custom_data' => 'array',
    ];

}
