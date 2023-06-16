<?php

namespace owonico\command;

use owonico\{Main, Variables};
use maipian\webhook\Embed;
use maipian\webhook\Message;
use maipian\webhook\Webhook;
use owonico\manager\RankManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class CoreCommand extends Command{

    public $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct("core", "QuanMC Main Command");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) return;
        if (!$sender->hasPermission("quza.staff")){
            $sender->sendMessage(Variables::Prefix . "§cYou dont have permission to perform this command!");
            return;
        }

        if (isset($args[0])){
            switch ($args[0]){
                case "setrank":
                    if (!isset($args[1]) && !isset($args[2])){
                        $sender->sendMessage("/core setrabk <Player> <Rank>");
                        return;
                    }
                    $playername = $args[1] ?? $sender->getName();
                    $rank = $args[2] ?? "Player";

                    $rankClass = RankManager::$ranks[strtolower($rank)] ?? null;
                    if ($rankClass === null){
                        $sender->sendMessage(Variables::Prefix . "§cRank with name {$rank} is not found!");
                        return;
                    }
                    $player = $this->plugin->getServer()->getPlayerExact($playername);
                    RankManager::setPlayerRank($player, $rank);
                    $sender->sendMessage(Variables::Prefix . "§aSuccessfully changed {$player->getName()}'s rank to {$rankClass->getDisplayFormat()}");
                    $player->sendMessage(Variables::Prefix . "§aYour rank has been changed to " . $rankClass->getDisplayFormat() . " by §6" . $sender->getName());
                    break;
            }
        }

        if (isset($args[0])){
            switch($args[0]){
                case "mm":
                    switch (strtolower($args[1])) {
                        case "on":
                            $this->plugin->getConfig()->set("maintenance", true);
                            $this->plugin->getConfig()->save();
                            $sender->sendMessage(Variables::Prefix . "§aYou have turned on maintenance");

                            $webhook = new Webhook("https://discord.com/api/webhooks/1119182130367447071/QwUTeCJqgXqThjdWzIjurke6V3Dhb0I4n2jYyWf88OqLq7pCdJcm1w-IGG-vv31CmEnw");
                            $embed = new Embed();
                            $embed->setTitle("Status");
                            $embed->setDescription("QuzaNetwork is now turned on maintenance");
                            $embed->setFooter("QuanMC & QuanAPI");
                            $embed->setColor(0xFE0C0C);
                            $message = new Message();
                            $message->addEmbed($embed);
                            $webhook->send($message);

                            break;
                        case "off":
                            $this->plugin->getConfig()->set("maintenance", false);
                            $this->plugin->getConfig()->save();
                            $sender->sendMessage(Variables::Prefix . "§aYou have turned off maintenance");

                            $webhook = new Webhook("https://discord.com/api/webhooks/1119182130367447071/QwUTeCJqgXqThjdWzIjurke6V3Dhb0I4n2jYyWf88OqLq7pCdJcm1w-IGG-vv31CmEnw");
                            $embed = new Embed();
                            $embed->setTitle("Status");
                            $embed->setDescription("QuzaNetwork is now turned off maintenance");
                            $embed->setFooter("QuanMC & QuanAPI");
                            $embed->setColor(0x4EFA03);
                            $message = new Message();
                            $message->addEmbed($embed);
                            $webhook->send($message);

                            break;
                    }
            }
        }

        if (isset($args[0])){
            switch($args[0]){
                case "help":
                    $sender->sendMessage("- /core setrank <Name> <Rank>\n- /core mm off/on");
            }
        }
    }
}
