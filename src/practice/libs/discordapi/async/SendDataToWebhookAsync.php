<?php

namespace practice\libs\discordapi\async;

use practice\libs\discordapi\DiscordAPI;
use practice\libs\discordapi\modules\Message;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\thread\NonThreadSafeValue;

class SendDataToWebhookAsync extends AsyncTask {

    private NonThreadSafeValue $discord;

    private NonThreadSafeValue $message;

    public function __construct(DiscordAPI $discord, Message $message) {
        $this->discord = new NonThreadSafeValue($discord);
        $this->message = new NonThreadSafeValue($message);
    }

    public function onRun(): void {
        $ch = curl_init($this->getDiscord()->getURL());
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->getMessage()));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        $this->setResult(curl_exec($ch));
        curl_close($ch);
    }

    public function getMessage(): Message {
        return $this->message->deserialize();
    }

    public function getDiscord(): DiscordAPI {
        return $this->discord->deserialize();
    }

    public function onCompletion(): void {
        $response = $this->getResult();
        if ($response !== "") {
            Server::getInstance()->getLogger()->error("Ocurrio un error al contactar con el webhook: " . $response);
        }
    }
}