<?php

http_response_code(400);
header('Content-Type: application/json');

$json = array(
    'succes' => false,
    'error' => null,
    'pictures' => [],
    'render_id' => null
);

function errorHandler($errno, $errstr)
{
    global $json;
    $json['error'] = $errno . ' ' . $errstr;
    dieWithJson();
}

function dieWithJson()
{
    global $json;
    header('Content-type:application/json;charset=utf-8');
    exit(json_encode($json));
}

set_error_handler("errorHandler");
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once("skincache.class.php");

function prepare()
{
    if (!is_dir("skins"))
        mkdir("skins");
    if (!is_dir("image"))
        mkdir("image");
    if (!is_dir("render"))
        mkdir("render");
}

function pictureSettingsByName($name)
{
    $settings = array(
        'spacemountain' => array(
            'player_count' => 20,
            'render_id' => 'spacemountain',
            'internal_name' => 'spacemountain',
            'file_name' => 'spacemountain',
            'start_frame' => 0,
            'end_frame' => 4,
            'cover_count' => 1,
            'width' => 128,
            'height' => 128
        ),
        'ppl' => array(
            'player_count' => 4,
            'render_id' => 'ppl',
            'internal_name' => 'ppl',
            'file_name' => 'ppl',
            'start_frame' => 0,
            'end_frame' => 0,
            'cover_count' => 1,
            'width' => 128,
            'height' => 128
        ),
        'cookiefactory' => array(
            'player_count' => 4,
            'render_id' => 'cookiefactory',
            'internal_name' => 'cookiefactory',
            'file_name' => 'cookiefactory',
            'start_frame' => 0,
            'end_frame' => 0,
            'cover_count' => 1,
            'width' => 256,
            'height' => 256
        ),
        'indy' => array(
            'player_count' => 2,
            'render_id' => 'indy',
            'internal_name' => 'indy',
            'file_name' => 'indy',
            'start_frame' => 0,
            'end_frame' => 0,
            'cover_count' => 1,
            'width' => 256,
            'height' => 256
        ),
        'aguaazul' => array(
            'player_count' => 3,
            'render_id' => 'aguaazul',
            'internal_name' => 'aguaazul',
            'file_name' => 'aguaazul',
            'start_frame' => 0,
            'end_frame' => 0,
            'cover_count' => 1,
            'width' => 128,
            'height' => 128
        ),
        'alphadera' => array(
            'player_count' => 6,
            'render_id' => 'alphadera',
            'internal_name' => 'alphadera',
            'file_name' => 'alphadera',
            'start_frame' => 0,
            'end_frame' => 0,
            'cover_count' => 1,
            'width' => 128,
            'height' => 128
        ),
        'fenrir' => array(
            'player_count' => 16,
            'render_id' => 'fenrir',
            'internal_name' => 'fenrir',
            'file_name' => 'fenrir',
            'start_frame' => 0,
            'end_frame' => 3,
            'cover_count' => 1,
            'width' => 128,
            'height' => 128
        ),
        'ccr' => array(
            'player_count' => 24,
            'render_id' => 'ccr',
            'internal_name' => 'ccr',
            'file_name' => 'ccr',
            'start_frame' => 0,
            'end_frame' => 3,
            'cover_count' => 1,
            'width' => 128,
            'height' => 128
        ),
        'fenghuang' => array(
            'player_count' => 28,
            'render_id' => 'fenghuang',
            'internal_name' => 'fenghuang',
            'file_name' => 'fenghuang',
            'start_frame' => 0,
            'end_frame' => 13,
            'cover_count' => 2,
            'person_resolver' => function ($data, $names, $frame) {
                $frameCount = $data['end_frame'] - $data['start_frame'] + 1;
                $personPerFrame = $data['player_count'] / $frameCount;

                $frame = $frame % 2 == 0 ? ($frame + 1) : ($frame - 1);

                $startNamesIndex = $frame * $personPerFrame;
                $endNamesIndex = ($frame + 1) * $personPerFrame;

                $playersInCurrentPicture = [];
                for ($p = $startNamesIndex; $p < $endNamesIndex; $p++) {
                    if ($names[$p] != null) {
                        array_push($playersInCurrentPicture, $names[$p]);
                    }
                }

                return $playersInCurrentPicture;
            },
            'width' => 128,
            'height' => 128
        )
    );

    if (array_key_exists($name, $settings)) {
        return $settings[$name];
    }
    return null;
}

prepare();
parse_str($_SERVER['QUERY_STRING'], $query);

function findKey($array, $keySearch)
{
    foreach ($array as $key => $item) {
        if ($key == $keySearch) {
            echo 'yes, it exists';
            return true;
        } elseif (is_array($item) && findKey($item, $keySearch)) {
            return true;
        }
    }
    return false;
}

function cleanCache()
{
    $kept = [];
    $removed = [];
    if ($handle = opendir('skins')) {
        $interval = strtotime('-3 hours');
        while (false !== ($file = readdir($handle))) {
            $entry = 'skins/' . $file;
            $file = str_replace(".png", "", $file);
            if (strpos($entry, '.png') !== false) {
                if (fileatime($entry) <= $interval) {
                    unlink($entry);
                    array_push($removed, $file);
                } else
                    array_push($kept, $file);
            }
        }
        closedir($handle);
    }
    return array($kept, $removed);
}

if (array_key_exists("cleancache", $query) && is_string($query['cleancache'])) {
    $result = cleanCache();

    $kept = $result[0];
    $removed = $result[1];

    $json["succes"] = true;
    $json["kept"] = $kept;
    $json["removed"] = $removed;
    http_response_code(200);

    dieWithJson();
} else
    if (array_key_exists("cache", $query) && is_string($query['cache'])) {
        cleanCache();
        $skinCache = new SkinCache();

        $textureUrl = null;
        try {
            if (array_key_exists("texture", $query) && is_string($query['texture'])) {
                $skinData = json_decode(base64_decode($query['texture']), true);
                $textureUrl = $skinData["textures"]["SKIN"]["url"];
            }
        } catch (Exception $e) {
        }

        $result = $skinCache->download($query['cache'], $textureUrl);
        $json["succes"] = true;
        $json["downloaded"] = $result;
        http_response_code(200);

    } else if (array_key_exists("id", $query) && array_key_exists("names", $query) && array_key_exists("textures", $query)) {
        $ride = $query["id"];
        $names = $query["names"];
        $encodedTextureDatas = $query["textures"];

//    $resolver = pictureSettingsByName('fenghuang')['person_resolver'];
//    $resolver(pictureSettingsByName('fenghuang'), $names, 0);
//
//    if (true)
//        exit();

        if (is_string($ride) && is_array($names)) {
            $settings = pictureSettingsByName($ride);

            if ($settings != null && count($names) == $settings['player_count']) {
                $json['render_id'] = $settings['render_id'];

                // Download skins
                $skinCache = new SkinCache();

                $nameToTextureMap = array_combine($names, $encodedTextureDatas);
                foreach ($names as &$name) {
                    $textureUrl = null;
                    try {
                        $skinData = array_key_exists($name, $nameToTextureMap) ? json_decode(base64_decode($nameToTextureMap[$name]), true) : null;
                        if (isset($skinData["textures"]) && $skinData["textures"]["SKIN"] && $skinData["textures"]["SKIN"]["url"])
                            $textureUrl = $skinData["textures"]["SKIN"]["url"];
                    } catch (Exception $e) {
                    }
                    $skinCache->download($name, $textureUrl);
                }

                // Copy skins to blender scene
                for ($i = 0; $i < count($names); $i++) {
                    $source = __DIR__ . "/skins/" . strtolower($names[$i]) . ".png";
                    $destDir = __DIR__ . "/scenes/" . $settings["file_name"] . "/skins/";
                    $dest = $destDir . ($i + 1) . ".png";

                    if (!file_exists($destDir)) {
                        mkdir($destDir);
                    }

                    if (!file_exists($source)) {
                        if ($names[$i] != null) {
                            $source = __DIR__ . "/default.png";
                        } else {
                            $source = __DIR__ . "/fallback.png";
                        }
                    }

                    if (file_exists($source)) {
//                        print($source . " to " . $dest);
                        if (!copy(strtolower($source), strtolower($dest)))
                            echo "Copy failed! " . var_dump(error_get_last()) . "<br/>";
                    } else if (file_exists($dest)) {
                        unlink($dest);
                    }
                }

                $sceneFiles = glob("scenes/" . $settings["file_name"] . "/*.blend");
                $sceneFile = $sceneFiles[array_rand($sceneFiles, 1)];
//                var_dump($sceneFile);
//                exit();


                for ($i = $settings['start_frame']; $i <= $settings['end_frame']; $i++) {
                    $renderFileName = 'render/' . $settings["file_name"] . '_' . $i . '.png';
                    if (file_exists($renderFileName)) {
                        unlink($renderFileName);
                    }
                }

                // Render with blender
                $command = "blender -b " . $sceneFile . " -o " . __DIR__ . "/render/" . $settings["file_name"] . "_# -a 0 2>&1; echo $?";
//                print($command);
                $result = shell_exec($command);
//                print ($result);
                // Assume rendering went successful, we don't know if it's actually cause blender won't give status codes...

                for ($i = $settings['start_frame']; $i <= $settings['end_frame']; $i++) {
                    try {
                        $coverCount = $settings['cover_count'];
                        $currentCover = $i % $coverCount;
                        $coverSuffix = $coverCount == 1 ? "" : "_" . $currentCover;
                        if (file_exists('cover/' . $settings["internal_name"] . '_back' . $coverSuffix . '.png') &&
                            file_exists('cover/' . $settings["internal_name"] . '_front' . $coverSuffix . '.png') &&
                            file_exists('render/' . $settings["file_name"] . '_' . $i . '.png')
                        ) {
                            $baseImage = imagecreatefrompng('cover/' . $settings["internal_name"] . '_back' . $coverSuffix . '.png');
                            $frontImage = imagecreatefrompng('cover/' . $settings["internal_name"] . '_front' . $coverSuffix . '.png');
                            $playerImage = imagecreatefrompng('render/' . $settings["file_name"] . '_' . $i . '.png');

                            imagealphablending($baseImage, true);
                            imagesavealpha($baseImage, true);
                            imagecopy($baseImage, $playerImage, 0, 0, 0, 0, $settings['width'], $settings['height']);
                            imagecopy($baseImage, $frontImage, 0, 0, 0, 0, $settings['width'], $settings['height']);
                            imagepng($baseImage, 'image/' . $settings["file_name"] . '_' . $i . '.png');
                            imagedestroy($baseImage);
                            imagedestroy($frontImage);
                            imagedestroy($playerImage);


                            if (key_exists('person_resolver', $settings) && $settings['person_resolver'] != null) {
                                $playersInCurrentPicture = $settings['person_resolver']($settings, $names, $i);
                            } else {
                                $frameCount = $settings['end_frame'] - $settings['start_frame'] + 1;
                                $personPerFrame = $settings['player_count'] / $frameCount;

                                $startNamesIndex = $i * $personPerFrame;
                                $endNamesIndex = ($i + 1) * $personPerFrame;

                                $playersInCurrentPicture = [];
                                for ($p = $startNamesIndex; $p < $endNamesIndex; $p++) {
                                    if ($names[$p] != null) {
                                        array_push($playersInCurrentPicture, $names[$p]);
                                    }
                                }
                            }

                            array_push($json["pictures"], array(
                                'name' => $settings["file_name"] . '_' . $i . '.png',
                                'picture_id' => $i,
                                'players' => $playersInCurrentPicture
//                            'players' => $frameCount . ',' . $personPerFrame . ', ' . $startNamesIndex . ', ' . $endNamesIndex//$playersInCurrentPicture
                            ));
                        }
                    } catch (Exception $e) {
                    }
                }

                $json["succes"] = true;
                http_response_code(200);
            } else {
                $json['error'] = "Failed to find settings or not the right number of players sent";
            }
        } else {
            $json['error'] = "Ride or names not valid";
        }
    } else {
        $json['error'] = "Failed to match any URL pattern";
    }

dieWithJson();