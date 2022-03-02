<?php

return [
    'heroes' => [
        1 => [
            'name' => 'Markus Kruber',
            'careers' => [
                1,
                2,
                3,
                16,
            ],
        ],
        2 => [
            'name' => 'Bardin Goreksson',
            'careers' => [
                4,
                5,
                6,
                17,
            ],
        ],
        3 => [  
            'name' => 'Kerillian',
            'careers' => [
                7,
                8,
                9,
                18,
            ],
        ],
        4 => [  
            'name' => 'Victor Saltzpyre',
            'careers' => [
                10,
                11,
                12,
                19,
            ],
        ],
        5 => [  
            'name' => 'Sienna Fuegonasus',
            'careers' => [
                13,
                14,
                15,
                20,
            ],
        ],
    ],
    'careers' => [
        1 => 'MERCENARY',
        2 => 'HUNTSMAN',
        3 => 'FOOT KNIGHT',
        16 => 'GRAIL KNIGHT',
        4 => 'RANGER VETERAN',
        5 => 'IRONBREAKER',
        6 => 'SLAYER',
        17 => 'OUTCAST ENGINEER',
        7 =>'WAYSTALKER',
        8 => 'HANDMAIDEN',
        9 => 'SHADE',
        18 => 'SISTER OF THE THORN',
        10 => 'WITCH HUNTER CAPTAIN',
        11 => 'BOUNTY HUNTER',
        12 => 'ZEALOT',
        19 => 'WARRIOR PRIEST OF SIGMAR',
        13 => 'BATTLE WIZARD',
        14 => 'PYROMANCER',
        15 => 'UNCHAINED',
        20 => 'UNKNOWN PREMIUM CAREER',
    ],
    'equipment' => [
        // Kruber
        14 => 'Mace',
        15 => 'Sword',
        18 => 'Executioner Sword',
        21 => 'Sword and Shield',
        20 => 'Mace and Shield',
        43 => 'Bretonnian Longsword',
        33 => 'Mace and Sword',
        42 => 'Bretonnian Sword and Shield',
        69 => 'Spear and Shield',
        34 => 'Tuskgor Spear',
        19 => 'Halberd',
        17 => 'Greatsword',
        16 => 'Two-Handed Hammer',
        56 => 'Handgun',
        58 => 'Repeater Handgun',
        55 => 'Blunderbuss',
        57 => 'Longbow',
        // Goreksson
        6  => 'Hammer',
        9  => 'War Pick',
        11 => 'Axe and Shield',
        5  => 'Axe',
        12 => 'Hammer and Shield',
        37 => 'Dual Hammers',
        7  => 'Great Axe',
        44 => 'Cog Hammer',
        8  => 'Great Hammer',
        53 => 'Handgun',
        67 => 'Throwing Axes',
        50 => 'Crossbow',
        68 => 'Masterwork Pistol',
        54 => 'Grudge-Raker',
        70 => 'Trollhammer Torpedo',
        52 => 'Drakegun',
        51 => 'Drakefire Pistols',
        10 => 'Dual Axes',
        // Kerillian
        26 => 'Sword and Dagger',
        25 => 'Dual Daggers',
        23 => 'Glaive',
        22 => 'Sword',
        28 => 'Elven Spear',
        35 => 'Elven Axe',
        27 => 'Dual Swords',
        24 => 'Greatsword', // Two-Handed Sword
        71 => 'Moonfire Bow',
        62 => 'Hagbane Shortbow',
        75 => 'Briar Javelin',
        61 => 'Swift Bow',
        60 => 'Longbow',
        36 => 'Spear and Shield',
        59 => 'Volley Crossbow',
        74 => 'Deepwood Staff',
        // Saltzpyre
        31 => 'Greatsword',
        13 => 'Flail',
        29 => 'Axe',
        39 => 'Bill Hook',
        30 => 'Falchion',
        38 => 'Axe and Falchion',
        32 => 'Rapier',
        64 => 'Crossbow',
        72 => 'Griffon-foot',
        65 => 'Volley Crossbow',
        66 => 'Repeater Pistol',
        63 => 'Brace of Pistols',
        76 => 'Skull-Splitter Hammer',
        77 => 'Holy Great Hammer',
        78 => 'Skull-Splitter & Shield',
        79 => 'Paired Skull-Splitters',
        80 => 'Skull-Splitter & Blessed Tome',
        81 => 'Flail & Shield',
        // Fuegonasus
        40 => 'Crowbill',
        4  => 'Sword',
        1  => 'Mace',
        3  => 'Fire Sword',
        41 => 'Flaming Flail',
        2  => 'Dagger',
        45 => 'Beam Staff',
        47 => 'Flamestorm Staff',
        73 => 'Coruscation Staff',
        46 => 'Fireball Staff',
        49 => 'Bolt Staff',
        48 => 'Conflagration Staff',
    ],
    'heroes_equipment' => [
        // Kruber
        1 => [
            14,
            15,
            18,
            21,
            20,
            43,
            33,
            42,
            69,
            34,
            19,
            17,
            16,
            56,
            58,
            55,
            57,
        ],
        // Goreksson
        2 => [
            6,
            9,
            11,
            5,
            12,
            37,
            7,
            44,
            8,
            53,
            67,
            50,
            68,
            54,
            70,
            52,
            51,
            10,
        ],
        // Kerillian
        3 => [
            26,
            25,
            23,
            22,
            28,
            35,
            27,
            24,
            71,
            62,
            75,
            61,
            60,
            36,
            59,
            74,
        ],
        // Saltzpyre
        4 => [
            31,
            13,
            29,
            39,
            30,
            38,
            32,
            64,
            72,
            65,
            66,
            63,
            76,
            66,
            69,
            77,
            78,
            79,
            80,
            81,
        ],
        // Fuegonasus
        5 => [
            40,
            4,
            1,
            3,
            41,
            2,
            45,
            47,
            73,
            46,
            49,
            48,
        ],
    ],
    'ranged_equipment' => [
        56,
        58,
        55,
        57,
        53,
        67,
        50,
        68,
        54,
        70,
        52,
        51,
        71,
        62,
        75,
        61,
        60,
        59,
        74,
        64,
        72,
        65,
        66,
        63,
        45,
        47,
        73,
        46,
        49,
        48,
    ],
    'properties' => [
        'melee' => [
            1 => 'Attack Speed',
            2 => 'Stamina',
            3 => 'Block Cost Reduction',
            4 => 'Crit Chance',
            5 => 'Crit Power',
            6 => 'Push/Block Angle',
            7 => 'Power vs Skaven',
            8 => 'Power vs Chaos',
        ],
        'ranged' => [
            1 => 'Crit Chance',
            2 => 'Crit Power',
            3 => 'Power vs Skaven',
            4 => 'Power vs Chaos',
            5 => 'Power vs Infantry',
            6 => 'Power vs Armored',
            7 => 'Power vs Berserkers',
            8 => 'Power vs Monsters',
        ],
        'necklace' => [
            1 => 'Stamina',
            2 => 'Block Cost Reduction',
            3 => 'Health',
            4 => 'Push/Block Angle',
            5 => 'Damage reduction vs Skaven',
            6 => 'Damage reduction vs Chaos',
            7 => 'Damage reduction vs Area',
        ],
        'charm' => [
            1 => 'Attack Speed',
            2 => 'Crit Power',
            3 => 'Power vs Skaven',
            4 => 'Power vs Chaos',
            5 => 'Power vs Infantry',
            6 => 'Power vs Armored',
            7 => 'Power vs Berserkers',
            8 => 'Power vs Monsters',
        ],
        'trinket' => [
            1 => 'Cooldown Reduction',
            2 => 'Crit Chance',
            3 => 'Curse Resistance',
            4 => 'Movement Speed',
            5 => 'Respawn Speed',
            6 => 'Revive Speed',
            7 => 'Stamina Recovery Rate',
        ],
    ],
    'traits' => [
        'melee' => [
            1 => 'Heroic Intervention',
            2 => 'Off Balance',
            3 => 'Opportunist',
            4 => 'Parry',
            5 => 'Resourceful Combatant',
            6 => 'Swift Slaying',
        ],
        'ranged' => [
            1 => 'Barrage',
            2 => 'Conservative Shooter / Heat Sink',
            3 => 'Hunter',
            4 => 'Inspirational Shot',
            5 => 'Resourceful Sharpshooter',
            6 => 'Scrounger / Thermal Equalizer'
        ],
        'necklace' => [
            1 => 'Barkskin',
            2 => 'Hand of Shallya',
            3 => 'Healers Touch',
            4 => 'Natural Bond',
            5 => 'Boon of Shallya',
        ],
        'charm' => [
            1 => 'Concoction',
            2 => 'Decanter',
            3 => 'Home Brewer',
            4 => 'Proxy',
        ],
        'trinket' => [
            1 => 'Explosive Ordnance',
            2 => 'Grenadier',
            3 => 'Shrapnel',
        ],
    ],
    'guides' => [
        0 => 'https://steamcommunity.com/sharedfiles/filedetails/?id=2034609078 https://www.ranalds.gift/user/J5DUTj98hhMy13zmAc0AuPDJgaw2/view https://www.ranalds.gift/user/OT58jWXlyZPcOTqoRn19CYFbLEW2/view',
        1 => 'https://steamcommunity.com/sharedfiles/filedetails/?id=1831243904',
        2 => 'https://steamcommunity.com/sharedfiles/filedetails/?id=1832306381',
        3 => 'https://steamcommunity.com/sharedfiles/filedetails/?id=1833337461',
        4 => 'https://steamcommunity.com/sharedfiles/filedetails/?id=1833338040',
        5 => 'https://steamcommunity.com/sharedfiles/filedetails/?id=1833925976',
    ]
];
