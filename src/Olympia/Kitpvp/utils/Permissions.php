<?php

namespace Olympia\Kitpvp\utils;

interface Permissions
{
    public const COMMAND_BOX = "olympia.command.spawn";
    public const COMMAND_GIVEKEY = "olympia.command.givekey";
    public const COMMAND_FORCECLEARLAG = "olympia.command.forceclearlag";
    public const COMMAND_NPC = "olympia.command.npc";
    public const COMMAND_STARTKOTH = "olympia.command.startkoth";
    public const COMMAND_CHESTREFILL = "olympia.command.chestrefill";

    public const COMMAND_ADDMONEY = "olympia.command.addmoney";
    public const COMMAND_REMOVEMONEY = "olympia.command.removemoney";

    public const COMMAND_STUFF = "olympia.command.stuff";


    public const COMMAND_KICK = "olympia.command.kick";
    public const COMMAND_MUTE = "olympia.command.mute";
    public const COMMAND_UNMUTE = "olympia.command.unmute";
    public const COMMAND_BAN = "olympia.command.ban";
    public const COMMAND_UNBAN = "olympia.command.unban";
    public const COMMAND_FREEZE = "olympia.command.freeze";
    public const COMMAND_UNFREEZE = "olympia.command.unfreeze";
    public const COMMAND_RTP = "olympia.command.rtp";
    public const COMMAND_ALIAS = "olympia.command.alias";
    public const COMMAND_CHAT = "olympia.command.chat";

    public const HOST_TOURNAMENT = "olympia.host.tournament";
    public const HOST_TOURNAMENT_12 = "olympia.host.tournament.12";
    public const HOST_TOURNAMENT_24 = "olympia.host.tournament.24";

    public const EXECUTE_COMMANDS_COMBAT = "olympia.execute.commands.combat";
    public const MESSAGE_COLORFUL = "olympia.message.colorful";

    public const BLOCK_BREAK = "olympia.block.break";
    public const BLOCK_PLACE = "olympia.block.place";
}