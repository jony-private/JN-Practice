<?php

namespace practice;

use JsonException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use practice\emmiter\EmmiterLogger;

class Practice extends PluginBase {
    use SingletonTrait {
        setInstance as private;
        reset as private;
    }

    public function onLoad(): void {
        self::setInstance($this);
    }

    public function onEnable(): void {
        EmmiterLogger::getInstance()->emmit();
    }

    /**
     * @throws JsonException
     */
    public function onDisable(): void {
        EmmiterLogger::getInstance()->demmit();
    }
}