<?php

namespace practice\arena\game;

use pocketmine\utils\SingletonTrait;

class GameRegistry {
    use SingletonTrait {
        setInstance as private;
        reset as private;
    }

    private array $games = [];

    public function addGame(Game $game): void {
        $this->games[$game->getArena()->getName()] = $game;
    }

    public function get(string $name): ?Game {
        return $this->games[$name] ?? null;
    }

    public function removeGame(string $name): void {
        unset($this->games[$name]);
    }

    public function existsGame(string $name): bool {
        return isset($this->games[$name]);
    }

    public function convertGameTypeToString(int $gameType): string {
        return match ($gameType) {
          Game::RANKED => "Ranked",
          Game::UNRANKED => "Unranked",
          Game::PARTY => "Party",
          Game::FRIENDLY => "Friendly",
          default => "Unknown"
        };
    }

    /**
     * @return Game[]
     */
    public function getGames(): array {
        return $this->games;
    }
}