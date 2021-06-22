<?php

declare(strict_types=1);

namespace Doxestic\AWinventory;

use Doxestic\AWinventory\events\playerTeleportToWorldEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;


class Main extends PluginBase{

    public const NAME = TF::AQUA . "[ " . TF::RED . "AW Inventory " . TF::AQUA . "] " . TF::LIGHT_PURPLE;

    public $config;
    public $data;
    /*
     * data:
     *   playerName:
     *     World1:[item...]
     *     world2:[item...]
     */

    public function onEnable()
    {
        $this->getServer()->getLogger()->info(self::NAME . "enabling Plugin...");
        $this->getServer()->getLogger()->info(self::NAME . "loading Config.yml");
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML);
        $this->getServer()->getLogger()->info(self::NAME . "Config loaded!");
        $this->getServer()->getLogger()->info(self::NAME . "Loading Data...");
        $this->data = new Config($this->getDataFolder()."data.yml", Config::YAML);
        $this->getServer()->getLogger()->info(self::NAME . "enabling events...");
        $this->getServer()->getPluginManager()->registerEvents(new playerTeleportToWorldEvent($this), $this);
        $this->getServer()->getLogger()->info(self::NAME . "plugin Enabled!");
        parent::onEnable(); // TODO: Change the autogenerated stub
    }

    public function onDisable()
    {
        $this->data->save();
        parent::onDisable(); // TODO: Change the autogenerated stub
    }
}