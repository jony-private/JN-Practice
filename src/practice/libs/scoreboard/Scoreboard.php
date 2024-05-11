<?php

namespace practice\libs\scoreboard;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

class Scoreboard {
    use SingletonTrait {
        reset as private;
        setInstance as private;
    }

    private array $scoreboards = [];

    public function create(Player $player, string $title): void {
        if (isset($this->scoreboards[$player->getName()])) {
            $this->delete($player);
        }
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = "sidebar";
        $pk->objectiveName = $player->getName();
        $pk->displayName = $title;
        $pk->criteriaName = "dummy";
        $pk->sortOrder = 0;
        $player->getNetworkSession()->sendDataPacket($pk);
        $this->scoreboards[$player->getName()] = $player->getName();
    }

    public function delete(Player $player): void {
        if (isset($this->scoreboards[$player->getName()])) {
            $player_name = $this->send($player);
            $pk = new RemoveObjectivePacket();
            $pk->objectiveName = $player_name;
            $player->getNetworkSession()->sendDataPacket($pk);
            unset($this->scoreboards[$player->getName()]);
        }
    }

    public function addLine(Player $player, int $line, string $line_content): void {
        if (!isset($this->scoreboards[$player->getName()])) {
            return;
        }
        if ($line > 15 || $line < 1) {
            return;
        }
        $player_name = $this->send($player);
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $player_name;
        $entry->type = $entry::TYPE_FAKE_PLAYER;
        $entry->customName = $line_content;
        $entry->score = $line;
        $entry->scoreboardId = $line;
        $pk = new SetScorePacket();
        $pk->type = $pk::TYPE_CHANGE;
        $pk->entries[] = $entry;
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function send(Player $player): ?string {
        return $this->scoreboards[$player->getName()] ?? null;
    }
}