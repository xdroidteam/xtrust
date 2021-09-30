<?php namespace XdroidTeam\XTrust\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\SoftDeletes;

trait XTrustUserTrait
{
    private $rolesPermissions;

    public function getRolesPermissions(){
        if ($this->rolesPermissions)
            return $this->rolesPermissions;

        return $this->rolesPermissions = Cache::tags(env('APP_KEY'), 'users_permissions_roles_cache')->remember($this->getCacheKey(), env('CACHE_TIME', 30), function () {
            $rolesPermissions = ['permissions' => [], 'roles' => [], 'role_permissions' => [], 'user_permissions' => []];

            foreach ($this->getPermissionsQuery()->get() as $key => $permission) {
                if (is_null($permission->role_id)){
                    $rolesPermissions['user_permissions'][$permission->user_permission_id] = $permission->enabled ? $permission->user_permission_name : false;
                    continue;
                }
                $rolesPermissions['role_permissions'][$permission->role_permission_id] = $permission->role_permission_name;
                $rolesPermissions['roles'][$permission->role_id] = $permission->role_name;
            }
            $rolesPermissions['permissions'] = array_replace($rolesPermissions['role_permissions'], $rolesPermissions['user_permissions']);

            // foreach ($this->roles()->get() as $role) {
            //     foreach ($role->permissions()->get() as $permission) {
            //         $rolesPermissions['permissions'][$permission->id] = $permission->name;
            //         $rolesPermissions['role_permissions'][$permission->id] = $permission->name;
            //     }
            //     $rolesPermissions['roles'][$role->id] = $role->name;
            // }
            //
            // foreach ($this->permissions()->select('permissions.*', 'role_permission_user.enabled AS enabled')->get() as $permission) {
            //     $rolesPermissions['permissions'][$permission->id] = $permission->enabled ? $permission->name : false;
            //     $rolesPermissions['user_permissions'][$permission->id] = $permission->enabled ? $permission->name : false;
            // }

            return $rolesPermissions;
        });
    }

    public function getPermissionsQuery(){
        $query = self::where('users.id', '=', $this->id)
                            ->leftJoin('role_permission_user', 'role_permission_user.user_id', '=', 'users.id')
                            ->leftJoin('roles', 'roles.id', '=', 'role_permission_user.role_id')
                            ->leftJoin('permission_role', 'role_permission_user.role_id', '=', 'permission_role.role_id')
                            ->leftJoin('permissions AS role_permission', 'role_permission.id', '=', 'permission_role.permission_id')
                            ->leftJoin('permissions', 'permissions.id', '=', 'role_permission_user.permission_id')
                            ->select(   'role_permission_user.enabled', 'role_permission_user.permission_id AS user_permission_id',
                                        'role_permission_user.role_id', 'permissions.name AS user_permission_name',
                                        'role_permission.name AS role_permission_name', 'permission_role.permission_id AS role_permission_id',
                                        'roles.name AS role_name');

        if($this->useSoftDeleting()){
            $query->withTrashed();
        }

        return $query;
    }

    public function getRoles(){
        return $this->getRolesPermissions()['roles'];
    }

    public function getPermissions(){
        return $this->getRolesPermissions()['permissions'];
    }

    public function getRolePermissions(){
        return $this->getRolesPermissions()['role_permissions'];
    }

    public function getUserPermissions(){
        return $this->getRolesPermissions()['user_permissions'];
    }

    public function clearCache(){
        Cache::tags(env('APP_KEY'), 'users_permissions_roles_cache')->forget($this->getCacheKey());
        $this->rolesPermissions = false;
    }

    public function hasPermission($perm){
        return (in_array($perm, $this->getPermissions()));
    }

    public function hasOneOfPermissions($perms){
        foreach ($perms as $key => $perm) {
            if ($this->hasPermission($perm))
                return true;
        }
        return false;
    }

    public function hasPermissions($perms){
        foreach ($perms as $key => $perm) {
            if (!$this->hasPermission($perm))
                return false;
        }
        return true;
    }

    public function hasRole($role){
        return (in_array($role, $this->getRoles()));
    }

    public function hasOneOfRoles($roles){
        foreach ($roles as $key => $role) {
            if ($this->hasRole($role))
                return true;
        }
        return false;
    }

    public function hasRoles($roles){
        foreach ($roles as $key => $role) {
            if (!$this->hasRole($role))
                return false;
        }
        return true;
    }

    public function attachRole($roleID){
        if (array_key_exists($roleID, $this->getRoles()))
            return;

        $this->roles()->attach($roleID);
        $this->clearCache();
    }

    public function detachRole($roleID){
        if (!array_key_exists($roleID, $this->getRoles()))
            return;

        $this->roles()->detach($roleID);
        $this->clearCache();
    }

    public function attachRoles($roleIDs){
        foreach ($roleIDs as $key => $roleID)
            $this->attachRole($roleID);
    }

    public function detachRoles($roleIDs){
        foreach ($roleIDs as $key => $roleID)
            $this->detachRole($roleID);
    }

    public function attachPermission($permID){
        $permissions = $this->getPermissions();
        if (array_key_exists($permID, $permissions) && $permissions[$permID])
            return;

        $rolePermissions = $this->getRolePermissions();

        if (array_key_exists($permID, $permissions) && !array_key_exists($permID, $rolePermissions))
            $this->permissions()->updateExistingPivot($permID, ['enabled' => true]);
        elseif (array_key_exists($permID, $permissions) && array_key_exists($permID, $rolePermissions))
            $this->permissions()->detach($permID);
        else
            $this->permissions()->attach($permID, ['enabled' => true]);

        $this->clearCache();
    }

    public function detachPermission($permID){
        $permissions = $this->getPermissions();
        if (array_key_exists($permID, $permissions) && !$permissions[$permID])
            return;

        $userPermissions = $this->getUserPermissions();
        $rolePermissions = $this->getRolePermissions();

        if (array_key_exists($permID, $rolePermissions) && array_key_exists($permID, $userPermissions))
            $this->permissions()->updateExistingPivot($permID, ['enabled' => false]);
        elseif (!array_key_exists($permID, $rolePermissions) && array_key_exists($permID, $userPermissions))
            $this->permissions()->detach($permID);
        else
            $this->permissions()->attach($permID, ['enabled' => false]);

        $this->clearCache();

    }

    public function attachPermissions($permIDs){
        foreach ($permIDs as $key => $permID)
            $this->attachPermission($permID);
    }

    public function detachPermissions($permIDs){
        foreach ($permIDs as $key => $permID)
            $this->detachPermission($permID);
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function($user) {
            if (!method_exists(Config::get('auth.model'), 'bootSoftDeletes')) {
                $user->roles()->sync([]);
                $user->permissions()->sync([]);
            }

            return true;
        });
    }

    public function roles()
    {
        return $this->belongsToMany('App\Models\Role', 'role_permission_user', 'user_id', 'role_id');
    }

    public function permissions()
    {
        return $this->belongsToMany('App\Models\Permission', 'role_permission_user', 'user_id', 'permission_id');
    }

    private function getCacheKey(){
        $userPrimaryKey = $this->primaryKey;
        return 'xtrust_permissions_for_user_'.$this->$userPrimaryKey;
    }

    public function useSoftDeleting()
    {
        return in_array(SoftDeletes::class, class_uses($this)) && !$this->forceDeleting;
    }
}
