<?php

namespace practice\libs\soundapi;

use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;

class SoundAPI {
    use SingletonTrait {
        setInstance as private;
        reset as private;
    }

    public function sendTo(Player $player, string $soundId, int $volume = 10, int $pitch = 1): void {
        $this->getSoundPacket($player, $soundId, $volume, $pitch);
    }

    public function sendToAll(string $soundId, int $volume = 10, int $pitch = 1): void {
        foreach (Server::getInstance()->getOnlinePlayers() as $players) {
            $this->getSoundPacket($players, $soundId, $volume, $pitch);
        }
    }

    public function getSoundPacket(Player $player, string $soundId, int $volume, int $pitch): void {
        $packet = new PlaySoundPacket();
        $packet->x = $player->getPosition()->getX();
        $packet->y = $player->getPosition()->getY();
        $packet->z = $player->getPosition()->getZ();
        $packet->soundName = $soundId;
        $packet->volume = $volume;
        $packet->pitch = $pitch;
        $player->getNetworkSession()->sendDataPacket($packet);
    }
}