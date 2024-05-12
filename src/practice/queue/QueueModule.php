<?php

namespace practice\queue;

use pocketmine\utils\SingletonTrait;
use practice\arena\game\Game;
use practice\arena\mode\Mode;
use practice\arena\mode\ModeModule;

class QueueModule {
    use SingletonTrait {
        setInstance as private;
        reset as private;
    }

    private array $queue = [];

    public function createQueueCache(): void {
        foreach (ModeModule::getInstance()->getModes() as $modes) {
            $this->queue[$modes->getName()][Game::RANKED] = [];
            $this->queue[$modes->getName()][Game::UNRANKED] = [];
        }
    }

    public function addToQueue(string $player, Mode $mode, int $type): void {
        $this->queue[$mode->getName()][$type][] = $player;
    }

    public function updateQueues(): void {
        if (count($this->getQueue()) >= 2) {
            if (ArenaManager::getInstance()->hasEnabledArenas()) {
                ArenaManager::getInstance()->getRandomArena(array_shift($this->queue), array_shift($this->queue));
            }
        }
    }

    public function isInQueue(string $player): bool {
        $is = false;
        foreach (ModeModule::getInstance()->getModes() as $mode) {
            if (isset($this->queue[$mode->getName()][Game::RANKED][array_search($player, $this->queue[$mode->getName()][Game::RANKED][$player])])) {
                $is = true;
            } else if (isset($this->queue[$mode->getName()][Game::UNRANKED][array_search($player, $this->queue[$mode->getName()][Game::UNRANKED][$player])])) {
                $is = true;
            }
        }
        return $is;
    }

    public function removeFromQueue(string $player): void {
        unset($this->queue[$player]);
    }

    /**
     * @return array[]
     */
    public function getQueue(): array {
        return $this->queue;
    }
}