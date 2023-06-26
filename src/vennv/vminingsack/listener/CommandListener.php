<?php

/**
 * VMiningSack - PocketMine plugin.
 * Copyright (C) 2023 - 2025 VennDev
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace vennv\vminingsack\listener;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use vennv\vminingsack\data\DataManager;

final class CommandListener {

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
        if ($command->getName() == "vminingsack") {
            if (isset($args[0])) {
                if ($args[0] == "give") {
                    if (!isset($args[1])) {
                        return false;
                    } else {
                        if (!isset($args[2]) || !isset($args[3])) {
                            return false;
                        } else {
                            if (!is_numeric($args[3])) {
                                $sender->sendMessage("Amount must be a number");
                                return true;
                            }
                            $player = $sender->getServer()->getPlayerExact($args[1]);
                            if ($player == null) {
                                $sender->sendMessage("Player not found");
                                return true;
                            } else {
                                $type = $args[2];
                                if (DataManager::getConfig()->getNested("types." . $type) === null) {
                                    $sender->sendMessage("Type not found");
                                    return true;
                                }
                                DataManager::giveMiningStack($player, $type, (int) $args[3]);
                            }
                        }
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }

}