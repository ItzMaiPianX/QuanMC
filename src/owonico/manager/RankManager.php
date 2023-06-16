<?php

namespace owonico\manager;

use owonico\Main;
use owonico\rank\Rank;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class RankManager {

    /** @var Rank[] */
    public static $ranks = [];

    public static function init() {
        $ranks = [
            new Rank("Owner", "§8[§bOWNER§8]§r ", ["quza.builder", "quza.operator", "quza.staff", "pocketmine.command.gamemode", "pocketmine.command.teleport", "pocketmine.command.kick"]),
            new Rank("Developer", "§8[§aDEVELOPER§8]§r ", ["quza.operator", "quza.staff"]),
            new Rank("Admin", "§8[§aADMIN§8]§r ", ["quza.operator", "pocketmine.command.teleport", "pocketmine.command.kick", "quza.staff"]),
            new Rank("Staff", "§8[§dSTAFF§8]§r ", ["quza.staff", "pocketmine.command.teleport", "pocketmine.command.kick"]),
            new Rank("Mod", "§8[§aMOD§8]§r ", ["quza.moderator", "pocketmine.command.teleport", "pocketmine.command.kick", "quza.staff"]),
            new Rank("Helper", "§8[§aHELPER§8]§r ", ["quza.helper", "pocketmine.command.kick", "quza.staff"]),
            new Rank("Builder", "§8[§aBUILDER§8]§r ", ["quza.builder", "quza.staff", "buildertools.command"]),
            new Rank("NAGA", "§8[§aNAGA§8]§r ", ["quza.naga", "quza.mvp", "quza.vip"]),
            new Rank("MVP", "§8[§aMVP+§8]§r ", ["quza.mvp", "quza.vip"]),
            new Rank("VIP", "§8[§aVIP§8]§r ", ["quza.vip"]),
            new Rank("YouTube", "§c[§fYOUTUBE§c] ", ["quza.naga", "quza.mvp", "quza.vip"]),
            new Rank("Famous", "§8[§aFAMOUS§8]§r ", ["quza.mvp", "quza.vip"]),
            new Rank("Voter", "§8[§aVOTER§8]§r ", ["quza.voter"]),
            new Rank("Player", "§8[§aPLAYER§8]§r ", ["quza.player"], false)
        ];

        foreach ($ranks as $rank) {
            self::$ranks[strtolower($rank->getName())] = $rank;
        }
    }

    public static function setPlayerRank(Player $player, string $rank) {
        /** @var Rank|null $rankClass */
        $rankClass = self::$ranks[strtolower($rank)] ?? null;
        if($rankClass === null) {
            $player->kick("Invalid rank ($rank)");
            Main::getInstance()->getLogger()->info("§cReceived invalid rank ($rank)");
            return;
        }
        $rankCfg = new Config(Main::getInstance()->getDataFolder() . "rank/Rank.yml", Config::YAML);

        $rankCfg->set($player->getXuid(), $rankClass->getName());
        $rankCfg->save();

        $player->recalculatePermissions();
        foreach ($rankClass->getPermissions() as $permission) {
            $player->addAttachment(Main::getInstance(), $permission, true);
        }
    }

    public static function saveVoteTime(Player $player) {
        //QueryQueue::submitQuery(new UpdateRowQuery(["HasVoted" => 1, "VoteDate" => time()], "Name", $player->getName()));
        //TODO

        $voterCfg = new Config(Main::getInstance()->getDataFolder() . "rank/Voter.yml", Config::YAML);
        $voterCfg->set($player->getName(), time());
        $voterCfg->save();

        if (self::getPlayerRank($player)->getName() == "Player") {
            self::setPlayerRank($player, "Voter");
        }
    }

    public static function hasVoted(Player $player): bool {
        return self::getPlayerRank($player)->getName() == "Voter";
    }

    public static function checkRankExpiration(Player $player, int $voteTime) {
        if(self::getPlayerRank($player)->getName() != "Voter") {
            return;
        }
        if($voteTime + 86400 >= time()) {
            return;
        }

        $player->sendMessage("§e§l§oRANKS:§r§f:§b Your VOTER rank expired. Vote again to extend it.");
        if(self::getPlayerRank($player)->getName() == "Voter") {
            self::setPlayerRank($player, "Player");
        }

        $voterCfg = new Config(Main::getInstance()->getDataFolder() . "rank/Voter.yml", Config::YAML);
        $voterCfg->remove($player->getName());
        $voterCfg->save();

        //QueryQueue::submitQuery(new UpdateRowQuery(["HasVoted" => 0], "Name", $player->getName()));
    }

    public static function getPlayerRank(Player $player): Rank {
        $rankCfg = new Config(Main::getInstance()->getDataFolder() . "rank/Rank.yml", Config::YAML);
        if (!$rankCfg->exists($player->getXuid())){
            self::setPlayerRank($player, "Player");
        }
        return self::$ranks[strtolower((string) $rankCfg->get($player->getXuid()))] ?? self::$ranks["player"];
    }

    public static function getRankByName(string $rank): ?Rank {
        return self::$ranks[strtolower($rank)] ?? null;
    }
}
