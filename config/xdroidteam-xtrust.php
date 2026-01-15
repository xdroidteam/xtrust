<?php

return [
    'route_middleware' => \XdroidTeam\XTrust\Middleware\XTrustPermissionMiddleware::class,
    'cache_tag' => 'users_permissions_roles_cache',
];
