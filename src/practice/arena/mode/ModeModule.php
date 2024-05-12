<?php

namespace practice\arena\mode;

use pocketmine\utils\SingletonTrait;
use practice\arena\mode\types\ArcherMode;
use practice\arena\mode\types\GappleMode;
use practice\arena\mode\types\NoDebuffMode;

class ModeModule {
    use SingletonTrait {
        setInstance as private;
        reset as private;
    }

    private array $modes = [];

    public function emmit(): void {
        foreach ([new NoDebuffMode(), new GappleMode(), new ArcherMode()] as $modes) {
            $this->add($modes);
        }
    }

    public function add(Mode $mode): void {
        $this->modes[$mode->getName()] = $mode;
    }

    public function get(string $name): ?Mode {
        return $this->modes[$name] ?? null;
    }

    public function exist(string $name): bool {
        return isset($this->modes[$name]);
    }

    /**
     * @return Mode[]
     */
    public function getModes(): array {
        return $this->modes;
    }
}