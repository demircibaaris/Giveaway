<?php

namespace ihs4ncim\events;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use ihs4ncim\events\commands\GiveawayCommands;

class Giveaway extends PluginBase {

    public function onLoad(): void {
        // GiveawayAPI'yi başlat ve veri dizinini oluştur
        $api = new GiveawayAPI();
        $api->init();
        $api->getData();

        if (!is_dir($this->getDataFolder())) {
            mkdir($this->getDataFolder(), 0777, true);
        }
    }

    public function onEnable(): void {
        // EconomyAPI eklentisinin yüklü olup olmadığını kontrol et
        $economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");

        if ($economy) {
            $this->registerCommands();
        } else {
            $this->getLogger()->emergency("Please download and enable the EconomyAPI plugin.");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
    }

    private function registerCommands(): void {
        // Komutları kaydet
        $commandMap = $this->getServer()->getCommandMap();
        $commandMap->register("giveaway", new GiveawayCommands("giveaway", $this));
    }
}
