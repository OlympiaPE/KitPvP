<?php

namespace Olympia\Kitpvp\commands\admin;

use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\managers\types\ConfigManager;
use Olympia\Kitpvp\managers\types\NPCManager;
use Olympia\Kitpvp\player\OlympiaPlayer;
use Olympia\Kitpvp\utils\Permissions;
use pocketmine\command\CommandSender;

class NPCCommand extends OlympiaCommand
{
    public function __construct()
    {
        $this->permission = Permissions::COMMAND_NPC;
        parent::__construct("npc", "NPC command", "/npc help");
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof OlympiaPlayer) {

            switch ($args[0] ?? null) {

                case "create":

                    if (isset($args[1])) {

                        $name = $args[1];
                        $id = NPCManager::getInstance()->createNpc($sender, $name);
                        $sender->sendMessage(str_replace(
                            ["{name}", "{id}"],
                            [$name, $id],
                            ConfigManager::getInstance()->getNested("messages.npc-create")
                        ));
                    }else{
                        $this->sendUsageMessage($sender);
                    }
                    break;

                case "delete":

                    if (isset($args[1])) {

                        $id = $args[1];

                        if (is_int($id) && NPCManager::getInstance()->getNpcExists($id)) {

                            NPCManager::getInstance()->deleteNpc($id);
                            $sender->sendMessage(str_replace(
                                "{id}",
                                $id,
                                ConfigManager::getInstance()->getNested("messages.npc-delete")
                            ));
                        }else{
                            $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.npc-not-exist"));
                        }
                    }else{
                        $this->sendUsageMessage($sender);
                    }
                    break;

                case "id":

                    NPCManager::getInstance()->addPlayerWantId($sender->getName());
                    $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.npc-id"));
                break;

                case "addcmd":

                    if (isset($args[1]) && isset($args[2])) {

                        $id = $args[1];
                        if (is_int($id) && NPCManager::getInstance()->getNpcExists($id)) {

                            $command = "";
                            for ($i = 2; $i < count($args); $i++) {
                                $command .= $args[$i] . " ";
                            }
                            NPCManager::getInstance()->addNpcCommand($id, $command);
                            $sender->sendMessage(str_replace(
                                "{id}",
                                $id,
                                ConfigManager::getInstance()->getNested("messages.npc-addcmd")
                            ));
                        }else{
                            $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.npc-not-exist"));
                        }
                    }else{
                        $this->sendUsageMessage($sender);
                    }
                    break;

                default:
                    $this->sendHelpMessage($sender);
            }
        }else{
            $this->sendNotPlayerMessage($sender);
        }
    }

    publiC function sendHelpMessage(CommandSender $sender): void
    {
        $sender->sendMessage(ConfigManager::getInstance()->getNested("messages.npc-help"));
    }
}