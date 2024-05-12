<?php

namespace practice\arena;

use JsonException;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\Position;
use practice\arena\game\Game;
use practice\arena\game\GameRegistry;
use practice\arena\mode\Mode;
use practice\arena\mode\ModeModule;
use practice\arena\scheduler\StartedGameScheduler;
use practice\database\DataCreator;
use practice\Practice;
use practice\queue\QueueModule;

class ArenaRegistry {
    use SingletonTrait {
        setInstance as private;
        reset as private;
    }

    private array $arenas = [];

    public function emmit(): void {
        QueueModule::getInstance()->createQueueCache();
        ModeModule::getInstance()->emmit();
        if (!is_dir(Practice::getInstance()->getDataFolder() . "arenas")) @mkdir(Practice::getInstance()->getDataFolder() . "arenas");
        foreach (glob(Practice::getInstance()->getDataFolder() . "arenas/" . "*.json") as $file) {
            $data = new Config($file, Config::JSON);
            if (!Server::getInstance()->getWorldManager()->isWorldLoaded($data->get("world"))) {
                Server::getInstance()->getWorldManager()->loadWorld($data->get("world"));
            }
            $world = Server::getInstance()->getWorldManager()->getWorldByName($data->get("world"));
            $spawn1ToArray = explode(":", $data->get("spawn1"));
            $spawn1ToPosition = new Position((int)$spawn1ToArray[0], (int)$spawn1ToArray[1], (int)$spawn1ToArray[2], $world);
            $spawn2ToArray = explode(":", $data->get("spawn2"));
            $spawn2ToPosition = new Position((int)$spawn2ToArray[0], (int)$spawn2ToArray[1], (int)$spawn2ToArray[2], $world);
            $arena = new Arena($data->get("name"));
            $arena->setWorld($world);
            $arena->setSpawn1($spawn1ToPosition);
            $arena->setSpawn2($spawn2ToPosition);
            $arena->setMode(ModeModule::getInstance()->get($data->get("mode")));
            $arena->setEnabled((bool)$data->get("enabled"));
            $this->add($arena);
        }
        Practice::getInstance()->getLogger()->info("Arenas loaded: " . count($this->getArenas()));
        Practice::getInstance()->getScheduler()->scheduleRepeatingTask(new StartedGameScheduler(), 20);
    }

    /**
     * @throws JsonException
     */
    public function create(Arena $arena): void {
        $data = new DataCreator("arenas/" . $arena->getName() . ".json");
        $data->setDataAll([
            "name" => $arena->getName(),
            "mode" => $arena->getMode()->getName(),
            "world" => $arena->getWorld()->getFolderName(),
            "spawn1" => "0:0:0",
            "spawn2" => "0:0:0",
            "enabled" => false
        ]);
        $data->save();
        $this->add($arena);
    }

    public function hasMaps(): bool {
        return count($this->getArenas()) >= 1;
    }

    public function getEnabledArenasCount(): int {
        $arenas = 0;
        if ($this->hasMaps()) {
            foreach ($this->getArenas() as $arena) {
                if ($arena->isEnabled()) {
                    $arenas++;
                }
            }
        }
        return $arenas;
    }

    public function getPlayersInFightCount(): int {
        $players = 0;
        if ($this->hasMaps()) {
            foreach ($this->getArenas() as $arena) {
                if ($arena->isEnabled()) {
                    if ($arena->isActive()) {
                        foreach ($arena->getPlayers() as $player) {
                            $players++;
                        }
                    }
                }
            }
        }
        return $players;
    }

    public function findArena(int $matchType, Mode $mode, array $players): void {
        $arena = $this->getRandomArena($mode);
        $arena->setPlayers($players);
        $game = new Game($arena);
        $game->setType($matchType);
        GameRegistry::getInstance()->addGame($game);
        $arena->setActive(true);
        $arena->setStatus(Arena::STARTING);
        $arena->teleport();
    }

    public function getRandomArena(Mode $mode): ?Arena {
        $arenas = [];
        if ($this->hasEnabledArenas()) {
            foreach ($this->getArenas() as $arena) {
                if ($arena->isEnabled()) {
                    if ($arena->getMode()->getName() === $mode->getName()) {
                        if ($arena->getStatus() === Arena::WAITING) {
                            $arenas[$arena->getName()] = $arena;
                        }
                    }
                }
            }
        }
        return $arenas[array_rand($arenas)] ?? null;
    }

    public function hasEnabledArenas(): bool {
        return $this->getEnabledArenasCount() >= 1;
    }

    public function add(Arena $arena): void {
        $this->arenas[$arena->getName()] = $arena;
    }

    public function get(string $name): ?Arena {
        return $this->arenas[$name] ?? null;
    }

    public function exists(string $name): bool {
        return isset($this->arenas[$name]);
    }

    public function existFile(string $name): bool {
        return file_exists(Practice::getInstance()->getDataFolder() . "arenas/" . $name . ".json");
    }

    public function remove(string $name): void {
        unset($this->arenas[$name]);
    }

    public function delete(string $name): void {
        if (!is_null($this->get($name))) $this->get($name)->close(true);
        if ($this->exists($name)) $this->remove($name);
        if ($this->existFile($name)) unlink(Practice::getInstance()->getDataFolder() . "sessions/" . $name . ".json");
        Practice::getInstance()->getLogger()->info("The arena by name " . $name . " has been deleted");
    }

    /**
     * @return Arena[]
     */
    public function getArenas(): array {
        return $this->arenas;
    }
}