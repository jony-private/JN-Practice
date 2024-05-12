<?php

namespace practice\utils;

use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\utils\SingletonTrait;

class PermissionRegistry {
    use SingletonTrait {
        setInstance as private;
        reset as private;
    }

    public const ARENA_COMMAND = "practice.command.arena";

    public function emmit(): void {
        foreach ([self::ARENA_COMMAND] as $permissions) {
            $this->registerPermission($permissions);
        }
    }

    public function registerPermission(string $permissionBin): void {
        $permission = new Permission($permissionBin);
        $operator = PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_OPERATOR);
        DefaultPermissions::registerPermission($permission, [$operator]);
    }
}