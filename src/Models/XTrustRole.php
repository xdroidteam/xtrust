<?php namespace XdroidTeam\XTrust\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Support\Facades\Cache;

class XTrustRole extends Model
{
    use Sluggable;

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

    public function attachPermission($permID){
        if(array_key_exists($permID, $this->getPermissions()))
            return;
        $this->permissions()->attach($permID);
    }

    public function detachPermission($permID){
        if(!array_key_exists($permID, $this->getPermissions()))
            return;
        $this->permissions()->detach($permID);
    }

    public function attachPermissions($permIDs){
        foreach ($permIDs as $permID)
            $this->attachPermission($permID);
    }

    public function detachPermissions($permIDs){
        foreach ($permIDs as $permID)
            $this->detachPermission($permID);
    }

    public function getPermissions(){
        $permissions = [];
        foreach ($this->permissions as $permission)
            $permissions[$permission->id] = $permission->name;

        return $permissions;
    }

    public function updatePermissions($data){
        if (is_null($data))
            $data = [];

        $permissionIDs = Permission::lists('id')->all();

        foreach ($permissionIDs as $permissionID) {
            if (array_key_exists($permissionID, $data))
                $this->attachPermission($permissionID);
            else
                $this->detachPermission($permissionID);
        }

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

    public function users(){
        return $this->belongsToMany('App\Models\User', 'role_permission_user', 'role_id', 'user_id');
    }

    public function permissions(){
        return $this->belongsToMany('App\Models\Permission', 'permission_role', 'role_id', 'permission_id');
    }

    public static function boot(){
        parent::boot();

        static::deleting(function($role) {
            if (!method_exists('App\Models\Role', 'bootSoftDeletes')) {
                $role->users()->sync([]);
                $role->permissions()->sync([]);
            }

            return true;
        });
    }

    public static function getRolesForEdit(){
        $ret = ['role_list' => [], 'role_permissions' => []];
        $roles = self::leftJoin('permission_role', 'roles.id', '=', 'permission_role.role_id')
                        ->leftJoin('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                        ->select('roles.id AS role_id', 'roles.display_name AS role_name', 'permissions.display_name AS permission_name')
                        ->get();

        foreach ($roles as $role) {
            $ret['role_list'][$role->role_id] = $role->role_name;
            if (array_key_exists($role->role_id, $ret['role_permissions']))
                $ret['role_permissions'][$role->role_id][] = $role->permission_name;
            else
                $ret['role_permissions'][$role->role_id] = [ $role->permission_name ];
        }

        return $ret;
    }
}
