<?php

namespace practice\libs\bossbar;

use practice\libs\bossbar\modules\PacketListener;
use pocketmine\plugin\Plugin;
use pocketmine\utils\SingletonTrait;

class BossbarAPI {
    use SingletonTrait {
        setInstance as private;
        reset as private;
    }

    public function emmit(Plugin $plugin): void {
        PacketListener::register($plugin);
    }
}