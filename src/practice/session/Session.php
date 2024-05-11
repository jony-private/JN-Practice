<?php

namespace practice\session;

use JsonException;
use practice\database\DataCreator;

class Session {

    private string $name;

    private int $wins;

    private int $loses;

    private int $helio;

    private int $kdr;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setWins(int $wins): void {
        $this->wins = $wins;
    }

    public function getWins(): int {
        return $this->wins;
    }

    public function setLoses(int $loses): void {
        $this->loses = $loses;
    }

    public function getLoses(): int {
        return $this->loses;
    }

    public function setHelio(int $helio): void {
        $this->helio = $helio;
    }

    public function getHelio(): int {
        return $this->helio;
    }

    public function setKDR(int $kdr): void {
        $this->kdr = $kdr;
    }

    public function getKDR(): int {
        return $this->kdr;
    }

    /**
     * @throws JsonException
     */
    public function save(): void {
        $data = new DataCreator("sessions/" . $this->getName() . ".json");
        if ($this->getWins() !== $data->getData("wins")) {
            $data->setData("wins", $this->getWins());
        }
        if ($this->getLoses() !== $data->getData("loses")) {
            $data->setData("loses", $this->getLoses());
        }
        if ($this->getHelio() !== $data->getData("helio")) {
            $data->setData("helio", $this->getHelio());
        }
        if ($this->getKDR() !== $data->getData("kdr")) {
            $data->setData("kdr", $this->getKDR());
        }
        $data->save();
    }
}