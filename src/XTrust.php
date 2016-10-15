<?php namespace XdroidTeam\XTrust;

use Auth;

class XTrust {
    public static function hasPermission($perm){
        if (Auth::guest())
            return false;

        return Auth::user()->hasPermission($perm);
    }

    public static function hasPermissions($perms){
        if (Auth::guest())
            return false;

        return Auth::user()->hasPermissions($perms);
    }
}
