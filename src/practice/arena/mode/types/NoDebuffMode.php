<?php

namespace practice\arena\mode\types;

use practice\arena\mode\Mode;

class NoDebuffMode extends Mode {

    public function getName(): string {
        return "NoDebuff";
    }
}