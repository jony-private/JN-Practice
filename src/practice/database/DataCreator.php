<?php

namespace practice\database;

use JsonException;
use practice\Practice;
use pocketmine\utils\Config;

class DataCreator {

    private string $path;

    private Config $config;

    private bool $changed = false;

    public function __construct(string $path) {
        $this->path = $path;
        $this->config = new Config(Practice::getInstance()->getDataFolder() . $path, $this->getExtensionFromString(explode(".", $path)[1]));
    }

    public function setData(string $key, mixed $data): void {
        $this->setChanged();
        $this->getConfig()->set($key, $data);
    }

    public function setDataAll(array $data): void {
        $this->setChanged();
        $this->getConfig()->setAll($data);
    }

    public function getDataNested(string $key): mixed {
        return $this->getConfig()->getNested($key);
    }

    public function getData(string $key): mixed {
        return $this->getConfig()->get($key);
    }

    /**
     * @throws JsonException
     */
    public function save(): void {
        if ($this->isChanged()) {
            $this->getConfig()->save();
            $this->setChanged(false);
        }
    }

    public function getExtensionFromString(string $extension): int {
        return match ($extension) {
            "yml" => Config::YAML,
            "json", "js" => Config::JSON,
            default => Config::DETECT
        };
    }

    public function getConfig(): Config {
        return $this->config;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function isChanged(): bool {
        return $this->changed;
    }

    public function setChanged(bool $changed = true): void {
        $this->changed = $changed;
    }
}