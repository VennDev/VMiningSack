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

namespace vennv\vminingsack\data;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\nbt\tag\Tag;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use vennv\vminingsack\utils\Balance;
use vennv\vminingsack\utils\ItemUtil;
use vennv\vminingsack\VMiningSack;

final class DataManager {

    public static function getConfig() : Config {
        return VMiningSack::getInstance()->getConfig();
    }

    public static function isMiningSack(Item $item) : bool {
        try {
            return $item->getNamedTag()->getString("vminingsack") === "vminingsack";
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function getType(Item $item) : string {
        return $item->getNamedTag()->getString("vminingsacktype");
    }

    public static function generatorIdMiningStack(Player $player) : string {
        return $player->getXuid() . "-" . microtime(true);
    }

    public static function checkItemHadTag(Item $item, string $tag) : null|Tag {
        return $item->getNamedTag()->getTag($tag);
    }

    public static function getTagsListOres(string $type) : array {
        $tags = [];
        $ores = (array) self::getConfig()->getNested("types." . $type . ".ores");
        foreach ($ores as [$ore, $name]) {
            if ($name === false) {
                $tagOre = strtolower("v".$ore);
                $tags[$tagOre] = ItemUtil::getItem($ore)->getName();
            } else {
                $tagOre = strtolower("v".$ore."_".$name);
                $tags[$tagOre] = ItemUtil::getItem($ore)->setCustomName($name)->getName();
            }
        }
        return $tags;
    }

    public static function giveMiningStack(Player $player, string $type, int $amount) : bool {
        if (self::getConfig()->getNested("types." . $type) === null) {
            return false;
        }

        $material = (string) self::getConfig()->getNested("types." . $type . ".item.material");
        $name = (string) self::getConfig()->getNested("types." . $type . ".name");
        $ores = (array) self::getConfig()->getNested("types." . $type . ".ores");

        $lastId = "";
        for ($i = 0; $i < $amount; $i++) {
            $id = self::generatorIdMiningStack($player);
            if ($id != $lastId) {

                $item = ItemUtil::getItem($material);
                $item->setCustomName($name);

                $item->getNamedTag()->setString("vminingsack", "vminingsack");
                $item->getNamedTag()->setString("id_vminingsack", $id);
                $item->getNamedTag()->setString("vminingsacktype", $type);

                foreach ($ores as [$ore, $name]) {
                    if ($name === false) {
                        $tagOre = strtolower("v".$ore);
                    } else {
                        $tagOre = strtolower("v".$ore."_".$name);
                    }
                    $item->getNamedTag()->setInt($tagOre, 0);
                }

                $player->getInventory()->addItem($item);

                $lastId = $id;
            } else {
                $i--;
            }
        }

        return true;
    }

    // Check balancing trading or not?
    public static function getBalancing(int|float $add, int|float $value, int|float $size) : Balance {

        $should = true;
        $quantity = 0;
        $add = $value + $add;

        if ($value === $size) {
            $should = false;
        }

        if ($add > $size) {
            $add += $size - $add;
            $quantity = abs($size - $add);
        }

        return new Balance($should, $add, $quantity);
    }

    /**
     * Check if the item is a mining stack and if it is, open the gui
     *
     * @throws \Throwable
     */
    public static function openMiningSack(Player $player, Item $item) : void {

        $cloneItem = clone $item;
        if (self::isMiningSack($item)) {

            $type = self::getType($item);
            if (self::getConfig()->getNested("types." . $type) === null) {
                return;
            }

            $size = (int) self::getConfig()->getNested("types." . $type . ".size");
            $name = (string) self::getConfig()->getNested("types." . $type . ".name");
            $ores = (array) self::getConfig()->getNested("types." . $type . ".ores");
            $borderMaterial = (string) self::getConfig()->getNested("gui.border.material");
            $loreOreItem = (array) self::getConfig()->getNested("gui.ore_item.lore");

            $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
            $menu->setName($name);

            $inventory = $menu->getInventory();

            $border = [8, 17, 26, 35, 44, 53];
            $itemBorder = ItemUtil::getItem($borderMaterial)->setCustomName("_");

            $fibers = [];
            foreach ($border as $slot) {
                $fiber = new \Fiber(function() use ($player, $slot, $inventory, $itemBorder) {
                    \Fiber::suspend(microtime(true) . $player->getName());
                    if ($slot == 8 || $slot == 53) {
                        for ($i = $slot - 8; $i <= $slot; $i++) {
                            $inventory->setItem($i, $itemBorder);
                        }
                    } else {
                        $inventory->setItem($slot - 8, $itemBorder);
                        $inventory->setItem($slot, $itemBorder);
                    }
                });
                $fiber->start();
                $fibers[] = $fiber;
            }
            foreach ($fibers as $fiber) {
                $fiber->resume();
            }

            $materialInsertItem = (string) self::getConfig()->getNested("gui.insert_item.material");
            $nameInsertItem = (string) self::getConfig()->getNested("gui.insert_item.name");
            $loreInsertItem = (array) self::getConfig()->getNested("gui.insert_item.lore");
            $slotInsertItem = (int) self::getConfig()->getNested("gui.insert_item.slot");

            $insertItem = ItemUtil::getItem($materialInsertItem);
            $insertItem->setCustomName($nameInsertItem);
            $insertItem->setLore($loreInsertItem);
            $insertItem->getNamedTag()->setString("vinsert_item", "vinsert_item");

            $inventory->setItem($slotInsertItem, $insertItem);

            $i = 0;
            $tags = [];
            foreach ($ores as [$ore, $name]) {
                if ($i > 28) {
                    break;
                }

                if ($name === false) {
                    $tagOre = strtolower("v".$ore);
                } else {
                    $tagOre = strtolower("v".$ore."_".$name);
                }

                if (self::checkItemHadTag($item, $tagOre) === null) {
                    break;
                }

                $count = self::checkItemHadTag($item, $tagOre)->getValue();
                if (!is_numeric($count) || !is_int($count)) {
                    //TODO: Error
                    break;
                }

                $itemOre = ItemUtil::getItem($ore);

                if ($name !== false) {
                    $itemOre->setCustomName($name);
                }

                $loreReplaced = str_replace(["%count%", "%max%"], [$count, $size], $loreOreItem);
                $itemOre->setLore($loreReplaced);

                // This forces it to be in the form of an integer data type
                $itemOre->getNamedTag()->setInt($tagOre, $count);

                $inventory->addItem($itemOre);

                $tags[$tagOre] = true;
                $i++;
            }

            $menu->setListener(function(InvMenuTransaction $transaction) use ($item, $tags, $loreOreItem, $size) : InvMenuTransactionResult {

                $player = $transaction->getPlayer();
                $itemClicked = $transaction->getItemClicked();
                $inventory = $transaction->getAction()->getInventory();

                if ($itemClicked->getNamedTag()->getTag("vinsert_item") !== null) {
                    $fiber = new \Fiber(function() use ($item, $player, $inventory, $loreOreItem, $size) {
                        \Fiber::suspend();
                        array_map(function ($content) use ($item, $player, $inventory, $loreOreItem, $size) {

                            $type = self::getType($item);
                            $tags = self::getTagsListOres($type);

                            if (in_array($content->getName(), array_values($tags))) {

                                $checkTag = array_search($content->getName(), $tags);
                                $itemInInvPlayer = $content;

                                array_map(function ($ct) use ($itemInInvPlayer, $checkTag, $inventory, $loreOreItem, $size, $player) {
                                    $tagValue = $ct->getNamedTag()->getTag($checkTag)?->getValue();
                                    if ($tagValue !== null) {

                                        $balancing = self::getBalancing($itemInInvPlayer->getCount(), $tagValue, $size);

                                        if ($balancing->should) {
                                            $itemNew = clone $ct;

                                            $loreReplaced = str_replace(["%count%", "%max%"], [$balancing->add, $size], $loreOreItem);
                                            $itemNew->setLore($loreReplaced);
                                            $itemNew->getNamedTag()->setInt($checkTag, $balancing->add);

                                            $itemNew->setCount(1);

                                            $inventory->setItem(array_search($ct, $inventory->getContents()), $itemNew);

                                            $player->getInventory()->setItem(array_search($itemInInvPlayer, $player->getInventory()->getContents()), $itemInInvPlayer->setCount($balancing->quantity));
                                        }
                                    }
                                }, $inventory->getContents());
                            }
                        }, $player->getInventory()->getContents());
                    });
                    $fiber->start();
                    $fiber->resume();
                    return $transaction->discard();
                }

                foreach ($tags as $tag => $value) {
                    if ($itemClicked->getNamedTag()->getTag($tag) !== null) {

                        // It's definitely an integer!
                        $count = $itemClicked->getNamedTag()->getTag($tag)->getValue();

                        $slotsInvPlayer = 36; // 36 = 4 rows * 9 columns
                        foreach ($player->getInventory()->getContents() as $content) {
                            $slotsInvPlayer--;
                        }

                        $countCanAdd = $itemClicked->getMaxStackSize() * $slotsInvPlayer;

                        if ($count > 0) {

                            if ($count > $countCanAdd) {
                                $amount = $count - $countCanAdd;
                            } else {
                                $amount = $count;
                            }

                            $clone = clone $itemClicked->setCount($amount);

                            $player->getInventory()->addItem($clone->setLore([]));

                            $count -= $amount;
                        }

                        $itemNew = clone $itemClicked;
                        $loreReplaced = str_replace(["%count%", "%max%"], [$count, $size], $loreOreItem);

                        $itemNew->setLore($loreReplaced);
                        $itemNew->getNamedTag()->setInt($tag, $count);

                        $inventory->setItem($transaction->getAction()->getSlot(), $itemNew);
                    }
                }

                return $transaction->discard();
            });

            $menu->setInventoryCloseListener(function(Player $player, Inventory $inventory) use ($tags, $item, $cloneItem) {
                $player->getInventory()->removeItem($item);
                $player->selectHotbarSlot(0);

                array_map(function ($content) use ($cloneItem, $tags) {
                    foreach ($tags as $tag => $value) {
                        $tagValue = $content->getNamedTag()->getTag($tag)?->getValue();
                        if ($tagValue !== null) {
                            $cloneItem->getNamedTag()->setInt($tag, $tagValue);
                        }
                    }
                }, $inventory->getContents());

                $player->getInventory()->addItem($cloneItem);
            });

            $menu->send($player);
        }
    }

}