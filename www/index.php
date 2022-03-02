<?php
    $gamedata = require_once __DIR__ . '/gamedata.php';
    require_once __DIR__ . '/functions.php';
    error_reporting(E_ALL);
    ini_set('display_errors', true);
?>
<!DOCTYPE html>
<html lang="en">
<meta charset="UTF-8">
<title><?php echo $title = 'Vermintide 2 Builds Organizer' ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/css/dark-theme.css" />
<link rel="stylesheet" href="/css/font-awesome.min.css" />
<link rel="stylesheet" href="/css/style.css" />
<script src="/js/jquery.min.js"></script>
<script>
    $(document).ready(function () {
        $(".build a").click(function (e) {
            e.stopPropagation();
        });
        $(".build .build-details").click(function (e) {
            e.stopPropagation();
        });
        $(".item a").click(function (e) {
            e.stopPropagation();
        });
    });

    function toggleDetails(id, event) {
        event.preventDefault();
        $('#' + id + ' .build-details').toggle();
    }

</script>
<body>
<h1><a href="/" class="header"><?=$title?></a></h1>
<div id="nav">
<a href="#" onclick="return history.back();"><i class="fa fa-arrow-left"></i></a> <a href="#" onclick="return history.forward();"><i class="fa fa-arrow-right"></i></a> &nbsp; 
<a href="?p=builds">Builds</a> <span class="add"><a href="?a=add_build"><i class="fa fa-plus"></i></a></span> &nbsp;
<a href="?p=equipment">Equipment</a> <span class="add"><a href="?a=add_equipment"><i class="fa fa-plus"></i></a></span> &nbsp;
<a href="?p=notes">Notes</a> &nbsp;
<a href="?p=equipment_missing">Missing Equipment</a> &nbsp;
</div>
<hr style="margin:20px 0;"></hr>
<?php

switch ($_GET['a'] ?? '') {
    case 'add_build':
    case 'edit_build':
    case 'copy_build':
        if ($_GET['a'] === 'edit_build' || $_GET['a'] === 'copy_build') {
            $link = urldecode($_GET['b']);
            $data = getData();

            foreach ($data['builds'] as $key => $build) {
                $tmp1 = explode('.gift', $build['link']);
                $tmp2 = explode('.gift', $link);

                if ($tmp1[1] === $tmp2[1]) {
                    $_POST['sourcelink'] = $build['link'];

                    if (!isset($_POST['link'])) {
                        $_POST['link'] = $build['link'];
                        $_POST['comment'] = $build['comment'];
                        $_POST['reference'] = $build['reference'];
                        isset($build['ignore_missing']) && $_POST['ignore_missing'] = $build['ignore_missing'] === true ? 'on' : '';
                    }

                    break;
                }
            }

            if (empty($_POST)) {
                echo '<p class="red">Error: This build does not exists.</p>';
                exit;
            }
        }

        if (
            !empty($_POST['link']) &&
            (isset($_POST['add_and_go_back']) || isset($_POST['add_and_stay']) || isset($_POST['save_and_go_back']))
        ) {
            if (validateLinkedBuildLink($_POST['link']) && !validateBuildLink($_POST['link'])) {
                $reference = $_POST['link'];
                $build = getRemoteBuild($reference);

                if (!empty($build)) {
                    $_POST['link'] = createLinkForBuild($build);
                    print_r($_POST['link']);

                    $_POST['reference'] = $reference;

                    if (empty($_POST['comment']) && !empty($build['comment'])) {
                        $_POST['comment'] = $build['comment'];
                    }
                } else {
                    $_POST['link'] = '';
                    echo '<p class="red">Error: Failed to import this build.</p>';
                }
            }

            if (!empty($_POST['link']) && !validateBuildLink($_POST['link'])) {
                echo '<p class="red">Error: Invalid build link.</p>';
            }

            if (
                filter_var($_POST['link'], FILTER_VALIDATE_URL) && 
                validateBuildLink($_POST['link']) &&
                (empty($_POST['reference']) || filter_var($_POST['reference'], FILTER_VALIDATE_URL))
            ) {
                if ($_GET['a'] === 'edit_build') {
                    $result = editBuild(
                        $_POST['sourcelink'],
                        $_POST['link'],
                        $_POST['comment'],
                        $_POST['reference'],
                        isset($_POST['ignore_missing'])
                    );
                } else {
                    $result = addBuild(
                        $_POST['link'],
                        $_POST['comment'],
                        $_POST['reference'],
                        isset($_POST['ignore_missing'])
                    );
                }

                if ($result) {
                    if (isset($_POST['add_and_go_back']) || isset($_POST['save_and_go_back'])) {
                        //header('location:/?p=builds#' . md5(trim(explode('heroes/', $_POST['link'])[1])));
                        exit('<script> window.location.href = "/?p=builds#' . md5(trim(explode('heroes/', normalizeBuildLink($_POST['link']))[1])) . '";</script>');
                    } else {
                        echo '<p class="green">Build added successfully.</p>';

                        foreach ($_POST as $key => $value) {
                            $_POST[$key] = '';
                        }
                    }
                } elseif ($result === null) {
                    if ($_GET['a'] === 'edit_build') {
                        echo '<p class="red">Error: This build does not exists.</p>';
                    } else {
                        echo '<p class="red">Error: This build already exists.</p>';
                    }
                } else {
                    echo '<p class="red">Error: Could not save build.</p>';
                }
            }
        }

        if ($_GET['a'] === 'edit_build') {
            echo '<b>Editing build: <a href="' . $_POST['sourcelink'] . '" target="_blank">' . $_POST['sourcelink'] . '</a></b><br><br>';
        } elseif ($_GET['a'] === 'copy_build') {
            echo '<b>Copying build: <a href="' . $_POST['sourcelink'] . '" target="_blank">' . $_POST['sourcelink'] . '</a></b><br><br>';
        } else {
            echo '<b>Adding Build</b><br><br>';
        }

        echo '<form method="POST" autocomplete="off">
    <label for="link">Build link:</label><br>
    <input type="text" id="link" name="link" placeholder="https://www.ranalds.gift/heroes/1/212122/18-1-3-6/56-4-5-2/3-2-1/5-3-2/3-2-3" size="65" value="' . ($_POST['link'] ?? '') . '" required><br>
    <label for="link">Comment:</label><br>
    <input type="text" id="comment" name="comment" placeholder="" size="65" value="' . ($_POST['comment'] ?? '') . '"><br>
    <label for="reference">Reference build link:</label><br>
    <input type="text" id="reference" name="reference" placeholder="https://www.ranalds.gift/build/iqCQLxPqUmDLVkaJVP9I/view" size="65" value="' . ($_POST['reference'] ?? '') . '"><br>
    <br>
    <input type="checkbox" id="ignore_missing" name="ignore_missing"' . (isset($_POST['ignore_missing']) && $_POST['ignore_missing'] === 'on' ? ' checked': '') . '>
    <label for="ignore_missing">Ignore missing items from this build on "Missing Equipment" page</label><br>
    <br>';

        if ($_GET['a'] === 'edit_build') {
            echo '<input type="submit" value="Save and go back" name="save_and_go_back">';
        } else {
            echo '<input type="submit" value="Add and go back" name="add_and_go_back"><input type="submit" value="Add and stay" name="add_and_stay">';
        }

        echo '</form>';
        
        if ($_GET['a'] !== 'edit_build') {
            echo '<p>TIP: You can also provide reference build as the build link to automatically convert and import it.</p>';
        }

        break;
    case 'add_equipment':
        if (
            (!empty($_POST['id']) || !empty($_POST['code'])) &&
            (isset($_POST['add_and_go_back']) || isset($_POST['add_and_stay']))
        ) {
            if (!empty($_POST['id'])) {
                if ($_POST['id'] == -1) {
                    $type = 'necklace';
                } elseif ($_POST['id'] == -2) {
                    $type = 'charm';
                } elseif ($_POST['id'] == -3) {
                    $type = 'trinket';
                } else {
                    $type = 'weapon';
                }
            }

            if (!empty($_POST['id']) && empty($_POST['code'])) {
                if ($_POST['id'] < 0) {
                    $code = $_POST['property1'] . '-' . $_POST['property2'] . '-' . $_POST['trait'];
                } else {
                    $code = $_POST['id'] . '-' . $_POST['property1'] . '-' . $_POST['property2'] . '-' . $_POST['trait'];
                }
            } elseif (!empty($_POST['code'])) {
                $code = trim($_POST['code'], '/');
            }

            $result = addEquipment($type, $code);

            if ($result) {
                if (isset($_POST['add_and_go_back'])) {
                    //header('location:/?p=equipment');
                    exit('<script> window.location.href = "/?p=equipment";</script>');
                } else {
                    echo '<p class="green">Equipment item added successfully.</p>';

                    foreach ($_POST as $key => $value) {
                        $_POST[$key] = '';
                    }
                }
            } elseif ($result === null) {
                echo '<p class="red">Error: This equipment item already exists.</p>';
            } else {
                echo '<p class="red">Error: Could not save equipment.</p>';
            }
        }

        if (isset($_GET['code'])) {
            $_POST['code'] = $_GET['code'];
            $code = explode('-', trim($_GET['code'], '/'));

            if (count($code) === 4) {
                $_POST['id'] = $code[0];
                $_POST['property1'] = $code[1];
                $_POST['property2'] = $code[2];
                $_POST['trait'] = $code[3];
            } else {
                $_POST['property1'] = $code[0];
                $_POST['property2'] = $code[1];
                $_POST['trait'] = $code[2];
            }
        }

        switch ($_GET['t'] ?? '') {
            case 'melee_weapon':
                echo '<form method="POST" autocomplete="off">
                <b>Adding Melee Weapon</b><br><br>
                <label for="id"><b>Weapon</b>:</label><br>
                <select id="id" name="id">
                <option value="" disabled selected>-</option>';
                foreach ($gamedata['equipment'] as $id => $name) {
                    if (in_array($id, $gamedata['ranged_equipment'])) {
                        continue;
                    }

                    echo '<option value="' . $id . '"' . ($_POST['id'] == $id ? ' selected' : '') . '>' . $name . ' (' . getEquipmentOwner($id) . ')</option>';
                }
                echo '</select><br>';
                
                echo '<label for="name">Property 1:</label><br>
                <select id="property1" name="property1">
                <option value="" disabled selected>-</option>';
                foreach ($gamedata['properties']['melee'] as $id => $name) {
                    echo '<option value="' . $id . '"' . ($_POST['property1'] == $id ? ' selected' : '') . '>' . $name . '</option>';
                }
                echo '</select><br>';
                
                echo '<label for="name">Property 2:</label><br>
                <select id="property2" name="property2">
                <option value="" disabled selected>-</option>';
                foreach ($gamedata['properties']['melee'] as $id => $name) {
                    echo '<option value="' . $id . '"' . ($_POST['property2'] == $id ? ' selected' : '') . '>' . $name . '</option>';
                }
                echo '</select><br>';
                
                echo '<label for="name">Trait:</label><br>
                <select id="trait" name="trait" required>
                <option value="" disabled selected>-</option>';
                foreach ($gamedata['traits']['melee'] as $id => $name) {
                    echo '<option value="' . $id . '"' . ($_POST['trait'] == $id ? ' selected' : '') . '>' . $name . '</option>';
                }
                echo '</select><br>';

                echo '<br><label for="link">OR enter item code:</label><br>
                <input type="text" id="code" name="code" placeholder="/18-3-1-6" size="9" maxlength="9" value="' . ($_POST['code'] ?? '') . '"><br><br>';

                echo '<input type="submit" value="Add and go back" name="add_and_go_back"><input type="submit" value="Add and stay" name="add_and_stay">';
                echo '</form>';
                break;
            case 'ranged_weapon':
                echo '<form method="POST" autocomplete="off">
                <b>Adding Ranged Weapon</b><br><br>
                <label for="id"><b>Weapon</b>:</label><br>
                <select id="id" name="id">
                <option value="" disabled selected>-</option>';
                foreach ($gamedata['equipment'] as $id => $name) {
                    if (!in_array($id, $gamedata['ranged_equipment'])) {
                        continue;
                    }
                    
                    echo '<option value="' . $id . '"' . ($_POST['id'] == $id ? ' selected' : '') . '>' . $name . ' (' . getEquipmentOwner($id) . ')</option>';
                }
                echo '</select><br>';
                
                echo '<label for="name">Property 1:</label><br>
                <select id="property1" name="property1">
                <option value="" disabled selected>-</option>';
                foreach ($gamedata['properties']['ranged'] as $id => $name) {
                    echo '<option value="' . $id . '"' . ($_POST['property1'] == $id ? ' selected' : '') . '>' . $name . '</option>';
                }
                echo '</select><br>';
                
                echo '<label for="name">Property 2:</label><br>
                <select id="property2" name="property2">
                <option value="" disabled selected>-</option>';
                foreach ($gamedata['properties']['ranged'] as $id => $name) {
                    echo '<option value="' . $id . '"' . ($_POST['property2'] == $id ? ' selected' : '') . '>' . $name . '</option>';
                }
                echo '</select><br>';
                
                echo '<label for="name">Trait:</label><br>
                <select id="trait" name="trait">
                <option value="" disabled selected>-</option>';
                foreach ($gamedata['traits']['ranged'] as $id => $name) {
                    echo '<option value="' . $id . '"' . ($_POST['trait'] == $id ? ' selected' : '') . '>' . $name . '</option>';
                }
                echo '</select><br>';

                echo '<br><label for="link">OR enter item code:</label><br>
                <input type="text" id="code" name="code" placeholder="/18-3-1-6" size="9" maxlength="9" value="' . ($_POST['code'] ?? '') . '"><br><br>';

                echo '<input type="submit" value="Add and go back" name="add_and_go_back"><input type="submit" value="Add and stay" name="add_and_stay">';
                echo '</form>';
                break;
            case 'necklace':
                empty($type) && $type = 'necklace';
            case 'charm':
                empty($type) && $type = 'charm';
            case 'trinket':
                empty($type) && $type = 'trinket';
                
                echo '<form method="POST" autocomplete="off">
                <b>Adding ' . ucfirst($type) . '</b><input type="hidden" id="id" name="id" value="-' . ($type === 'necklace' ? '1' : ($type === 'charm' ? '2' : '3')) . '"><br><br>';
                
                echo '<label for="name">Property 1:</label><br>
                <select id="property1" name="property1">
                <option value="" disabled selected>-</option>';
                foreach ($gamedata['properties'][$type] as $id => $name) {
                    echo '<option value="' . $id . '"' . ($_POST['property1'] == $id ? ' selected' : '') . '>' . $name . '</option>';
                }
                echo '</select><br>';
                
                echo '<label for="name">Property 2:</label><br>
                <select id="property2" name="property2">
                <option value="" disabled selected>-</option>';
                foreach ($gamedata['properties'][$type] as $id => $name) {
                    echo '<option value="' . $id . '"' . ($_POST['property2'] == $id ? ' selected' : '') . '>' . $name . '</option>';
                }
                echo '</select><br>';
                
                echo '<label for="name">Trait:</label><br>
                <select id="trait" name="trait">
                <option value="" disabled selected>-</option>';
                foreach ($gamedata['traits'][$type] as $id => $name) {
                    echo '<option value="' . $id . '"' . ($_POST['trait'] == $id ? ' selected' : '') . '>' . $name . '</option>';
                }
                echo '</select><br>';

                echo '<br><label for="link">OR enter item code:</label><br>
                <input type="text" id="code" name="code" placeholder="/3-1-6" size="6" maxlength="6" value="' . ($_POST['code'] ?? '') . '"><br><br>';

                echo '<input type="submit" value="Add and go back" name="add_and_go_back"><input type="submit" value="Add and stay" name="add_and_stay">';
                echo '</form>';
                break;
            default:
                echo '<p>Add: <a href="/?a=add_equipment&t=melee_weapon">Melee Weapon</a>, <a href="/?a=add_equipment&t=ranged_weapon">Ranged Weapon</a>, <a href="/?a=add_equipment&t=necklace">Necklace</a>, <a href="/?a=add_equipment&t=charm">Charm</a>, <a href="/?a=add_equipment&t=trinket">Trinket</a></p>';
        }

        break;
    case 'move_build':
        if ($_GET['a'] === 'move_build') {
            $link = urldecode($_GET['b']);
            $id = $_GET['id'];

            $result = moveBuild($link, $id);

            if ($result) {
                echo '<p class="green">Build moved successfully.</p>';
            } elseif ($result === null) {
                echo '<p class="red">Error: This build does not exists.</p>';
            } else {
                echo '<p class="red">Error: Could not save build.</p>';
            }
        }
    case 'sync_build':
        if ($_GET['a'] === 'sync_build') {
            $link = urldecode($_GET['b']);

            if (!empty($_POST['new_link']) && isset($_POST['confirm'])) {
                $result = updateBuildLink($link, $_POST['new_link']);

                if ($result) {
                    echo '<p class="green">Build synchronized successfully.</p>';
                } elseif ($result === null) {
                    echo '<p class="red">Error: This build does not exists.</p>';
                } else {
                    echo '<p class="red">Error: Could not save build.</p>';
                }
            } else {
                echo '<h2>BUILD SYNCHRONIZATION</h2>';
    
                $build = findBuildByLink($link);
    
                if ($build !== null && !empty($build['reference'])) {
                    $oldBuild = parseBuildLink($build['link']);
    
                    echo '<h3>CURRENT BUILD: &nbsp; <a href="' . $build['link'] . '" target="_blank"><i class="fa fa-external-link"></i></a></h3>';
                    echo renderBuildDetails($oldBuild, true, false, 'build-details-sync');
    
                    $newBuild = getRemoteBuild($build['reference']);

                    if (!empty($newBuild)) {
                        $newLink = createLinkForBuild($newBuild);
                        $newBuildParsed = parseBuildLink($newLink);
            
                        echo '<h3>REMOTE BUILD: &nbsp; <a href="' . $build['reference'] . '" target="_blank"><i class="fa fa-external-link"></i></a></h3>';
                        echo renderBuildDetails($newBuildParsed, true, false, 'build-details-sync', $oldBuild);
        
                        echo '<br><form method="POST" autocomplete="off">';
                        echo '<input type="hidden" id="new_link" name="new_link" value="' . $newLink . '">';
                        echo '<input type="submit" value="Confirm synchronization" name="confirm"><input type="button" value="Cancel and go back" onclick="return window.location.href = \'/?p=builds\'">';
                        echo '</form>';
                    } else {
                        echo '<p class="red">Error: Failed to fetch remote build data.</p>';
                    }
    
                    break;
                } else {
                    echo '<p class="red">Error: This build does not exists or is not synchronizable.</p>';
                }
            }
        }
    case 'remove_build':
        if ($_GET['a'] === 'remove_build') {
            $link = urldecode($_GET['b']);
            $result = removeBuild($link);

            if ($result) {
                echo '<p class="green">Build removed successfully.</p>';
            } elseif ($result === null) {
                echo '<p class="yellow">Warning: Build not found.</p>';
            } else {
                echo '<p class="red">Error: Could not remove build.</p>';
            }
        }
    case 'remove_equipment':
        if ($_GET['a'] === 'remove_equipment') {
            $type = strtolower($_GET['t']);
            $code = $_GET['c'];
            $result = removeEquipment($type, $code);

            if ($result) {
                echo '<p class="green">Equipment item removed successfully.</p>';
            } elseif ($result === null) {
                echo '<p class="yellow">Warning: Equipment item not found.</p>';
            } else {
                echo '<p class="red">Error: Could not remove equipment item.</p>';
            }
        }
    default:
        $data = getData();

        switch ($_GET['p'] ?? '') {
            case 'notes':
                echo '<h2>NOTES</h2>';

                if (isset($_POST['notes'])) {
                    $data['notes'] = $_POST['notes'];

                    $result = saveNotes($_POST['notes']);

                    if ($result) {
                        echo '<p class="green">Notes saved successfully.</p>';
                    } else {
                        echo '<p class="red">Error: Could not save notes.</p>';
                    }
                }

                echo '<form method="POST" autocomplete="off">';
                echo '<div class="wrapper"><textarea cols="30" rows="15" name="notes">' . ($data['notes'] ?? '') . '</textarea></div>';
                echo '<input type="submit" value="Save" name="save">';
                echo '</form>';

                break;
            case 'equipment_reference':
                echo '<h2>EQUIPMENT REFERENCE</h2>';

                $type = strtolower($_GET['t']);
                $code = $_GET['c'];
    
                $matches = [];
                if (!empty($data['builds'])) {
                    foreach ($data['builds'] as $i => $build) {
                        $parsed = parseBuildLink($build['link']);

                        if (
                            ($type === 'weapon' && $parsed['raw']['primary'] === $code || $parsed['raw']['secondary'] === $code) ||
                            $parsed['raw'][$type] === $code
                        ) {
                            if (!in_array($parsed['career'], $matches)) {
                                $matches[] = $parsed['career'];
                            }
                        }
                    }
                }

                if (count($matches) > 0) {
                    echo '<p>Characters using ' . ucfirst($type) . ' with code "' . $code . '":</p>';

                    foreach ($matches as $match) {
                        echo ' - ' . $match . '<br>';
                    }
                } else {
                    echo 'This item is not used!<br>';
                }

                echo '<br><a href="/?p=equipment">Go back</a>';

                break;
            case 'equipment_missing':

                $equipment_all = [
                    'weapon' => [],
                    'necklace' => [],
                    'charm' => [],
                    'trinket' => [],
                ];
                $equipment_missing = [
                    'weapon' => [],
                    'necklace' => [],
                    'charm' => [],
                    'trinket' => [],
                ];

                if (!empty($data['equipment'])) {
                    foreach ($data['equipment'] as $type => $items) {
                        foreach ($items as $item) {
                            $equipment_all[$type][] = $item;
                        }
                    }
                }

                foreach ($data['builds'] as $i => $build) {
                    if (isset($build['ignore_missing']) && $build['ignore_missing'] === true) {
                        continue;
                    }

                    $parsed = parseBuildLink($build['link']);

                    foreach ($parsed['raw'] as $type => $item) {
                        if (substr_count($item, '-') === 3 && !in_array($item, $equipment_all['weapon']) && !in_array($item, $equipment_missing['weapon'])) {
                            $equipment_missing['weapon'][] = $item;
                        } elseif (substr_count($item, '-') === 2 && !in_array($item, $equipment_all[$type]) && !in_array($item, $equipment_missing[$type])) {
                            $equipment_missing[$type][] = $item;
                        }
                    }
                }

            case 'equipment':
                $equipment = $data['equipment'];
                if (isset($equipment_missing)) {
                    $equipment = $equipment_missing;
                }
                $count = count($equipment['weapon']) + count($equipment['necklace']) + count($equipment['charm']) + count($equipment['trinket']);

                if (isset($_GET['p']) && $_GET['p'] === 'equipment_missing') {
                    echo '<h2>MISSING EQUIPMENT (' . $count . ')</h2>';
                } else {
                    echo '<h2>EQUIPMENT (' . $count . ')</h2>';
                }

                if (!empty($equipment)) {
                    foreach ($equipment as $type => $items) {
                        if (empty($items)) {
                            continue;
                        }

                        echo '<h3>' . strtoupper($type) . '</h3>';
                        echo '<div class="items">';

                        asort($items, SORT_NATURAL);
                        foreach (array_reverse($items) as $item) {
                            $id = md5($type . $item);

                            echo '<div class="item" id="' . $id . '" _onclick="javascript:location.href=\'?p=equipment_reference&t=' . $type . '&c=' . $item . '\'">';

                            $used = 0;
                            if (!empty($data['builds'])) {
                                foreach ($data['builds'] as $i => $build) {
                                    $parsed = parseBuildLink($build['link']);
            
                                    if (
                                        ($type === 'weapon' && $parsed['raw']['primary'] === $item || $parsed['raw']['secondary'] === $item) ||
                                        ($type !== 'weapon' && $parsed['raw'][$type] === $item)
                                    ) {
                                        $used++;
                                    }
                                }
                            }

                            $code = explode('-', $item);

                            if ($type === 'weapon') {
                                if (in_array($code[0], $gamedata['ranged_equipment'])) {
                                    echo '<b>/' . $item . '</b> - <b>' . $gamedata['equipment'][$code[0]] . ' (' . getEquipmentOwner($code[0]) . ')</b> - ' . $gamedata['properties']['ranged'][$code[1]] . ' - ' . $gamedata['properties']['ranged'][$code[2]] . ' - ' . normalizeTrait($gamedata['traits']['ranged'][$code[3]], $code[0]) . ' &nbsp; <i class="bold ' . ($used === 0 ? 'lightred' : ' lightgreen') . '">~ used ' . $used . ' time(s)</i>';
                                } else {
                                    echo '<b>/' . $item . '</b> - <b>' . $gamedata['equipment'][$code[0]] . ' (' . getEquipmentOwner($code[0]) . ')</b> - ' . $gamedata['properties']['melee'][$code[1]] . ' - ' . $gamedata['properties']['melee'][$code[2]] . ' - ' . normalizeTrait($gamedata['traits']['melee'][$code[3]], $code[0]) . ' &nbsp; <i class="bold ' . ($used === 0 ? 'lightred' : 'lightgreen') . '">~ used ' . $used . ' time(s)</i>';
                                }
                            } else {
                                echo '<b>/' . $item . '</b> - ' . $gamedata['properties'][$type][$code[0]] . ' - ' . $gamedata['properties'][$type][$code[1]] . ' - ' . $gamedata['traits'][$type][$code[2]] . ' &nbsp; <i class="bold ' . ($used === 0 ? 'lightred' : 'lightgreen') . '">~ used ' . $used . ' time(s)</i>';
                            }
                            
                            if (isset($_GET['p']) && $_GET['p'] === 'equipment_missing') {
                                echo '<span class="item-actions"><a href="?p=builds_reference&t=' . $type . '&c=' . $item . '" title="Show builds using this item"><i class="fa fa-link"></i></a></span>';
                            } else {
                                echo '<span class="item-actions"><a href="?p=builds_reference&t=' . $type . '&c=' . $item . '" title="Show builds using this item"><i class="fa fa-link"></i></a><a href="?p=equipment&a=remove_equipment&t=' . $type . '&c=' . $item . '" onclick="return confirm(\'Are you sure?\');" title="Remove"><i class="fa fa-remove"></i></a></span>';
                            }

                            echo '</div>';
                        }
                        
                        echo '</div>';
                    }
                }

                break;
            case 'builds_reference':
            default:
                $guide_link = '';
                if (isset($gamedata['guides'][0])) {
                    $guide_link = ' &nbsp; ' . preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target="_blank" title="$1"><i class="fa fa-external-link"></i></a>', $gamedata['guides'][0]);;
                }
                
                echo '<h2>BUILDS (' . count($data['builds']) . ') ' . $guide_link . '</h2>';

                if (isset($_GET['p']) && $_GET['p'] === 'builds_reference') {
                    $referencedtype = strtolower($_GET['t']);
                    $referencedcode = $_GET['c'];

                    echo '<p class="yellow">Showing only builds using ' . ucfirst($referencedtype) . ' with code "' . $referencedcode . '".</p>';
                }

                $builds = [];
                foreach ($gamedata['careers'] as $id => $career) {
                    $builds[$id] = [];
                }

                if (!empty($data['builds'])) {
                    foreach ($data['builds'] as $i => $build) {
                        $parsed = parseBuildLink($build['link']);

                        if (isset($_GET['p']) && $_GET['p'] === 'builds_reference') {
                            if (
                                ($referencedtype === 'weapon' && $parsed['raw']['primary'] !== $referencedcode && $parsed['raw']['secondary'] !== $referencedcode) ||
                                ($referencedtype !== 'weapon' && $parsed['raw'][$referencedtype] !== $referencedcode)
                            ) {
                                continue;
                            }
                        }

                        if ($parsed !== null) {
                            $builds[$parsed['raw']['career']][$i] = $parsed + $build;
                        } else {
                            throw new \Exception('Failed to parse build link: ' . $build['link']);
                        }
                    }
                }

                if (!empty($builds)) {
                    $prev_id = 999;
                    $hero_i = 0;
                    foreach ($builds as $id => $career) {
                        if ($id < $prev_id) {
                            if ($hero_i > 0) {
                                echo '</div>';
                            }

                            $hero_i++;

                            $guide_link = '';
                            if (isset($gamedata['guides'][$hero_i])) {
                                $guide_link = ' &nbsp; <a href="' . $gamedata['guides'][$hero_i] . '" target="_blank" title="' . $gamedata['guides'][$hero_i] . '"><i class="fa fa-external-link"></i></a>';
                            }

                            $noBuilds = true;
                            foreach ($gamedata['heroes'][$hero_i]['careers'] as $career) {
                                if (!empty($builds[$career])) {
                                    $noBuilds = false;
                                    break;
                                }
                            }

                            if ($noBuilds === false) {
                                echo '<div class="hero"><h2>' . $gamedata['heroes'][$hero_i]['name'] . $guide_link . '</h2>';
                            }
                        }
                        $prev_id = $id;

                        if (!empty($builds[$id])) {
                            echo '<h3 id="career_' . $id . '">' . $gamedata['careers'][$id] . '</h3>';
                            echo '<div class="builds">';
            
                            $last_id = null;
                            $next_id = null;
                            $last_key = @end(array_keys($builds[$id]));

                            foreach ($builds[$id] as $i => $build) {
                                $bid = md5(trim(explode('heroes/', $build['link'])[1]));
                                $ignore_missing = isset($build['ignore_missing']) && $build['ignore_missing'] === true;

                                if (isset($_GET['p']) && $_GET['p'] === 'builds_reference') {
                                    $ignore_missing = false;
                                }

                                $referenced = null;
                                if (isset($referencedcode, $referencedtype)) {
                                    $referenced = ['code' => $referencedcode, 'type' => $referencedtype];
                                }

                                echo '<div class="build' . ($i == $last_key ? ' ' : '') . '" id="' . $bid . '" onclick="return toggleDetails(\'' . $bid . '\', event);">';
                                echo '<a href="' . $build['link'] . '" target="_blank" class="build-link">' . highlightMissingEquipment(explode('heroes/', $build['link'])[1], $ignore_missing, $referenced) . '</a>';

                                if (!empty($build['comment'])) {
                                    echo ' - ' . strtoupper(str_replace('!', '&#x2757;', $build['comment']));
                                }

                                if (!empty($build['reference'])) {
                                    echo '<a href="' . $build['reference'] . '" target="_blank" class="ref-link"title="Referenced build"><i class="fa fa-external-link"></i></a>';
                                }
                               
                                if ($ignore_missing) {
                                    echo ' <i class="fa fa-low-vision" title="Missing equipment is ignored from this build"></i>';
                                }

                                next($builds[$id]);
                                $next_id = key($builds[$id]);

                                $extraLinks = '';
                                if (!isset($_GET['p']) || $_GET['p'] !== 'builds_reference') {
                                    if (!empty($build['reference'])) {

                                        $extraLinks .= ' <a href="?a=sync_build&b=' . urlencode($build['link']) . '#' . $bid . '" title="Synchronize with referenced build and compare"><i class="fa fa-refresh"></i></a>';
                                    }
                                }

                                if (!empty($extraLinks)) {
                                    $extraLinks = trim($extraLinks) . ' ';
                                }
                                
                                // <a href="#" onclick="return toggleDetails(\'' . $bid . '\', event);"><i class="fa fa-question-circle"></i></a>
                                echo '<span class="build-actions">' . $extraLinks . '<a href="?a=copy_build&b=' . urlencode($build['link']) . '" title="Copy"><i class="fa fa-copy"></i></a> <a href="?a=edit_build&b=' . urlencode($build['link']) . '" title="Edit"><i class="fa fa-edit"></i></a> <a href="?p=builds&a=remove_build&b=' . urlencode($build['link']) . '#career_' . $id . '" onclick="return confirm(\'Are you sure?\');" title="Remove"><i class="fa fa-remove"></i></a></span>';

                                $moveLinks = '';
                                if (!isset($_GET['p']) || $_GET['p'] !== 'builds_reference') {
                                    if ($last_id !== null) {
                                        $moveLinks .= '<a href="?p=builds&a=move_build&b=' . urlencode($build['link']) . '&id=' . $last_id . '#' . $bid . '" title="Move up"><i class="fa fa-arrow-up"></i></a>';
                                    }

                                    if ($i !== array_key_last($builds[$id])) {
                                        $moveLinks .= '<a href="?p=builds&a=move_build&b=' . urlencode($build['link']) . '&id=' . $next_id . '#' . $bid . '" title="Move down"><i class="fa fa-arrow-down"></i></a>';
                                    }
                                }
                                echo '<span class="build-move">' . $moveLinks . '</span>';

                                echo renderBuildDetails($build);
                               
                                echo '</div>';

                                $last_id = $i;
                            }

                            echo '</div>';
                        }
                    }
                    echo '</div>';
                }
        }
}

?>
<p class="copyright">&copy; <a href="https://jacklul.github.io/" target="_blank">Jack'lul</a></p>
</body>
</html>
