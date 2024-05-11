<?php

namespace practice\libs\discordapi;

use practice\libs\discordapi\async\SendDataToWebhookAsync;
use practice\libs\discordapi\modules\Message;
use pocketmine\Server;

class DiscordAPI {

    private string $url;

    public function __construct(string $url) {
        $this->url = $url;
    }

    public function getURL(): string {
        return $this->url;
    }

    public function isValid(): bool {
        return filter_var($this->url, FILTER_VALIDATE_URL) !== false;
    }

    public function send(Message $message): void {
        Server::getInstance()->getAsyncPool()->submitTask(new SendDataToWebhookAsync($this, $message));
    }
}