<?php

namespace Olympia\Kitpvp\commands\admin;

use Exception;
use Olympia\Kitpvp\commands\OlympiaCommand;
use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\utils\constants\Permissions;
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
        if ($sender instanceof Session) {

            switch ($args[0] ?? null) {

                case "create":

                    if (isset($args[1])) {

                        $name = $args[1];
                        $id = Managers::NPC()->createNpc($sender, $name);
                        $sender->sendMessage(str_replace(
                            ["{name}", "{id}"],
                            [$name, $id],
                            Managers::CONFIG()->getNested("messages.npc-create")
                        ));
                    }else{
                        $this->sendUsageMessage($sender);
                    }
                    break;

                case "delete":

                    if (isset($args[1])) {

                        $id = $args[1];

                        if (is_int($id) && Managers::NPC()->getNpcExists($id)) {

                            Managers::NPC()->deleteNpc($id);
                            $sender->sendMessage(str_replace(
                                "{id}",
                                $id,
                                Managers::CONFIG()->getNested("messages.npc-delete")
                            ));
                        }else{
                            $sender->sendMessage(Managers::CONFIG()->getNested("messages.npc-not-exist"));
                        }
                    }else{
                        $this->sendUsageMessage($sender);
                    }
                    break;

                case "id":

                    Managers::NPC()->addPlayerWantId($sender->getName());
                    $sender->sendMessage(Managers::CONFIG()->getNested("messages.npc-id"));
                break;

                case "addcmd":

                    if (isset($args[1]) && isset($args[2])) {

                        $id = $args[1];
                        if (is_int($id) && Managers::NPC()->getNpcExists($id)) {

                            $command = "";
                            for ($i = 2; $i < count($args); $i++) {
                                $command .= $args[$i] . " ";
                            }
                            Managers::NPC()->addNpcCommand($id, $command);
                            $sender->sendMessage(str_replace(
                                "{id}",
                                $id,
                                Managers::CONFIG()->getNested("messages.npc-addcmd")
                            ));
                        }else{
                            $sender->sendMessage(Managers::CONFIG()->getNested("messages.npc-not-exist"));
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
        $sender->sendMessage(Managers::CONFIG()->getNested("messages.npc-help"));
    }
}