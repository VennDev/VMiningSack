# VMiningSack
<img src="https://github.com/VennDev/VMiningSack/blob/main/icon.png" alt="VMiningSack" height="150" width="150" />
- One plugin for PocketMine-PMMP 5

# How to install it ?
- You should install InvMenu here: [Sources](https://github.com/Muqsit/InvMenu)

# Notes
- The image representing this plugin is for illustrative purposes only, if you want similar objects you can customize your own.

# Commands
```
/vminingsack or /vms - give <player> <type> <amount>
```

# Features
- Save your bag space as you dig mines or collect blocks packed into something called VMiningSack
- Automatically pocket objects or so-called blocks into your Sack when digging them.
- Allow pocketing similar items or blocks in your Sack with 1 tap.
- It's all done on asynchronous which makes the plugin lightweight.

# Config
```config
---

####################################################################################################
# Frequently Asked Questions:
#
# What is the material? - They are the names of id-like objects in vanilla
#                         minecraft bedrock, example: /give @s diamond 1
#
# Can I create multiple types of sacks? - Quite possibly, you can just copy a template
#                                         I already have and rename it like small, medium, ... according
#                                         to your preferences and reset everything according to
#                                         the needs that you need.
####################################################################################################

# GUI Settings
gui:
  border:
    # Border material
    material: "barrier"
  ore_item:
    lore:
      - "§r§7%count%x§f/§c%max%x"

  insert_item:
    # The place of this object on the GUI
    slot: 49
    # Material
    material: "chest"
    # Name for the item
    name: "§aInsert inventory"
    # Lore for the item
    lore:
      - "§r§7Insert your inventory items into your sacks!"
      - "§r§eClick to put all items in!"

# Types of mining stacks
types:
  small:
    # Item for the mining sack
    item:
      material: "chest"
    # Sizes to accommodate each type of ores
    size: 100
    # Name to be displayed in the GUI
    name: "Small Mining Sack"
    # Types of ores, max is 28
    ores:
      # material, name (name is optional)
      - [ "coal", false ]
      - [ "raw_iron", false ]
      - [ "raw_gold", false ]
      - [ "raw_copper", false ]
      - [ "redstone_dust", false ]
      - [ "diamond", false ]
      - [ "emerald", false ]
  medium:
    # Item for the mining sack
    item:
      material: "chest"
    # Sizes to accommodate each type of ores
    size: 500
    # Name to be displayed in the GUI
    name: "Medium Mining Sack"
    # Types of ores, max is 28
    ores:
      # material, name (name is optional)
      - [ "coal", false ]
      - [ "raw_iron", false ]
      - [ "raw_gold", false ]
      - [ "raw_copper", false ]
      - [ "redstone_dust", false ]
      - [ "diamond", false ]
      - [ "emerald", false ]
  large:
    # Item for the mining sack
    item:
      material: "chest"
    # Sizes to accommodate each type of ores
    size: 1000
    # Name to be displayed in the GUI
    name: "Large Mining Sack"
    # Types of ores, max is 28
    ores:
      # material, name (name is optional)
      - [ "coal", false ]
      - [ "raw_iron", false ]
      - [ "raw_gold", false ]
      - [ "raw_copper", false ]
      - [ "redstone_dust", false ]
      - [ "diamond", false ]
      - [ "emerald", false ]
  extra:
    # Item for the mining sack
    item:
      material: "chest"
    # Sizes to accommodate each type of ores
    size: 2500
    # Name to be displayed in the GUI
    name: "Extra Mining Sack"
    # Types of ores, max is 28
    ores:
      # material, name (name is optional)
      - [ "coal", false ]
      - [ "raw_iron", false ]
      - [ "raw_gold", false ]
      - [ "raw_copper", false ]
      - [ "redstone_dust", false ]
      - [ "diamond", false ]
      - [ "emerald", false ]

...
```

# Images
<img src="https://github.com/VennDev/VMiningSack/blob/main/images/Untitled.png" alt="VMiningSack" height="300" width="300" />

# Credits
- Email: pnam5005@gmail.com
- Paypal: lifeboat909@gmail.com
