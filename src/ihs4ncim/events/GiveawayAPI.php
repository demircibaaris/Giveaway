<?php

namespace ihs4ncim\events;

use pocketmine\Server;
use pocketmine\utils\Config;

class GiveawayAPI {

    private static ?GiveawayAPI $instance = null;
    private string $dataFolder;

    public function __construct() {
        $this->dataFolder = Server::getInstance()->getDataPath() . 'plugin_data/Giveaway/';
        if (!self::$instance) {
            self::$instance = $this;
        }
    }

    public static function getInstance(): GiveawayAPI {
        if (!self::$instance) {
            throw new \RuntimeException("GiveawayAPI instance not initialized.");
        }
        return self::$instance;
    }

    private function getData(): Config {
        return new Config($this->dataFolder . "giveaway.yml", Config::YAML, []);
    }

    public function finishGiveaway(): void {
        $randomTicket = $this->getRandomTicket();
        $winner = $this->getWinner($randomTicket);

        if (!$randomTicket || !$winner) {
            Server::getInstance()->broadcastMessage("No winner could be determined.");
            return;
        }

        Server::getInstance()->broadcastMessage("The winner of the big giveaway is $randomTicket | $winner");
        $this->addMoney($winner);
        $this->resetTickets();
    }

    public function createTicket(): string {
        $prefix = 'TICKET-';
        $characters = '0123456789';
        $ticket = $prefix;

        for ($i = 0; $i < 5; $i++) {
            $ticket .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $ticket;
    }

    public function resetTickets(): void {
        $data = $this->getData();
        foreach (array_keys($this->getTickets()) as $ticket) {
            $data->remove($ticket);
        }
        $data->save();
        Server::getInstance()->getLogger()->info("All giveaway data has been reset.");
    }

    public function getTickets(): array {
        return $this->getData()->getAll();
    }

    public function getRandomTicket(): ?string {
        $tickets = array_keys($this->getTickets());
        return $tickets ? $tickets[array_rand($tickets)] : null;
    }

    public function getWinner(string $ticket): ?string {
        $tickets = $this->getTickets();
        return $tickets[$ticket] ?? null;
    }

    public function inGiveaway(string $ticket): bool {
        return $this->getData()->exists($ticket);
    }

    public function addGiveaway($player): void {
        $ticket = $this->createTicket();

        if ($this->inGiveaway($ticket)) {
            $player->sendMessage("\u00a7cTry again.");
            return;
        }

        if ($this->getMoney($player) < 1000) {
            $player->sendMessage("\u00a7cNot enough money to buy a ticket.");
            return;
        }

        $this->reduceMoney($player);
        $this->registerGiveaway($ticket, $player);
        $player->sendMessage("You bought a ticket.");
    }

    private function registerGiveaway(string $ticket, $player): void {
        $data = $this->getData();
        $data->set($ticket, $player->getName());
        $data->save();
    }

    private function getEconomyAPI() {
        return Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI");
    }

    private function getMoney($player): float {
        $economy = $this->getEconomyAPI();
        return $economy ? $economy->myMoney($player->getName()) : 0.0;
    }

    private function reduceMoney($player): void {
        $economy = $this->getEconomyAPI();
        if ($economy) {
            $economy->reduceMoney($player->getName(), 1000);
        }
    }

    private function addMoney(string $playerName): void {
        $economy = $this->getEconomyAPI();
        if ($economy) {
            $economy->addMoney($playerName, $this->getReward());
        }
    }

    private function getReward(): int {
        return count($this->getTickets()) * 1000;
    }
}
