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

namespace vennv\vminingsack;

use muqsit\invmenu\InvMenuHandler;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use vennv\vminingsack\data\DataManager;
use vennv\vminingsack\listener\CommandListener;
use vennv\vminingsack\listener\EventListener;

class VMiningSack extends PluginBase implements Listener {

    private static ?VMiningSack $instance = null;

    public static function getInstance() : ?VMiningSack {
        return self::$instance;
    }

    public function onLoad() : void {
        self::$instance = $this;
    }

    public function onEnable() : void {
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
        return (new CommandListener())->onCommand($sender, $command, $label, $args);
    }

    public function getDataManager() : DataManager {
        return (new DataManager());
    }

}