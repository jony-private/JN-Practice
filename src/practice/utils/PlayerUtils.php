<?php

namespace practice\utils;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

class PlayerUtils {
    use SingletonTrait {
        setInstance as private;
        reset as private;
    }

    public function sendLobbyItems(Player $player): void {
        $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
        $player->getEffects()->clear();
        $player->getArmorInventory()->clearAll();
        $player->setHealth(20.0);
        $player->getHungerManager()->setFood(20.0);
        $player->setScale(1.0);
        $inventory = $player->getInventory();
        $inventory->setItem(0, VanillaItems::DIAMOND_SWORD()->setCustomName(TextFormat::colorize("&l&eRanked Duels")));
        $inventory->setItem(1, VanillaItems::IRON_SWORD()->setCustomName(TextFormat::colorize("&l&eUnranked Duels")));
        $inventory->setItem(2, VanillaItems::GOLDEN_AXE()->setCustomName(TextFormat::colorize("&l&eFFA Duels")));
        $inventory->setItem(7, VanillaItems::NETHER_STAR()->setCustomName(TextFormat::colorize("&l&eParties")));
        $inventory->setItem(8, VanillaBlocks::REDSTONE_REPEATER()->asItem()->setCustomName(TextFormat::colorize("&l&eSettings")));
    }

    public function sendJoinMessage(Player $player): void {
        $player->sendMessage(TextFormat::colorize("&l&eWelcome to JN-Practice"));
        $player->sendMessage(" ");
        $player->sendMessage(TextFormat::colorize("&7Some of these features of this season:"));
        $player->sendMessage(TextFormat::colorize("&l&e| &r&7Ranked and Unranked Duels."));
        $player->sendMessage(TextFormat::colorize("&l&e| &r&7FFA Duels."));
        $player->sendMessage(TextFormat::colorize("&l&e| &r&7Parties system."));
        $player->sendMessage(" ");
        $player->sendMessage(TextFormat::colorize("&7Join our discord &9discord.myserver.com &7for more news."));
    }

}