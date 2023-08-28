<?php

require_once("skincache.class.php");

function render($json, $settings, $names)
{

}

parse_str($_SERVER['QUERY_STRING'], $query);

if (array_key_exists("id", $query) && array_key_exists("names", $query)) {
    $ride = $query["id"];
    $names = $query["names"];

    if (is_string($ride) && is_array($names)) {
        $settings = pictureSettingsByName($ride);

        if ($settings != null && count($names) == $settings['player_count']) {
            $json['render_id'] = $settings['id'];

            // Download skins
            $skinCache = new SkinCache();
            foreach ($names as &$name) {
                $skinCache->download($name);
            }

            // Copy skins to blender scene
            for ($i = 0; $i < count($names); $i++) {
                $source = __DIR__ . "/skins/" . strtolower($names[$i]) . ".png";
                $dest = __DIR__ . "/scenes/" . $ride . "/skins/" . ($i + 1) . ".png";

                if (file_exists($source)) {
                    if (!copy(strtolower($source), strtolower($dest)))
                        echo "Copy failed! " . var_dump(error_get_last()) . "<br/>";
                } else if (file_exists($dest)) {
                    unlink($dest);
                }
            }

            // Render with blender
            $result = shell_exec("blender -b scenes/" . $settings["id"] . "/scene.blend -o " . __DIR__ . "/render/" . $settings["id"] . "_# -a 0 2>&1; echo $?");
            // Assume rendering went successful, we don't know if it's actually cause blender won't give status codes...

            for ($i = $settings['start_frame']; $i <= $settings['end_frame']; $i++) {
                try {
                    if (file_exists('cover/' . $ride . '_back.png') &&
                        file_exists('cover/' . $ride . '_front.png') &&
                        file_exists('render/' . $ride . '_' . $i . '.png')
                    ) {
                        $baseImage = imagecreatefrompng('cover/' . $ride . '_back.png');
                        $frontImage = imagecreatefrompng('cover/' . $ride . '_front.png');
                        $playerImage = imagecreatefrompng('render/' . $ride . '_' . $i . '.png');

                        imagealphablending($baseImage, true);
                        imagesavealpha($baseImage, true);
                        imagecopy($baseImage, $playerImage, 0, 0, 0, 0, $settings['width'], $settings['height']);
                        imagecopy($baseImage, $frontImage, 0, 0, 0, 0, $settings['width'], $settings['height']);
                        imagepng($baseImage, 'image/' . $ride . '_' . $i . '.png');
                        imagedestroy($baseImage);
                        imagedestroy($frontImage);
                        imagedestroy($playerImage);

                        array_push($json["pictures"], array(
                            'name' => $ride . '_' . $i . '.png',
                            'frame' => $i
                        ));
                    }
                } catch (Exception $e) {
                }
            }

            $json["succes"] = true;
            http_response_code(200);
        }
    }
}

exit(json_encode($json));