<?php namespace XdroidTeam\XTrust;

use Auth;

class XTrust {
    public static function hasPermission($perm){
        if (Auth::guest())
            return false;

        return Auth::user()->hasPermission($perm);
    }

    public static function hasOneOfPermissions($perms){
        if (Auth::guest())
            return false;

        return Auth::user()->hasOneOfPermissions($perms);
    }

    public static function hasPermissions($perms){
        if (Auth::guest())
            return false;

        return Auth::user()->hasPermissions($perms);
    }

    public static function hasRole($role){
        if (Auth::guest())
            return false;

        return Auth::user()->hasRole($role);
    }

    public static function hasOneOfRoles($roles){
        if (Auth::guest())
            return false;

        return Auth::user()->hasOneOfRoles($roles);
    }

    public static function hasRoles($roles){
        if (Auth::guest())
            return false;

        return Auth::user()->hasRoles($roles);
    }
}
