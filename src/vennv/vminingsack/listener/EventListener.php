<?php

/**
 * VPickaxe - PocketMine plugin.
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

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use vennv\vminingsack\data\DataManager;

final class EventListener implements Listener {

    /**
     * @throws \Throwable
     */
    public function onInteract(PlayerInteractEvent $event) : void {
        $fiber = new \Fiber(function() use ($event) {
            \Fiber::suspend();
            $player = $event->getPlayer();
            $item = $player->getInventory()->getItemInHand();
            if (DataManager::isMiningSack($item)) {
                DataManager::openMiningSack($player, $item);
                $event->cancel();
            }
        });
        $fiber->start();
        $fiber->resume();
    }

    /**
     * @throws \Throwable
     */
    public function onBreak(BlockBreakEvent $event) : void {
        $fiber = new \Fiber(function() use ($event) {
            \Fiber::suspend();
            $player = $event->getPlayer();
            $inventory = $player->getInventory();
            $drops = $event->getDrops();

            $cancel = false;
            foreach ($inventory->getContents() as $index => $content) {

                if ($cancel) {
                    break;
                }

                if (DataManager::isMiningSack($content)) {

                    $type = DataManager::getType($content);
                    $tags = DataManager::getTagsListOres($type);

                    foreach ($drops as $item) {
                        if (in_array($item->getName(), array_values($tags))) {

                            $size = (int) DataManager::getConfig()->getNested("types." . $type . ".size");

                            $value = $content->getNamedTag()->getInt(array_search($item->getName(), $tags));

                            $balancing = DataManager::getBalancing($item->getCount(), $value, $size);

                            if ($balancing->should) {
                                $content->getNamedTag()->setInt(
                                    array_search($item->getName(), $tags),
                                    $balancing->add
                                );
                                $inventory->setItem($index, $content);
                                $item->setCount($balancing->quantity);
                                $cancel = true;
                            }
                        }
                    }
                }
            }
        });
        $fiber->start();
        $fiber->resume();
    }
}