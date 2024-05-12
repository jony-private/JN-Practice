<?php

namespace practice\scheduler;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use practice\arena\ArenaRegistry;
use practice\libs\scoreboard\Scoreboard;

class ScoreboardScheduler extends Task {

    public function onRun(): void {
        $scoreboard = Scoreboard::getInstance();
        foreach (Server::getInstance()->getWorldManager()->getDefaultWorld()->getPlayers() as $players) {
            $scoreboard->create($players, TextFormat::colorize("&l&eJN-Practice"));
            $scoreboard->addLine($players, 1, "");
            $scoreboard->addLine($players, 2, TextFormat::colorize(" &eOnline: &f" . count(Server::getInstance()->getOnlinePlayers())));
            $scoreboard->addLine($players, 3, TextFormat::colorize("&r"));
            $scoreboard->addLine($players, 4, TextFormat::colorize(" &eIn Fights: &f" . ArenaRegistry::getInstance()->getPlayersInFightCount()));
            $scoreboard->addLine($players, 5, TextFormat::colorize(" &eIn Queues: &f" . "0"));
            $scoreboard->addLine($players, 6, TextFormat::colorize("&r&r"));
            $scoreboard->addLine($players, 7, TextFormat::colorize(" &7myserver.com"));
            $scoreboard->addLine($players, 10, TextFormat::colorize("&e"));
            $scoreboard->send($players);
        }
    }
}