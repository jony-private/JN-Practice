<?php

namespace practice\commands;

use JsonException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use practice\arena\Arena;
use practice\arena\ArenaRegistry;
use practice\arena\game\Game;
use practice\arena\mode\ModeModule;
use practice\utils\PermissionRegistry;

class ArenaCommand extends Command {

    public function __construct() {
        parent::__construct("arena", "Arena command.");
        $this->setPermission(PermissionRegistry::ARENA_COMMAND);
        $this->setPermissionMessage(TextFormat::colorize("&cYou no have permissions to execute this command."));
    }

    /**
     * @throws JsonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if ($sender instanceof Player) {
            if (empty($args[0])) {
                $sender->sendMessage(TextFormat::colorize("&cUse: /arena help for more commands."));
                return;
            }
            switch ($args[0]) {
                case "help":
                    $sender->sendMessage(TextFormat::colorize("&e/arena help: Help Commands"));
                    $sender->sendMessage(TextFormat::colorize("&e/arena create: Create new arena"));
                    $sender->sendMessage(TextFormat::colorize("&e/arena delete: Delete arena"));
                    $sender->sendMessage(TextFormat::colorize("&e/arena set-spawn: Set arena spawn"));
                    $sender->sendMessage(TextFormat::colorize("&e/arena enable: Enable arena."));
                    break;
                case "create":
                    if (empty($args[1])) {
                        $sender->sendMessage(TextFormat::colorize("&cUse: /arena create (name) (mode)."));
                        return;
                    }
                    if (ArenaRegistry::getInstance()->exists($args[1]) or ArenaRegistry::getInstance()->existFile($args[1])) {
                        $sender->sendMessage(TextFormat::colorize("&cThe arena with the id " . $args[1] . " already exists."));
                        return;
                    }
                    if (empty($args[2])) {
                        $sender->sendMessage(TextFormat::colorize("&cUse: /arena create (name) (mode)."));
                        return;
                    }
                    if (!ModeModule::getInstance()->exist($args[2])) {
                        $sender->sendMessage(TextFormat::colorize("&cThe mode with the id " . $args[2] . " not exists."));
                        return;
                    }
                    $arena = new Arena($args[1]);
                    $arena->setWorld($sender->getWorld());
                    $arena->setEnabled(false);
                    $arena->setActive(false);
                    $arena->setSpawn1(new Position(0, 0, 0, $sender->getWorld()));
                    $arena->setSpawn2(new Position(0, 0, 0, $sender->getWorld()));
                    $arena->setMode(ModeModule::getInstance()->get($args[2]));
                    ArenaRegistry::getInstance()->create($arena);
                    $sender->sendMessage(TextFormat::colorize("&aYou created a new arena with the id " . $arena->getName() . " and the mode " . $arena->getMode()->getName()));
                    break;
                case "delete":
                    if (empty($args[1])) {
                        $sender->sendMessage(TextFormat::colorize("&cUse: /arena delete (name)."));
                        return;
                    }
                    if (!ArenaRegistry::getInstance()->exists($args[1]) or !ArenaRegistry::getInstance()->existFile($args[1])) {
                        $sender->sendMessage(TextFormat::colorize("&cThe arena with the id " . $args[1] . " does not exists."));
                        return;
                    }
                    ArenaRegistry::getInstance()->delete($args[1]);
                    $sender->sendMessage(TextFormat::colorize("&aYou removed a arena " . $args[1] . " successfully."));
                    break;
                case "set-spawn":
                    if (empty($args[1])) {
                        $sender->sendMessage(TextFormat::colorize("&cUse: /arena set-spawm (name) (1/2)."));
                        return;
                    }
                    if (!ArenaRegistry::getInstance()->exists($args[1]) or !ArenaRegistry::getInstance()->existFile($args[1])) {
                        $sender->sendMessage(TextFormat::colorize("&cThe arena with the id " . $args[1] . " does not exists."));
                        return;
                    }
                    $arena = ArenaRegistry::getInstance()->get($args[1]);
                    if (empty($args[2])) {
                        $sender->sendMessage(TextFormat::colorize("&cUse: /arena set-spawm (name) (1/2)."));
                        return;
                    }
                    if (!is_numeric($args[2])) {
                        $sender->sendMessage(TextFormat::colorize("&cYou do not receive a number between 1 and 2 to place the spawn.."));
                        return;
                    }
                    if ($args[2] > 2 or $args[2] < 1) {
                        $sender->sendMessage(TextFormat::colorize("&cYou do not receive a number between 1 and 2 to place the spawn.."));
                        return;
                    }
                    $this->setSpawnByNumber($sender, $arena, (int) $args[2]);
                    $sender->sendMessage(TextFormat::colorize("&aYou placer the spawn " . $args[2] . " to arena " . $args[1] . " successfully."));
                    break;
                case "enable":
                    if (empty($args[1])) {
                        $sender->sendMessage(TextFormat::colorize("&cUse: /arena enable (name)."));
                        return;
                    }
                    if (!ArenaRegistry::getInstance()->exists($args[1]) or !ArenaRegistry::getInstance()->existFile($args[1])) {
                        $sender->sendMessage(TextFormat::colorize("&cThe arena with the id " . $args[1] . " does not exists."));
                        return;
                    }
                    $arena = ArenaRegistry::getInstance()->get($args[1]);
                    if ($arena->isEnabled()) {
                        $sender->sendMessage(TextFormat::colorize("&cThe arena already enabled."));
                        return;
                    }
                    if (!$arena->canTeleport()) {
                        $sender->sendMessage(TextFormat::colorize("&cCan't enable this arena, first set the spawns."));
                        return;
                    }
                    $arena->setEnabled(true);
                    $sender->sendMessage(TextFormat::colorize("&aYou enabled a arena " . $args[1] . " successfully."));
                    break;
                case "arenas":
                    foreach (ArenaRegistry::getInstance()->getArenas() as $arenas) {
                        $sender->sendMessage(TextFormat::colorize("&eArena ID: &7" . $arenas->getName() . "&e, Mode: &7" . $arenas->getMode()->getName() . "&e, Enabled: &7" . ($arenas->isEnabled() ? "Yes" : "No")));
                    }
                    break;
                default:
                    $sender->sendMessage(TextFormat::colorize("&cUse: /arena help for more commands."));
                    return;
            }
        } else {
            $sender->sendMessage(TextFormat::colorize("&cYou can't execute this command on console."));
        }
    }

    private function setSpawnByNumber(Player $player, Arena $arena, int $spawnNumber): void {
        switch ($spawnNumber) {
            case 1:
                $arena->setSpawn1($player->getPosition());
                break;
            case 2:
                $arena->setSpawn2($player->getPosition());
                break;
        }
    }
}