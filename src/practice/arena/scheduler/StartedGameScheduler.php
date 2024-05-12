<?php

namespace practice\arena\scheduler;

use pocketmine\scheduler\Task;
use practice\arena\ArenaRegistry;

class StartedGameScheduler extends Task {

    public function onRun(): void {
        foreach (ArenaRegistry::getInstance()->getArenas() as $arenas) {
            if ($arenas->isActive()) {
                $arenas->schedule();
            }
        }
    }
}