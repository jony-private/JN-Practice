<?php

namespace practice\emmiter;

use JsonException;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use practice\arena\ArenaRegistry;
use practice\commands\ArenaCommand;
use practice\libs\bossbar\BossbarAPI;
use practice\listener\PlayerListener;
use practice\Practice;
use practice\scheduler\ScoreboardScheduler;
use practice\session\SessionRegistry;
use practice\utils\PermissionRegistry;

class EmmiterLogger {
    use SingletonTrait {
        setInstance as private;
        reset as private;
    }

    public function emmit(): void {
        BossbarAPI::getInstance()->emmit(Practice::getInstance());
        PermissionRegistry::getInstance()->emmit();
        SessionRegistry::getInstance()->emmit();
        ArenaRegistry::getInstance()->emmit();
        foreach ([new PlayerListener()] as $item) {
            Server::getInstance()->getPluginManager()->registerEvents($item, Practice::getInstance());
        }
        Server::getInstance()->getCommandMap()->registerAll("JN-Practice", [
            new ArenaCommand()
        ]);
        Practice::getInstance()->getScheduler()->scheduleRepeatingTask(new ScoreboardScheduler(), 3 * 20);
    }

    /**
     * @throws JsonException
     */
    public function demmit(): void {
        foreach (SessionRegistry::getInstance()->getSessions() as $session) {
            $session->save();
        }
        foreach (ArenaRegistry::getInstance()->getArenas() as $arena) {
            $arena->save();
        }
    }
}