<?php

namespace practice\session;

use JsonException;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use practice\database\DataCreator;
use practice\Practice;

class SessionRegistry {
    use SingletonTrait {
        setInstance as private;
        reset as private;
    }

    private array $sessions = [];

    public function emmit(): void {
        if (!is_dir(Practice::getInstance()->getDataFolder() . "sessions")) @mkdir(Practice::getInstance()->getDataFolder() . "sessions");
    }

    /**
     * @throws JsonException
     */
    public function create(string $name): void {
        $session = new Session($name);
        $session->setWins(0);
        $session->setHelio(0);
        $session->setLoses(0);
        $session->setKDR(0);
        $data = new DataCreator("sessions/" . $name . ".json");
        $data->setDataAll([
            "name" => $session->getName(),
            "wins" => $session->getWins(),
            "loses" => $session->getLoses(),
            "kdr" => $session->getKDR(),
            "helio" => $session->getHelio()
        ]);
        $data->save();
        $this->add($session);
    }

    public function load(string $name): void {
        $data = new DataCreator("sessions/" . $name . ".json");
        $session = new Session($data->getData("name"));
        $session->setWins($data->getData("wins"));
        $session->setHelio($data->getData("helio"));
        $session->setLoses($data->getData("loses"));
        $session->setKDR($data->getData("kdr"));
        $this->add($session);
    }

    public function add(Session $session): void {
        $this->sessions[$session->getName()] = $session;
    }

    public function get(string $name): ?Session {
        return $this->sessions[$name] ?? null;
    }

    public function exists(string $name): bool {
        return isset($this->sessions[$name]);
    }

    public function remove(string $name): void {
        unset($this->sessions[$name]);
    }

    public function delete(string $name): void {
        if (!is_null(($player = Server::getInstance()->getPlayerExact($name)))) $player->kick("Your session has been deleted.");
        if ($this->exists($name)) $this->remove($name);
        if ($this->existFile($name)) unlink(Practice::getInstance()->getDataFolder() . "sessions/" . $name . ".json");
        Practice::getInstance()->getLogger()->info("The session with name " . $name . " has been deleted.");
    }

    public function existFile(string $name): bool {
        return file_exists(Practice::getInstance()->getDataFolder() . "sessions/" . $name . ".json");
    }

    /**
     * @return Session[]
     */
    public function getSessions(): array {
        return $this->sessions;
    }
}