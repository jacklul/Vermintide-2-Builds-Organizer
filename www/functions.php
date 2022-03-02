<?php

if (file_exists(dirname(__DIR__) . '/phpdesktop-chrome.exe')) {
	define('DATA_DIR', dirname(__DIR__) . '/data');
} else {
	define('DATA_DIR', __DIR__ . '/data');
}

function writeLog(string $message, array $extra = []): void {
    if (!empty($extra)) {
        $message .= ' ' . json_encode($extra);
    }

    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0777, true);
    }

    file_put_contents(DATA_DIR . '/log.txt', '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n", FILE_APPEND);
}

function validateBuildLink(string $link): bool {
    return preg_match('/ranalds\.gift\/heroes\/\d+\/\d{6}\/\d+-\d-\d-\d\/\d+-\d-\d-\d\/\d-\d-\d\/\d-\d-\d\/\d-\d-\d/', trim($link));
}

function validateLinkedBuildLink(string $link): bool {
    return preg_match('/www.ranalds.gift\/build\/(.*)\/view/', trim($link));
}

function parseBuildLink(string $link): ?array {
    global $gamedata;

    preg_match('/ranalds\.gift\/heroes\/(\d+)\/(\d{6})\/(\d+-\d-\d-\d)\/(\d+-\d-\d-\d)\/(\d-\d-\d)\/(\d-\d-\d)\/(\d-\d-\d)/', trim($link), $matches);

    if (isset($matches[1]) && is_numeric($matches[1])) {
        $primary = explode('-', $matches[3]);
        $secondary = explode('-', $matches[4]);
        $necklace = explode('-', $matches[5]);
        $charm = explode('-', $matches[6]);
        $trinket = explode('-', $matches[7]);

        return [
            'career' => $gamedata['careers'][$matches[1]],
            'career_id' => $matches[1],
            'hero_id' => getHeroId($matches[1]),
            'talents' => $matches[2],
            'equipment' => [
                'primary' => [
                    'id' => $primary[0],
                    'name' => $gamedata['equipment'][$primary[0]],
                    'property1' => !in_array($primary[0], $gamedata['ranged_equipment']) ? $gamedata['properties']['melee'][$primary[1]] : $gamedata['properties']['ranged'][$primary[1]],
                    'property2' => !in_array($primary[0], $gamedata['ranged_equipment']) ? $gamedata['properties']['melee'][$primary[2]] : $gamedata['properties']['ranged'][$primary[2]],
                    'trait' => !in_array($primary[0], $gamedata['ranged_equipment']) ? $gamedata['traits']['melee'][$primary[3]] : $gamedata['traits']['ranged'][$primary[3]],
                ],
                'secondary' => [
                    'id' => $secondary[0],
                    'name' => $gamedata['equipment'][$secondary[0]],
                    'property1' => !in_array($secondary[0], $gamedata['ranged_equipment']) ? $gamedata['properties']['melee'][$secondary[1]] : $gamedata['properties']['ranged'][$secondary[1]],
                    'property2' => !in_array($secondary[0], $gamedata['ranged_equipment']) ? $gamedata['properties']['melee'][$secondary[2]] : $gamedata['properties']['ranged'][$secondary[2]],
                    'trait' => !in_array($secondary[0], $gamedata['ranged_equipment']) ? $gamedata['traits']['melee'][$secondary[3]] : $gamedata['traits']['ranged'][$secondary[3]],
                ],
                'necklace' => [
                    'property1' => $gamedata['properties']['necklace'][$necklace[0]],
                    'property2' => $gamedata['properties']['necklace'][$necklace[1]],
                    'trait' => $gamedata['traits']['necklace'][$necklace[2]],
                ],
                'charm' => [
                    'property1' => $gamedata['properties']['charm'][$charm[0]],
                    'property2' => $gamedata['properties']['charm'][$charm[1]],
                    'trait' => $gamedata['traits']['charm'][$charm[2]],
                ],
                'trinket' => [
                    'property1' => $gamedata['properties']['trinket'][$trinket[0]],
                    'property2' => $gamedata['properties']['trinket'][$trinket[1]],
                    'trait' => $gamedata['traits']['trinket'][$trinket[2]],
                ],
            ],
            'raw' => [
                'career' => $matches[1],
                'talents' => $matches[2],
                'primary' => $matches[3],
                'secondary' => $matches[4],
                'necklace' => $matches[5],
                'charm' => $matches[6],
                'trinket' => $matches[7],
            ],
        ];
    }

    return null;
}

$data_cache = null;

function getData(): array {
    global $data_cache;

    if (!empty($data_cache)) {
        return $data_cache;
    }

    $data = [];
    $default = [
        'builds' => [],
        'equipment' => [
            'weapon' => [],
            'necklace' => [],
            'charm' => [],
            'trinket' => [],
        ]
    ];

    if (file_exists(DATA_DIR . '/data.json')) {
        $data = json_decode(file_get_contents(DATA_DIR . '/data.json'), true);
    }

    $data = array_replace_recursive($default, $data);
    $data_cache = $data;

    return $data;
}

function saveData(array $data): bool {
    global $data_cache;

    if (!is_dir($backupDir = DATA_DIR . '/backups')) {
        mkdir($backupDir, 0777, true);
    } else {
        $backupLife = 7 * 86400;
        $maxBackups = 100;
        $allFiles = [];
        foreach (new DirectoryIterator($backupDir) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            $allFiles[$fileInfo->getRealPath()] = $fileInfo->getMTime();
        }
        
        asort($allFiles);

        $oldFiles = [];
        foreach ($allFiles as $path => $time) {
            if ($time + $backupLife < time()) {
                $oldFiles[$path] = $time;
            }
        }

        $keepFiles = $allFiles;
        array_splice_assoc($keepFiles, 0, count($allFiles) - $maxBackups);

        foreach ($allFiles as $path => $time) {
            if (!isset($keepFiles[$path])) {
                @unlink($path);
            }
        }
    }

    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0777, true);
    }

    file_exists(DATA_DIR . '/data.json') && copy(DATA_DIR . '/data.json', $backupDir . '/data_' . time() . '.json');

    $data_cache = $data;

    return file_put_contents(DATA_DIR . '/data.json', json_encode($data));
}

function normalizeBuildLink(string $link): string {
    $linkstart = explode('heroes/', $link);
    $link = explode('/', $linkstart[1]);
    $linkstart = $linkstart[0] . 'heroes/';

    foreach ($link as &$part) {
        if (substr_count($part, '-') === 3) {
            $tmp = explode('-', $part);
            $part = $tmp[0] . '-' . ($tmp[1] > $tmp[2] ? $tmp[1] . '-' . $tmp[2] : $tmp[2] . '-' . $tmp[1]) . '-' . $tmp[3];
        } elseif (substr_count($part, '-') === 2) {
            $tmp = explode('-', $part);
            $part = ($tmp[0] > $tmp[1] ? $tmp[0] . '-' . $tmp[1] : $tmp[1] . '-' . $tmp[0]) . '-' . $tmp[2];
        }
    }

    return rtrim($linkstart . implode('/', $link), '/');
}

function addBuild(string $link, string $comment = '', string $reference = '', bool $ignore_missing = false): ?bool {
    $build = [
        'link' => normalizeBuildLink(trim($link)),
        'comment' => trim($comment),
        'reference' => trim($reference),
        'ignore_missing' => $ignore_missing,
    ];

    $data = getData();

    if (empty($data['builds'])) {
        $data['builds'] = [];
    }

    foreach ($data['builds'] as $sbuild) {
        $tmp1 = explode('.gift', $sbuild['link']);
        $tmp2 = explode('.gift', normalizeBuildLink($link));

        if ($tmp1[1] === $tmp2[1]) {
            return null;
        }
    }

    $data['builds'][] = $build;

    $tmp1 = explode('.gift', $build['link']);

    writeLog('Added build: ' . $tmp1[1], $build);

    return saveData($data);
}

function removeBuild(string $link): ?bool {
    $data = getData();

    if (empty($data['builds'])) {
        return false;
    }

    foreach ($data['builds'] as $key => $build) {
        $tmp1 = explode('.gift', $build['link']);
        $tmp2 = explode('.gift', $link);

        if ($tmp1[1] === $tmp2[1]) {
            unset($data['builds'][$key]);
            $data['builds'] = array_values($data['builds']);

            writeLog('Removed build: ' . $tmp1[1], $build);

            return saveData($data);
        }
    }

    return null;
}

function editBuild(string $sourcelink, string $link, string $comment = '', string $reference = '', bool $ignore_missing = false): ?bool {
    $data = getData();

    if (empty($data['builds'])) {
        return false;
    }

    foreach ($data['builds'] as $key => $build) {
        $tmp1 = explode('.gift', $build['link']);
        $tmp2 = explode('.gift', $sourcelink);

        if ($tmp1[1] === $tmp2[1]) {
            $data['builds'][$key]['link'] = normalizeBuildLink(trim($link));
            $data['builds'][$key]['comment'] = trim($comment);
            $data['builds'][$key]['reference'] = trim($reference);
            $data['builds'][$key]['ignore_missing'] = $ignore_missing;

            writeLog('Edited build: ' . $tmp1[1], $data['builds'][$key]);

            return saveData($data);
        }
    }

    return null;
}

function highlightMissingEquipment(string $link, bool $ignore_missing = false, ?array $referenced = null): string {
    $data = getData();

    if (empty($data['equipment'])) {
        $data['equipment'] = [
            'weapon' => [],
            'necklace' => [],
            'charm' => [],
            'trinket' => [],
        ];
    }

    $referenced_code = null;
    $referenced_type = null;
    if (!empty($referenced) && isset($referenced['code'], $referenced['type'])) {
        $referenced_code = $referenced['code'];
        $referenced_type = $referenced['type'];
    }

    $link = explode('/', $link);

    $matchC = 0;
    foreach ($link as &$part) {
        $matchC++;
        
        if (substr_count($part, '-') === 3) {
            if ($referenced_code === $part && $referenced_type === 'weapon') {
                $part = '<span class="yellow">' . $part . '</span>';
            } elseif (!in_array($part, $data['equipment']['weapon'])) {
                $part = '<span class="red">' . $part . '</span>';
            } else {
                $part = '<span class="green">' . $part . '</span>';
            }
        } elseif (substr_count($part, '-') === 2) {
            if (
                $referenced_code === $part &&
                (
                    ($referenced_type === 'necklace' && $matchC === 5) ||
                    ($referenced_type === 'charm' && $matchC === 6) ||
                    ($referenced_type === 'trinket' && $matchC === 7)
                )
            ) {
                $part = '<span class="yellow">' . $part . '</span>';
            } elseif (
                ($matchC === 5 && !in_array($part, $data['equipment']['necklace'])) ||
                ($matchC === 6 && !in_array($part, $data['equipment']['charm'])) ||
                ($matchC === 7 && !in_array($part, $data['equipment']['trinket']))
            ) {
                $part = '<span class="red">' . $part . '</span>';
            } else {
                $part = '<span class="green">' . $part . '</span>';
            }
        }
    }

    /*if ($ignore_missing === true) {
        $link = str_replace(['"red"', '"green"'], ['"lightred"', '"lightgreen"'], $link);
    }*/
    if ($ignore_missing === true) {
        $link = str_replace([' class="red"', ' class="green"'], '', $link);
    }

    return implode('/', $link);
}

function renderTalents(string $talents): string {
    $talents = str_split($talents);

    $output = '<table class="talents">';
    foreach ($talents as $talent) {
        if ($talent == 1) {
            $output .= '<tr><td class="filled"></td><td></td><td></td></tr>';
        } elseif ($talent == 2) {
            $output .= '<tr><td></td><td class="filled"></td><td></td></tr>';
        } elseif ($talent == 3) {
            $output .= '<tr><td></td><td></td><td class="filled"></td></tr>';
        }
    }
    $output .= '</table>';

    return $output;
}

function getEquipmentOwner(int $id): ?string {
    global $gamedata;
    
    foreach ($gamedata['heroes_equipment'] as $hero_id => $hero_equipment) {
        foreach ($hero_equipment as $item_id) {
            if ($item_id === $id) {
                return $gamedata['heroes'][$hero_id]['name'];
            }
        }
    }

    return '?';
}

function addEquipment(string $type, string $code): ?bool {
    $data = getData();

    if (empty($data['equipment'])) {
        $data['equipment'] = [
            'weapon' => [],
            'necklace' => [],
            'charm' => [],
            'trinket' => [],
        ];
    }

    if (!in_array($type, ['weapon', 'necklace', 'charm', 'trinket'])) {
        return false;
    }

    $tmp = explode('-', $code);

    if (substr_count($code, '-') === 3) {
        $code = $tmp[0] . '-' . ($tmp[1] > $tmp[2] ? $tmp[1] . '-' . $tmp[2] : $tmp[2] . '-' . $tmp[1]) . '-' . $tmp[3];
    } elseif (substr_count($code, '-') === 2) {
        $code = ($tmp[0] > $tmp[1] ? $tmp[0] . '-' . $tmp[1] : $tmp[1] . '-' . $tmp[0]) . '-' . $tmp[2];
    }

    if (in_array($code, $data['equipment'][$type])) {
        return null;
    }

    $data['equipment'][$type][] = $code;

    writeLog('Added equipment: ' . $code);

    return saveData($data);
}

function removeEquipment(string $type, string $code): ?bool {
    $data = getData();

    if (empty($data['equipment'])) {
        return false;
    }

    if (!in_array($type, ['weapon', 'necklace', 'charm', 'trinket'])) {
        return false;
    }

    foreach ($data['equipment'][$type] as $key => $ecode) {
        if ($ecode == $code) {
            unset($data['equipment'][$type][$key]);
            $data['equipment'][$type] = array_values($data['equipment'][$type]);

            writeLog('Removed equipment: ' . $code);

            return saveData($data);
        }
    }

    return null;
}

function moveElement(&$array, $a, $b) {
    $out = array_splice($array, $a, 1);
    array_splice($array, $b, 0, $out);
}

function moveBuild(string $link, int $id): bool {
    $data = getData();

    if (empty($data['builds'])) {
        return false;
    }

    foreach ($data['builds'] as $key => $build) {
        if ($build['link'] === $link) {
            moveElement($data['builds'], $key, $id);

            writeLog('Moved build: ' . $link . ' to ' . $id);

            return saveData($data);
        }
    }

    return null;
}

function saveNotes(string $notes): bool {
    $data = getData();

    if (empty($data['notes'])) {
        $data['notes'] = '';
    }

    $data['notes'] = $notes;

    writeLog('Saved notes');

    return saveData($data);
}

function normalizeTrait(string $trait, int $item_id): string {
    if (strpos($trait, ' / ') === false) {
        return $trait;
    }

    $trait = explode(' / ', $trait);

    if (getEquipmentOwner($item_id) === 'Sienna Fuegonasus') {
        return $trait[1];
    }

    return $trait[0];
}

function getHeroId(int $career_id):? int {
    global $gamedata;

    foreach ($gamedata['heroes'] as $id => $hero) {
        if (in_array($career_id, $hero['careers'])) {
            return $id;
        }
    }

    return null;
}

function findPropertyId(string $property, string $type = null): ?int {
    global $gamedata;

    if ($type !== null) {
        foreach ($gamedata['properties'][$type] as $id => $name) {
            if (strpos($property, $name) !== false) {
                return $id;
            }
        }
    } else {
        foreach ($gamedata['properties'] as $type => $properties) {
            foreach ($properties as $id => $name) {
                if (strpos($property, $name) !== false) {
                    return $id;
                }
            }
        }
    }

    return null;
}

function createLinkForBuild(array $build): string {
    $link = 'https://www.ranalds.gift/heroes/' . $build['career_id'] . '/' . $build['talents'] . '/';
    $link .= $build['equipment']['primary']['id'] . '-' . $build['equipment']['primary']['property1'] . '-' . $build['equipment']['primary']['property2'] . '-' . $build['equipment']['primary']['trait'] . '/';
    $link .= $build['equipment']['secondary']['id'] . '-' . $build['equipment']['secondary']['property1'] . '-' . $build['equipment']['secondary']['property2'] . '-' . $build['equipment']['secondary']['trait'] . '/';
    $link .= $build['equipment']['necklace']['property1'] . '-' . $build['equipment']['necklace']['property2'] . '-' . $build['equipment']['necklace']['trait'] . '/';
    $link .= $build['equipment']['charm']['property1'] . '-' . $build['equipment']['charm']['property2'] . '-' . $build['equipment']['charm']['trait'] . '/';
    $link .= $build['equipment']['trinket']['property1'] . '-' . $build['equipment']['trinket']['property2'] . '-' . $build['equipment']['trinket']['trait'] . '/';

    return normalizeBuildLink($link);
}

function getRemoteBuild(string $link): ?array {
    $nodePath = 'node';
    if (file_exists($localNode = __DIR__ . '/../node/node.exe')) {
        $nodePath = '"' . realpath($localNode) . '"';
    }

    $chromePath = 'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe';
    if (file_exists($puppeteerPkgJson = __DIR__ . '/../puppeteer/package.json')) {
        $json = json_decode(file_get_contents($puppeteerPkgJson), true);

        if (!isset($json['dependencies']['puppeteer-core'])) {
            $chromePath = '';
        }
    }

    if (file_exists($chromePath)) {
        $output = shell_exec($nodePath . ' "' . __DIR__ . '/../puppeteer/load_page_external.js" "' . trim($link) . '" "' . $chromePath . '"');
    } elseif ($chromePath === '') {
        $output = shell_exec($nodePath . ' "' . __DIR__ . '/../puppeteer/load_page.js" "' . trim($link) . '"');
    } else {
        writeLog('Unable to locate Chrome executable (tried path "' . $chromePath . '")');
        return null;
    }

    if (empty($output)) {
        return null;
    }

    return parseBuildHtml($output);
}

function parseBuildHtml(string $data): ?array {
    global $gamedata;

    $build_data = [
        'career_id' => 0,
        'talents' => '000000',
        'comment' => '',
        'equipment' => [
            'primary' => [
                'id' => 0,
                'property1' => 0,
                'property2' => 0,
                'trait' => 0,
            ],
            'secondary' => [
                'id' => 0,
                'property1' => 0,
                'property2' => 0,
                'trait' => 0,
            ],
            'necklace' => [
                'property1' => 0,
                'property2' => 0,
                'trait' => 0,
            ],
            'charm' => [
                'property1' => 0,
                'property2' => 0,
                'trait' => 0,
            ],
            'trinket' => [
                'property1' => 0,
                'property2' => 0,
                'trait' => 0,
            ],
        ]
    ];
    
    preg_match('/<span class="build-header.*">(.*)<\/span>/U', $data, $matches);
    if (isset($matches[1])) {
        $build_data['comment'] = $matches[1];
    } else {
        writeLog('Failed to parse "build-header" section');
        return null;
    }
    
    preg_match('/<div class="build-main-container">.*<div class="hero-talent-summary" data-career="(\d+)" data-talent1="(\d)" data-talent2="(\d)" data-talent3="(\d)" data-talent4="(\d)" data-talent5="(\d)" data-talent6="(\d)">/U', $data, $matches);
    if (isset($matches[1])) {
        $build_data['career_id'] = $matches[1];
        $build_data['talents'] = $matches[2] . $matches[3] . $matches[4] . $matches[5] . $matches[6] . $matches[7];
    } else {
        writeLog('Failed to parse "hero-talent-summary" section');
        return null;
    }
    
    preg_match('/<div class="build-main-container">.*<div class="weapon-icon.*" data-id="(\d+)" data-slot="primary">.*<div class="trait-icon.*" data-type=".*" data-id="(\d)">/U', $data, $matches);
    if (isset($matches[1])) {
        $build_data['equipment']['primary']['id'] = $matches[1];
        $build_data['equipment']['primary']['trait'] = $matches[2];
    } else {
        writeLog('Failed to parse "primary weapon" section');
        return null;
    }
    
    preg_match('/<div class="build-main-container">.*<div class="build-melee-summary">.*<li class="item-property-1">(.*)<\/li>.*<li class="item-property-2">(.*)<\/li>/U', $data, $matches);
    if (isset($matches[1])) {
        $build_data['equipment']['primary']['property1'] = findPropertyId($matches[1], 'melee');
        $build_data['equipment']['primary']['property2'] = findPropertyId($matches[2], 'melee');
    } else {
        writeLog('Failed to parse "build-melee-summary" section');
        return null;
    }
    
    preg_match('/<div class="build-main-container">.*<div class="weapon-icon.*" data-id="(\d+)" data-slot="secondary">.*<div class="trait-icon.*" data-type=".*" data-id="(\d)">/U', $data, $matches);
    if (isset($matches[1])) {
        $build_data['equipment']['secondary']['id'] = $matches[1];
        $build_data['equipment']['secondary']['trait'] = $matches[2];
    } else {
        writeLog('Failed to parse "secondary weapon" section');
        return null;
    }
    
    preg_match('/<div class="build-main-container">.*<div class="build-range-summary">.*<li class="item-property-1">(.*)<\/li>.*<li class="item-property-2">(.*)<\/li>/U', $data, $matches);
    if (isset($matches[1])) {
        $is_ranged = false;
        if (in_array($build_data['equipment']['secondary']['id'], $gamedata['ranged_equipment'])) {
            $is_ranged = true;
        }
    
        $build_data['equipment']['secondary']['property1'] = findPropertyId($matches[1], $is_ranged ? 'ranged' : 'melee');
        $build_data['equipment']['secondary']['property2'] = findPropertyId($matches[2], $is_ranged ? 'ranged' : 'melee');
    } else {
        writeLog('Failed to parse "build-range-summary" section');
        return null;
    }
    
    preg_match('/<div class="build-main-container">.*<div class="trait-icon.*" data-type="defence_accessory" data-id="(\d)">/U', $data, $matches);
    if (isset($matches[1])) {
        $build_data['equipment']['necklace']['trait'] = $matches[1];
    } else {
        writeLog('Failed to parse "defence_accessory" section');
        return null;
    }
    
    preg_match('/<div class="build-main-container">.*<div class="build-jewelry-summary necklace-summary">.*<li class="item-property-1">(.*)<\/li>.*<li class="item-property-2">(.*)<\/li>/U', $data, $matches);
    if (isset($matches[1])) {
        $build_data['equipment']['necklace']['property1'] = findPropertyId($matches[1], 'necklace');
        $build_data['equipment']['necklace']['property2'] = findPropertyId($matches[2], 'necklace');
    } else {
        writeLog('Failed to parse "necklace-summary" section');
        return null;
    }
    
    preg_match('/<div class="build-main-container">.*<div class="trait-icon.*" data-type="offence_accessory" data-id="(\d)">/U', $data, $matches);
    if (isset($matches[1])) {
        $build_data['equipment']['charm']['trait'] = $matches[1];
    } else {
        writeLog('Failed to parse "offence_accessory" section');
        return null;
    }
    
    preg_match('/<div class="build-main-container">.*<div class="build-jewelry-summary charm-summary">.*<li class="item-property-1">(.*)<\/li>.*<li class="item-property-2">(.*)<\/li>/U', $data, $matches);
    if (isset($matches[1])) {
        $build_data['equipment']['charm']['property1'] = findPropertyId($matches[1], 'charm');
        $build_data['equipment']['charm']['property2'] = findPropertyId($matches[2], 'charm');
    } else {
        writeLog('Failed to parse "charm-summary" section');
        return null;
    }
    
    preg_match('/<div class="build-main-container">.*<div class="trait-icon.*" data-type="utility_accessory" data-id="(\d)">/U', $data, $matches);
    if (isset($matches[1])) {
        $build_data['equipment']['trinket']['trait'] = $matches[1];
    } else {
        writeLog('Failed to parse "utility_accessory" section');
        return null;
    }
    
    preg_match('/<div class="build-main-container">.*<div class="build-jewelry-summary trinket-summary">.*<li class="item-property-1">(.*)<\/li>.*<li class="item-property-2">(.*)<\/li>/U', $data, $matches);
    if (isset($matches[1])) {
        $build_data['equipment']['trinket']['property1'] = findPropertyId($matches[1], 'trinket');
        $build_data['equipment']['trinket']['property2'] = findPropertyId($matches[2], 'trinket');
    } else {
        writeLog('Failed to parse "trinket-summary" section');
        return null;
    }

    return $build_data;
}

function findBuildByLink(string $link): ?array {
    $data = getData();

    if (empty($data['builds'])) {
        return false;
    }

    foreach ($data['builds'] as $build) {
        if ($build['link'] === $link && !empty($build['reference'])) {
            return $build;
        }
    }

    return null;
}

function syncBuild(string $link): ?bool {
    $data = getData();

    if (empty($data['builds'])) {
        return false;
    }

    $build = findBuildByLink($link);

    if ($build !== null) {
        $newBuild = getRemoteBuild($build['reference']);
        $newLink = createLinkForBuild($newBuild);

        writeLog('Synced build: ' . $link);

        return editBuild($build['link'], $newLink, $build['comment'], $build['reference'], $build['ignore_missing']);
    }

    return null;
}

function updateBuildLink(string $link, string $newLink): ?bool {
    $data = getData();

    if (empty($data['builds'])) {
        return false;
    }

    $build = findBuildByLink($link);

    if ($build !== null) {
        writeLog('Updated build: ' . $link);

        return editBuild($build['link'], $newLink, $build['comment'], $build['reference'], $build['ignore_missing']);
    }

    return null;
}

function renderBuildDetails(array $build, bool $visible = false, bool $show_missing = true, string $cssClass = 'build-details', ?array $compareBuild = null): string {
    global $gamedata;

    $data = getData();

    $output = '<div class="' . $cssClass . '"' . ($visible === false ? ' style="display:none;"' : '') . '><table class="build-table"><thead><td>Talents</td><td>Primary</td><td>Secondary</td><td>Necklace</td><td>Charm</td><td>Trinket</td></thead><tbody><tr>';
    
    $difference = '';
    if ($compareBuild !== null && $build['talents'] !== $compareBuild['talents']) {
        $difference = '<span class="missing">Difference!</span></td>';
    }

    $output .= '<td>' . renderTalents($build['talents']) . '' . $difference . '</td>';

    foreach ($build['equipment'] as $type => $item) {
        $tmp = '';
        if (!empty($item['name'])) {
            $tmp .= '<b>' . $item['name'] . '</b><br>';
        }
        
        $tmp .= $item['property1'] . '<br>';
        $tmp .= $item['property2'] . '<br>';

        if (isset($item['id'])) {
            $tmp .= normalizeTrait($item['trait'], $item['id']) . '<br>';
        } else {
            $tmp .= $item['trait'] . '<br>';
        }

        if ($show_missing) {
            if (
                in_array($type, ['primary', 'secondary']) && !in_array($build['raw'][$type], $data['equipment']['weapon']) ||
                (isset($data['equipment'][$type]) && !in_array($build['raw'][$type], $data['equipment'][$type])))
            {
                if (in_array($type, ['primary', 'secondary'])) {
                    $atype = (in_array($item['id'], $gamedata['ranged_equipment']) ? 'ranged' : 'melee') . '_weapon';
                } else {
                    $atype = $type;
                }

                $code = '/' . $build['raw'][$type];
                $tmp .= '<span class="missing">Missing!</span> <a href="?a=add_equipment&t=' . $atype . '&code=' . $code . '"><i class="fa fa-plus"></i></a>';
            }
        }

        if ($compareBuild !== null) {
            if (
                (empty($item['name']) || $compareBuild['equipment'][$type]['id'] !== $item['id']) &&
                $compareBuild['equipment'][$type]['property1'] != $item['property1'] ||
                $compareBuild['equipment'][$type]['property2'] != $item['property2'] ||
                $compareBuild['equipment'][$type]['trait'] != $item['trait']
            ) {
                $tmp .= '<span class="missing">Difference!</span>';
            }
        }

        $output .= '<td>' . $tmp . '</td>';
    }

    $output .= '</tr></tbody></table></div>';

    return $output;
}

function array_splice_assoc(&$input, $offset, $length, $replacement = array()) {
    $replacement = (array) $replacement;
    $key_indices = array_flip(array_keys($input));
    if (isset($input[$offset]) && is_string($offset)) {
            $offset = $key_indices[$offset];
    }
    if (isset($input[$length]) && is_string($length)) {
            $length = $key_indices[$length] - $offset;
    }

    $input = array_slice($input, 0, $offset, TRUE)
            + $replacement
            + array_slice($input, $offset + $length, NULL, TRUE); 
}