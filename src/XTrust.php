<?php namespace XdroidTeam\XTrust;

use Auth;
use Illuminate\Contracts\Auth\Authenticatable;

class XTrust {
    public static function getUser(Authenticatable $user = null) {
        if($user) {
            return config('auth.providers.users.model')::findOrFail($user);
        } else {
            return Auth::user();
        }
    }

    public static function hasPermission($perm, Authenticatable $user = null){
        if (Auth::guest())
            return false;

        return static::getUser($user)->hasPermission($perm);
    }

    public static function hasOneOfPermissions($perms, Authenticatable $user = null){
        if (Auth::guest())
            return false;

        return static::getUser($user)->hasOneOfPermissions($perms);
    }

    public static function hasPermissions($perms, Authenticatable $user = null){
        if (Auth::guest())
            return false;

        return static::getUser($user)->hasPermissions($perms);
    }

    public static function hasRole($role, Authenticatable $user = null){
        if (Auth::guest())
            return false;

        return static::getUser($user)->hasRole($role);
    }

    public static function hasOneOfRoles($roles, Authenticatable $user = null){
        if (Auth::guest())
            return false;

        return static::getUser($user)->hasOneOfRoles($roles);
    }

    public static function hasRoles($roles, Authenticatable $user = null){
        if (Auth::guest())
            return false;

        return static::getUser($user)->hasRoles($roles);
    }
}
