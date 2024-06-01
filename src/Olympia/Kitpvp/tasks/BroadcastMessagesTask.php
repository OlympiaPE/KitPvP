<?php

namespace Olympia\Kitpvp\tasks;

use Olympia\Kitpvp\managers\types\ConfigManager;
use pocketmine\scheduler\Task;
use pocketmine\Server;

final class BroadcastMessagesTask extends Task
{
    private array $messages;
    private int $nextMessage = 0;

    public function __construct()
    {
        $this->messages = ConfigManager::getInstance()->get("broadcast-messages");
    }

    public function onRun(): void
    {
        Server::getInstance()->broadcastMessage($this->messages[$this->nextMessage]);

        if ($this->nextMessage === count($this->messages) - 1) {
            $this->nextMessage = 0;
        }else{
            $this->nextMessage++;
        }
    }
}