<?php

namespace Olympia\Kitpvp\managers\types;

use CortexPE\DiscordWebhookAPI\Embed;
use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use Exception;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\Manager;

final class WebhookManager extends Manager
{
    public const CHANNEL_REPORT = 0;
    public const CHANNEL_LOGS_COMMANDS = 1;
    public const CHANNEL_LOGS_SANCTIONS = 2;

    private Webhook $webhookReport;
    private Webhook $webhookLogsCommands;
    private Webhook $webhookLogsSanctions;

    public function onLoad(): void
    {
        $webhooks = Managers::CONFIG()->get("webhooks");
        $this->webhookReport = new Webhook($webhooks["report"]);
        $this->webhookLogsCommands = new Webhook($webhooks["logs-commands"]);
        $this->webhookLogsSanctions = new Webhook($webhooks["logs-sanctions"]);
    }

    /**
     * @throws Exception
     */
    public function sendMessage(string $title, string $message, int $channel): void
    {
        $msg = new Message();
        $embed = new Embed();
        $embed->setTitle($title);
        $embed->setDescription($message);
        $embed->setColor( 0xFFA500);
        $msg->addEmbed($embed);

        match ($channel) {
            $this::CHANNEL_REPORT => $this->webhookReport->send($msg),
            $this::CHANNEL_LOGS_COMMANDS => $this->webhookLogsCommands->send($msg),
            $this::CHANNEL_LOGS_SANCTIONS => $this->webhookLogsSanctions->send($msg),
        };
    }
}