<?php namespace XdroidTeam\XTrust\Traits;

use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

trait XTrustPermissionTrait
{
    public function roles(){
        return $this->belongsToMany('App\Models\Role', 'permission_role', 'permission_id', 'role_id');
    }

    public function users(){
        return $this->belongsToMany('App\Models\User', 'role_permission_user', 'permission_id', 'user_id');
    }

    public function save(array $options = []){
        $result = parent::save($options);
        Cache::tags('users_permissions_roles_cache')->flush();
        return $result;
    }

    public function delete(array $options = []){
        $result = parent::delete($options);
        Cache::tags('users_permissions_roles_cache')->flush();
        return $result;
    }

    public function restore(){
        $result = parent::restore();
        Cache::tags('users_permissions_roles_cache')->flush();
        return $result;
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function($permission) {
            if (!method_exists('App\Models\Permission', 'bootSoftDeletes')) {
                $permission->roles()->sync([]);
                $permission->users()->sync([]);
            }

            return true;
        });
    }
}
