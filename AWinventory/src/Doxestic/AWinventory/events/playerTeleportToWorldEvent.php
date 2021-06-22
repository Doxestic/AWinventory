<?php


namespace Doxestic\AWinventory\events;


use Doxestic\AWinventory\Main;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\Level;
use pocketmine\Player;

class playerTeleportToWorldEvent implements Listener
{

    public $main;

    public function __construct(Main $pl)
    {
        $this->main = $pl;
    }

    public function onTeleport(EntityTeleportEvent $event){
        $player = $event->getEntity();
        if ($player instanceof Player){
            $fromWorld = $event->getFrom()->getLevel();
            $toWorld = $event->getTo()->getLevel();
            if ($fromWorld->getFolderName() != $toWorld->getFolderName()){
                $config = $this->main->config;
                $data = $this->main->data;
                if (isset($config->getAll()["multiInventory"])){
                    if (in_array($toWorld->getFolderName(), $config->getAll()["multiInventory"])){
                        if (in_array($fromWorld->getFolderName(), $config->getAll()["multiInventory"])) {
                            $this->doInventory($player, $toWorld, $fromWorld, true, true);
                        }else{
                            $this->doInventory($player, $toWorld, $fromWorld, true);
                        }
                    }else{
                        if (in_array($fromWorld->getFolderName(), $config->getAll()["multiInventory"])) {
                            $this->doInventory($player, $toWorld, $fromWorld, false, true);
                        }else{
                            $this->doInventory($player, $toWorld, $fromWorld);
                        }
                    }
                }else{
                    $this->doInventory($player, $toWorld, $fromWorld);
                }
            }
        }
    }

    private function doInventory(Player $player, Level $toWorld, Level $fromWorld, bool $isMulti = false, bool $isFromWorldMulti = false){
        $this->fixData($player, $fromWorld, $toWorld);
        $data = $this->main->data;
        $mainInventory = $player->getInventory()->getContents();
        if ($isFromWorldMulti){
            $dArr = $data->getAll();
            $dArr[$player->getName()]["multi"] = $this->code($player->getInventory()->getContents());
            $data->setAll($dArr);
        }else{
            $dArr = $data->getAll();
            $dArr[$player->getName()][$fromWorld->getFolderName()] = $this->code($player->getInventory()->getContents());
            $data->setAll($dArr);
        }
        if ($isMulti){
            $targetInventory = $this->unCode($data->getAll()[$player->getName()]["multi"]);
        }else{
            $targetInventory = $this->unCode($data->getAll()[$player->getName()][$toWorld->getFolderName()]);
        }
        $player->getInventory()->setContents($targetInventory);
    }

    private function checkInventoryIsset(Player $player) : bool{
        $data = $this->main->data;
        if (isset($data->getAll()[$player->getName()])){
            return true;
        }else{
            return false;
        }
    }

    private function fixData(Player $player, Level $fromWorld, Level $toWorld){
        $data = $this->main->data;
        $dArr = $data->getAll();
        if (!isset($dArr[$player->getName()])){
            $dArr[$player->getName()] = [];
        }
        if (!isset($dArr[$player->getName()]["multi"])){
            $dArr[$player->getName()]["multi"] = [];
        }
        if (!isset($dArr[$player->getName()][$fromWorld->getFolderName()])){
            $dArr[$player->getName()][$fromWorld->getFolderName()] = [];
        }
        if (!isset($dArr[$player->getName()][$toWorld->getFolderName()])){
            $dArr[$player->getName()][$toWorld->getFolderName()] = [];
        }
        $data->setAll($dArr);
    }
    private function checkItemInInventory(Player $player, $item) : bool{
        $data = $this->main->data;
        if (isset($data->getAll()[$player->getName()][$item])){
            return true;
        }else{
            return false;
        }
    }

    public function onLeft(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        $fWorld = $player->getLevel();
        $this->fixData($player, $fWorld, $fWorld);
        $data = $this->main->data;
        $dArr = $data->getAll();
        $dArr[$player->getName()][$fWorld->getFolderName()] = $this->code($player->getInventory()->getContents());
        $data->setAll($dArr);
    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $fWorld = $player->getLevel();
        $this->fixData($player, $fWorld, $fWorld);
        $data = $this->main->data;
        $config = $this->main->config;
        if (in_array($fWorld->getFolderName(), $config->getAll()["multiInventory"])){
            $worldName = "multi";
        }
        else{
            $worldName = $fWorld->getFolderName();
        }
        $player->getInventory()->setContents($this->unCode($data->getAll()[$player->getName()][$worldName]));
    }

    private function unCode($items){
        $arr = [];
        foreach ($items as $item){
            $arr[] = unserialize($item);
        }
        return $arr;
    }

    private function code($items){
        $arr = [];
        foreach ($items as $item){
            $arr[] = serialize($item);
        }
        return $arr;
    }
}