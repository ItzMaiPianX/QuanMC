<?php

namespace owonico\task;

use owonico\Main;
use pocketmine\scheduler\Task;

class BroadcastTask extends Task{

    public $plugin;

    private static $message = [
        "§c[!] §6Welcome to QuzaPractice !",
        "§c[!] §6Join the offical discord(https://dsc.gg/quza) to learn more !",
        "§c[!] §6Have fun !"];

    private static $instance = 0;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onRun(): void
    {
        $this->plugin->getServer()->broadcastMessage(self::$message[self::$instance]);
        self::$instance++;
        if(self::$instance > count(self::$message)-1)self::$instance = 0;
    }
}
