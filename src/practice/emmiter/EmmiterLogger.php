<?php

namespace practice\emmiter;

use JsonException;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use practice\libs\bossbar\BossbarAPI;
use practice\listener\PlayerListener;
use practice\Practice;
use practice\scheduler\ScoreboardScheduler;
use practice\session\SessionRegistry;

class EmmiterLogger {
    use SingletonTrait {
        setInstance as private;
        reset as private;
    }

    public function emmit(): void {
        BossbarAPI::getInstance()->emmit(Practice::getInstance());
        SessionRegistry::getInstance()->emmit();
        foreach ([new PlayerListener()] as $item) {
            Server::getInstance()->getPluginManager()->registerEvents($item, Practice::getInstance());
        }
        Practice::getInstance()->getScheduler()->scheduleRepeatingTask(new ScoreboardScheduler(), 3 * 20);
    }

    /**
     * @throws JsonException
     */
    public function demmit(): void {
        foreach (SessionRegistry::getInstance()->getSessions() as $session) {
            $session->save();
        }
    }
}