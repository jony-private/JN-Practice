<?php

namespace practice\arena\game;

use practice\arena\Arena;

class Game {

    public const RANKED = 1;
    public const UNRANKED = 2;
    public const PARTY = 3;
    public const FRIENDLY = 4;

    private Arena $arena;

    private int $type;

    public function __construct(Arena $arena) {
        $this->arena = $arena;
    }

    public function getArena(): Arena {
        return $this->arena;
    }

    public function getType(): int {
        return $this->type;
    }

    public function setType(int $type): void {
        $this->type = $type;
    }
}