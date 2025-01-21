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

class GiveawayCommands extends Command implements PluginOwned {

    private Giveaway $plugin;

    public function __construct(string $name, Giveaway $plugin) {
        parent::__construct($name, "Manage giveaways", "/giveaway <help|buyticket|finish>");
        $this->plugin = $plugin;
        $this->setPermission(DefaultPermissions::ROOT_OPERATOR); // Varsayılan yetki düzeyi
    }

    public function getOwningPlugin(): Giveaway {
        return $this->plugin;
    }

    public function execute(CommandSender $sender, string $label, array $args): bool {
        $api = GiveawayAPI::getInstance();

        if (empty($args)) {
            $sender->sendMessage("Usage: /giveaway help");
            return false;
        }

        switch (strtolower($args[0])) {
            case 'help':
                $sender->sendMessage("Giveaway Commands:\n" .
                                     "/giveaway help - Show this help message\n" .
                                     "/giveaway buyticket - Buy a giveaway ticket\n" .
                                     "/giveaway finish - Finish the giveaway (Admin only)");
                break;

            case 'buyticket':
                if (!$sender instanceof Player) {
                    $sender->sendMessage("This command can only be used in-game.");
                    return false;
                }

                if ($api->getMoney($sender) < 1000) {
                    $sender->sendMessage("You don't have enough money to buy a ticket.");
                    return false;
                }

                $api->addGiveaway($sender);
                break;

            case 'finish':
                if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    $sender->sendMessage("You don't have permission to use this command.");
                    return false;
                }

                $tickets = $api->getTickets();
                if (count($tickets) < 5) {
                    $sender->sendMessage("Not enough tickets to finish the giveaway. Current tickets: " . count($tickets));
                    return false;
                }

                $api->finishGiveaway();
                break;

            default:
                $sender->sendMessage("Unknown subcommand. Use /giveaway help for a list of commands.");
                break;
        }

        return true;
    }
}
