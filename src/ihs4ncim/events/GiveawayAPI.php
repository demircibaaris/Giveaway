<?php

namespace ihs4ncim\events;

use ihs4ncim\events\Giveaway;
use pocketmine\Server;
use pocketmine\utils\Config;

class GiveawayAPI{
 
 public static $api;
 public $dataFolder;
 
 public function init():void{
  self::$api = $this;
  $this->dataFolder = Server::getInstance()->getDataPath() . 'plugin_data/Giveaway/';
 }

 public static function getAPI():GiveawayAPI{
  return self::$api;
 }

 public function getData(){
  return $data = new Config($this->dataFolder . "giveaway.yml", Config::YAML, array());
 }

 public function finishGiveaway(){
  $randomticket = $this->getRandomTicket();
  $winner = $this->getWinner($randomticket);
  Server::getInstance()->broadcastMessage("The winner of the big giveaway is $randomticket | $winner");
  $this->addMoney($winner);
  $this->resetTickets();
 }

 public function createTicket(){
  $string = 'TICKET-';
  $characters = '0123456789';
  for ($i = 0; $i < 5; $i++) {
   $string .= $characters[rand(0, strlen($characters)-1)];
  }
  return $string;
 }

 public function resetTickets(){
  $data = $this->getData();
  $tickets = array_keys($this->getTickets());
  foreach($tickets as $ticket){
   $data->remove($ticket);
   $data->save();
  }
  Server::getInstance()->getLogger()->info("All data has been reset.");
 }

 public function getTickets(){
  $data = $this->getData();
  return $tickets = $data->getAll();
 }

 public function getRandomTicket(){
  $list = $this->getTickets();
  return $ticket = array_rand($list);
 }

 public function getWinner($ticket){
  $list = $this->getTickets();
  return $list[$ticket];
 }

 public function inGiveaway($ticket):bool{
  $data = $this->getData();
  if($data->getNested($ticket)){
   return true;
  }else{
   return false;
  }
 }

 public function addGiveaway($player){
  $ticket = $this->createTicket();
  $data = $this->getData();
  if($this->inGiveaway($ticket) == true){
   return $player->sendMessage("§cTry again.");
  }else{
   $this->reduceMoney($player);
   $this->registGiveaway($ticket, $player);
   $player->sendMessage("You bought a ticket.");
  }
 }

 public function registGiveaway($ticket, $player):void{
  $data = $this->getData();
  $data->set($ticket, $player->getName());
  $data->save();
 }

 public function getEconomyAPI(){
  return $economy = Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI");
 }

 public function getMoney($player){
  return $this->getEconomyAPI()->myMoney($player->getName());
 }

 public function reduceMoney($player){
  $this->getEconomyAPI()->reduceMoney($player->getName(), 1000);
 }

 public function addMoney($player){
  $this->getEconomyAPI()->addMoney($player, $this->getReward());
 }

 public function getReward():int{
  $tickets = $this->getTickets();
  $lentickets = count($tickets);
  return $lentickets*1000;
 }
}