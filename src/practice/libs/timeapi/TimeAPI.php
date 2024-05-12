<?php

namespace practice\libs\timeapi;

use pocketmine\utils\SingletonTrait;

class TimeAPI {
    use SingletonTrait {
        setInstance as private;
        reset as private;
    }

    public function getTimeToFullString(int $time): string {
        return gmdate("i:s", $time);
    }

    public function getTimeFormat(int $time): string {
        $remaining = $time - time();
        $seconds = $remaining % 60;
        $minutes = null;
        $hours = null;
        $days = null;
        if ($remaining >= 60) {
            $minutes = floor(($remaining % 3600) / 60);
            if ($remaining >= 3600) {
                $hours = floor(($remaining % 86400) / 3600);
                if ($remaining >= 3600 * 24) {
                    $days = floor($remaining / 86400);
                }
            }
        }
        return ($minutes !== null ? ($hours !== null ? ($days !== null ? "$days Days " : "")."$hours Hours " : "")."$minutes Minutes " : "")."$seconds Seconds";
    }
}