<?php

namespace ihs4ncim\events\commands;

use ihs4ncim\events\Giveaway;
use ihs4ncim\events\GiveawayAPI;
use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\permission\DefaultPermissions;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\plugin\PluginOwned;

class GiveawayCommands extends Command implements PluginOwned{

 private $plugin;

 public function getOwningPlugin() : Giveaway {
  return $this->plugin;
 }

 public function __construct($name, Giveaway $plugin){
  parent::__construct("giveaway", "Giveaway", "/giveaway");
  $this->plugin = $plugin;
 }

 public function execute(CommandSender $sender, string $label, array $args): bool{
  $api = GiveawayAPI::getAPI();
  if($args){
   $arg = $args[0];
   if($arg){
    if($arg == 'help'){
     $sender->sendMessage("Giveaway Help:\n\n/giveaway help\n/giveaway buyticket\n/giveaway finish");
    }elseif($arg == 'buyticket'){
     if($sender instanceof Player){
      if($api->getMoney($sender) >= 1000){
       $api->addGiveaway($sender);
      }else{
       $sender->sendMessage("You don't have enough money.");
      }
     }else{
      $sender->sendMessage("Use in game.");
     }
    }elseif($arg == 'finish'){
     if($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){
      $tickets = $api->getTickets();
      if(count($tickets) >= 5){
       $api->finishGiveaway();
      }else{
       $sender->sendMessage("More tickets needed to finish. Tickets: " . count($api->getTickets()));
      }
     }else{
      $sender->sendMessage("You don't have permission.");
     }
    }
   }else{
    $sender->sendMessage("/giveaway help");
   }
  }else{
   $sender->sendMessage("/giveaway help");
  }
  return true;
 }
}
