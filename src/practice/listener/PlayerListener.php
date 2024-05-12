<?php

namespace practice\listener;

use JsonException;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\libs\bossbar\modules\BossBar;
use practice\libs\scoreboard\Scoreboard;
use practice\session\SessionRegistry;
use practice\utils\PlayerUtils;

class PlayerListener implements Listener {

    /**
     * @throws JsonException
     */
    public function login(PlayerLoginEvent $event): void {
        $player = $event->getPlayer();
        if (SessionRegistry::getInstance()->existFile($player->getName())) {
            SessionRegistry::getInstance()->load($player->getName());
        } else {
            SessionRegistry::getInstance()->create($player->getName());
        }
    }

    public function join(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        PlayerUtils::getInstance()->sendJoinMessage($player);
        PlayerUtils::getInstance()->sendLobbyItems($player);
        $event->setJoinMessage(TextFormat::colorize("&8[&e+&8] &f" . $player->getName()));
        (new BossBar())->setTitle(TextFormat::colorize("&l&eJN-Practice &7| &r&7NA #1"))->setPercentage(100.0)->addPlayer($player);
    }

    public function worldChange(EntityTeleportEvent $event): void {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            $oldWorld = $event->getFrom()->getWorld();
            $newWorld = $event->getTo()->getWorld();
            if ($oldWorld->getFolderName() !== $newWorld->getFolderName()) {
                Scoreboard::getInstance()->delete($player);
                (new BossBar())->hideFrom([$player]);
            }
        }
    }

    /**
     * @throws JsonException
     */
    public function quit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        if (SessionRegistry::getInstance()->exists($player->getName())) {
            $session = SessionRegistry::getInstance()->get($player->getName());
            $session->save();
            SessionRegistry::getInstance()->remove($player->getName());
        }
        $event->setQuitMessage(TextFormat::colorize("&8[&e-&8] &f" . $player->getName()));
    }
}