<?php

namespace practice\arena;

use JsonException;
use pocketmine\player\GameMode;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use practice\arena\game\GameRegistry;
use practice\arena\mode\Mode;
use practice\database\DataCreator;
use practice\libs\scoreboard\Scoreboard;
use practice\libs\timeapi\TimeAPI;
use practice\session\Session;
use practice\session\SessionRegistry;
use practice\utils\PlayerUtils;

class Arena {

    private string $name;

    private World $world;

    private Position $spawn1;

    private Position $spawn2;

    private Mode $mode;

    private array $players = [];

    private array $spectators = [];

    private bool $active = false;

    private bool $enabled = false;

    private int $status = self::WAITING;

    private int $startingTime = 10;

    private int $gameTime = 10;

    private int $finishingTime = 10;

    public const WAITING = 0;
    public const STARTING = 1;
    public const IN_GAME = 2;
    public const FINISHING = 3;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getWorld(): World {
        return $this->world;
    }

    public function setWorld(World $world): void {
        $this->world = $world;
    }

    public function getSpawn1(): Position {
        return $this->spawn1;
    }

    public function setSpawn1(Position $spawn1): void {
        $this->spawn1 = $spawn1;
    }

    public function getSpawn2(): Position {
        return $this->spawn2;
    }

    public function setSpawn2(Position $spawn2): void {
        $this->spawn2 = $spawn2;
    }

    public function canTeleport(): bool {
        return !is_null($this->getSpawn1()) and !is_null($this->getSpawn2()) and $this->getSpawn1()->getY() !== 0 and $this->getSpawn2()->getY() !== 0;
    }

    public function teleport(): void {
        if ($this->canTeleport()) {
            $player1 = $this->getSessionClass($this->getPlayers()[0]);
            $player1->getPlayer()->teleport($this->getSpawn1());
            // KitRegistry::getInstance()->getKitByMode($this->getMode())->send($player1);
            $player2 = $this->getSessionClass($this->getPlayers()[1]);
            $player2->getPlayer()->teleport($this->getSpawn2());
            // KitRegistry::getInstance()->getKitByMode($this->getMode())->send($player2);
            PlayerUtils::getInstance()->sendDuelFoundMessage($player1, $player2); PlayerUtils::getInstance()->sendDuelFoundMessage($player2, $player1);
        }
    }

    public function setMode(Mode $mode): void {
        $this->mode = $mode;
    }

    public function getMode(): Mode {
        return $this->mode;
    }

    public function getPlayers(): array {
        return $this->players;
    }

    public function setPlayers(array $players): void {
        $this->players = $players;
    }

    public function addPlayer(string $name): void {
        $this->players[] = $name;
        var_dump($this->players);
    }

    public function removePlayer(string $name): void {
        unset($this->players[array_search($name, $this->players)]);
    }

    public function getSessionClass(string $name): ?Session {
        return SessionRegistry::getInstance()->get($this->players[array_search($name, $this->players)]);
    }

    public function existPlayer(string $name): bool {
        return isset($this->players[array_search($name, $this->players)]);
    }

    public function getSpectators(): array {
        return $this->spectators;
    }

    public function setSpectators(array $spectators): void {
        $this->spectators = $spectators;
    }

    public function addSpectator(string $name): void {
        $this->spectators[] = $name;
    }

    public function removeSpectator(string $name): void {
        unset($this->spectators[array_search($name, $this->spectators)]);
    }

    public function existSpectator(string $name): bool {
        return isset($this->spectators[array_search($name, $this->spectators)]);
    }

    public function setActive(bool $active): void {
        $this->active = $active;
    }

    public function isActive(): bool {
        return $this->active;
    }

    public function setEnabled(bool $enabled): void {
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool {
        return $this->enabled;
    }

    public function setStatus(int $status): void {
        $this->status = $status;
    }

    public function getStatus(): int {
        return $this->status;
    }

    public function setStartingTime(int $startingTime): void {
        $this->startingTime = $startingTime;
    }

    public function getStartingTime(): int {
        return $this->startingTime;
    }

    public function setGameTime(int $gameTime): void {
        $this->gameTime = $gameTime;
    }

    public function getGameTime(): int {
        return $this->gameTime;
    }

    public function setFinishingTime(int $finishingTime): void {
        $this->finishingTime = $finishingTime;
    }

    public function getFinishingTime(): int {
        return $this->finishingTime;
    }

    public function close(bool $disable = false): void {
        foreach ($this->getPlayers() as $player) {
            $player = $this->getSessionClass($player)->getPlayer();
            Server::getInstance()->dispatchCommand($player, "hub");
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->getEffects()->clear();
            $this->removePlayer($player);
        }
        foreach ($this->getSpectators() as $spectator) {
            $player = SessionRegistry::getInstance()->get($spectator)->getPlayer();
            Server::getInstance()->dispatchCommand($player, "hub");
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->getEffects()->clear();
            $this->removeSpectator($spectator);
        }
        $this->setActive(false);
        $this->setStatus(Arena::WAITING);
        $this->setStartingTime(10);
        $this->setGameTime(10);
        $this->setFinishingTime(10);
        if ($disable) {
            $this->setEnabled(false);
        }
    }

    public function getRival(string $name): ?string {
        $rival = null;
        foreach ($this->getPlayers() as $players) {
            if ($players !== $name) {
                $rival = $players;
            }
        }
        return $rival;
    }

    public function endGame(string $winner): void {
        $loserName = $this->getRival($winner);
        $loserSession = $this->getSessionClass($loserName);
        $loserSession->setLoses($loserSession->getLoses() + 1);
        $loserSession->getPlayer()->setGamemode(GameMode::SPECTATOR);
        $winnerSession = $this->getSessionClass($winner);
        $winnerSession->setWins($winnerSession->getWins() + 1);
        $scoreboard = Scoreboard::getInstance();
        /** @var Session $players */
        foreach ([$loserSession, $winnerSession] as $players) {
            $scoreboard->delete($players->getPlayer());
            $players->getPlayer()->getInventory()->clearAll();
            $players->getPlayer()->getArmorInventory()->clearAll();
            $players->getPlayer()->getEffects()->clear();
            $players->getPlayer()->setHealth(20.0);
            if ($players->getPlayer()->isOnFire()) {
                $players->getPlayer()->extinguish();
            }
        }
        GameRegistry::getInstance()->removeGame($this->getName());
        Server::getInstance()->broadcastMessage(TextFormat::colorize("&a" . $winner . " &7has won the fight versus &r" . $loserName));
    }

    public function winFight(string $name): void {
        foreach ($this->getPlayers() as $player) {
            if ($player !== $name) {
                $this->endGame($player);
            }
        }
    }

    public function stopByReason(string $reason): void {
        foreach ($this->getPlayers() as $player) {
            $session = $this->getSessionClass($player);
            $session->getPlayer()->sendMessage(TextFormat::colorize($reason));
        }
        GameRegistry::getInstance()->removeGame($this->getName());
        $this->close();
    }

    public function schedule(): void {
        $scoreboard = Scoreboard::getInstance();
        if (count($this->getPlayers()) <= 1) {
            $this->stopByReason("&cIt seems that your opponent abandoned the game.");
        }
        $game = GameRegistry::getInstance()->get($this->getName());
        switch ($this->getStatus()) {
            case self::STARTING:
                if ($this->getStartingTime() === 0) {
                    $this->setStatus(self::IN_GAME);
                } else {
                    $this->setStartingTime($this->getStartingTime() - 1);
                }
                foreach ($this->getPlayers() as $player) {
                    $session = $this->getSessionClass($player);
                    $player = $session->getPlayer();
                    $player->setNoClientPredictions();
                    $player->sendTip(TextFormat::colorize("&eThe fight start in: &f" . TimeAPI::getInstance()->getTimeToFullString($this->getStartingTime())));
                    $scoreboard->create($player, TextFormat::colorize("&l&eJN-Practice"));
                    $scoreboard->addLine($player, 1, "");
                    $scoreboard->addLine($player, 2, TextFormat::colorize(" &eMatch Type: &f" . GameRegistry::getInstance()->convertGameTypeToString($game->getType())));
                    $scoreboard->addLine($player, 3, TextFormat::colorize(" &eStarting in: &f" . TimeAPI::getInstance()->getTimeToFullString($this->getStartingTime())));
                    $scoreboard->addLine($player, 4, TextFormat::colorize("&r"));
                    $scoreboard->addLine($player, 5, TextFormat::colorize(" &eOpponent: &f" . $this->getRival($player)));
                    $scoreboard->addLine($player, 6, TextFormat::colorize(" &eYour Ping: &f" . $player->getNetworkSession()->getPing()));
                    $scoreboard->addLine($player, 7, TextFormat::colorize("&r&r"));
                    $scoreboard->addLine($player, 8, TextFormat::colorize(" &7myserver.com"));
                    $scoreboard->addLine($player, 9, TextFormat::colorize("&e"));
                    $scoreboard->send($player);
                }
                break;
            case self::IN_GAME:
                if ($this->getGameTime() === 0) {
                    $this->stopByReason("&cThe game time has expired and there was no winner.");
                } else {
                    $this->setGameTime($this->getGameTime() - 1);
                }
                foreach ($this->getPlayers() as $player) {
                    $session = $this->getSessionClass($player);
                    $player = $session->getPlayer();
                    $player->setNoClientPredictions(false);
                    $scoreboard->create($player, TextFormat::colorize("&l&eJN-Practice"));
                    $scoreboard->addLine($player, 1, "");
                    $scoreboard->addLine($player, 2, TextFormat::colorize(" &eMatch Type: &f" . GameRegistry::getInstance()->convertGameTypeToString($game->getType())));
                    $scoreboard->addLine($player, 3, TextFormat::colorize(" &eRestant Time: &f" . TimeAPI::getInstance()->getTimeToFullString($this->getGameTime())));
                    $scoreboard->addLine($player, 4, TextFormat::colorize("&r"));
                    $scoreboard->addLine($player, 5, TextFormat::colorize(" &eOpponent: &f" . $this->getRival($player)));
                    $scoreboard->addLine($player, 6, TextFormat::colorize(" &eYour Ping: &f" . $player->getNetworkSession()->getPing()));
                    $scoreboard->addLine($player, 7, TextFormat::colorize("&r&r"));
                    $scoreboard->addLine($player, 8, TextFormat::colorize(" &7myserver.com"));
                    $scoreboard->addLine($player, 9, TextFormat::colorize("&e"));
                    $scoreboard->send($player);
                }
                break;
            case self::FINISHING:
                if ($this->getFinishingTime() === 0) {
                    $this->close();
                } else {
                    $this->setFinishingTime($this->getFinishingTime() - 1);
                }
                foreach ($this->getPlayers() as $player) {
                    $session = $this->getSessionClass($player);
                    $player = $session->getPlayer();
                    $scoreboard->create($player, TextFormat::colorize("&l&eJN-Practice"));
                    $scoreboard->addLine($player, 1, "");
                    $scoreboard->addLine($player, 2, TextFormat::colorize(" &eMatch Type: &f" . GameRegistry::getInstance()->convertGameTypeToString($game->getType())));
                    $scoreboard->addLine($player, 3, TextFormat::colorize(" &eFinishing in: &f" . TimeAPI::getInstance()->getTimeToFullString($this->getFinishingTime())));
                    $scoreboard->addLine($player, 4, TextFormat::colorize("&r"));
                    $scoreboard->addLine($player, 5, TextFormat::colorize(" &eOpponent: &f" . $this->getRival($player)));
                    $scoreboard->addLine($player, 6, TextFormat::colorize(" &eYour Ping: &f" . $player->getNetworkSession()->getPing()));
                    $scoreboard->addLine($player, 7, TextFormat::colorize("&r&r"));
                    $scoreboard->addLine($player, 8, TextFormat::colorize(" &7myserver.com"));
                    $scoreboard->addLine($player, 9, TextFormat::colorize("&e"));
                    $scoreboard->send($player);
                }
                break;
        }
    }

    /**
     * @throws JsonException
     */
    public function save(): void {
        $data = new DataCreator("arenas/" . $this->getName() . ".json");
        $spawn1 = $this->getSpawn1()->getX() . ":" . $this->getSpawn1()->getY() . ":" . $this->getSpawn1()->getZ();
        if ($spawn1 !== $data->getData("spawn1")) {
            $data->setData("spawn1", $spawn1);
        }
        $spawn2 = $this->getSpawn2()->getX() . ":" . $this->getSpawn2()->getY() . ":" . $this->getSpawn2()->getZ();
        if ($spawn2 !== $data->getData("spawn2")) {
            $data->setData("spawn2", $spawn2);
        }
        if ($this->isEnabled() !== $data->getData("enabled")) {
            $data->setData("enabled", $this->isEnabled());
        }
        $data->save();
    }
}