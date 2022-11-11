<?php

namespace ihs4ncim\events;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use ihs4ncim\events\commands\GiveawayCommands;

class Giveaway extends PluginBase{

 public function onLoad():void{
  $api = new GiveawayAPI;
  $api->init();
  $api->getData();
  @mkdir($this->getDataFolder());
 }

 public function onEnable():void{
  $economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
  if($economy){
   $this->registerCommands();
  }else{
   $this->getLogger()->emergency("Please download the EconomyAPI plugin.");
  }
 }

 public function registerCommands(){
  $command = $this->getServer()->getCommandMap();
  $command->register("giveaway", new GiveawayCommands("giveaway", $this));
 }
}