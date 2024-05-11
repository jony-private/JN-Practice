<?php

namespace practice\listener;

use JsonException;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
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
    }
}