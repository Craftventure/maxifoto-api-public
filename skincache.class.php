<?php

/**
 * Created by PhpStorm.
 * User: joey_
 * Date: 27-1-2017
 * Time: 23:55
 */
require_once('mojangapi.class.php');

class SkinCache
{
    public function download($uuid, $initialTextureUrl)
    {
        try {
            if ($uuid == null || !is_string($uuid) || strlen($uuid) <= 0) {
                return false;
            }
            $lowerName = strtolower($uuid);
            $local_file = "skins/" . $lowerName . "_temp.png";
            $local_file_final = "skins/" . $lowerName . ".png";
            $local_file_lower = strtolower($local_file_final);
            $fileExists = file_exists($local_file_lower);
            if ($fileExists) {
                $interval = strtotime('-3 hours');
                if (filemtime($local_file_lower) <= $interval)
                    unlink($local_file_lower);
                $fileExists = file_exists($local_file_lower);
            }
            if (!$fileExists) {
                $skin = null;
                if ($initialTextureUrl != null) {
//                    print("Loading url");
                    $skin = MojangAPI::fetch($initialTextureUrl);
                }
                if ($skin == null || $skin == FALSE) {
//                    print("Loading skin");
                    MojangAPI::getSkin($uuid);
                }

//                print   "$skin \n";
//                var_dump($skin);

                if ($skin != null && $skin != false) {
//                    print "Saving skin to file";

                    file_put_contents($local_file, $skin);

                    list($width, $height) = getimagesize($local_file);
                    if ($width && $height) {
                        if ($height == 64) {
                            $result = rename($local_file, $local_file_final);
                            if (file_exists($local_file))
                                unlink($local_file);
                            return $result;
                        } else if ($height == 32) {
                            //                    echo 'using python<br/>';
                            $result = shell_exec("python3 format18.py " . __DIR__ . "/" . $local_file . " 2>&1");
                            //                    echo $result;
                            list($width, $height) = getimagesize($local_file);
                            //                    echo $width . ' > ' . $height . '<br/>';
                            if ($width == 64 && $height == 64) {
                                //                        echo 'Resize python<br/>';
                                $result = rename($local_file, $local_file_final);
                                if (file_exists($local_file))
                                    unlink($local_file);
                                return $result;
                            }
                        }
                    } else {
                        echo 'fail';
                    }
                }
                if (file_exists($local_file))
                    unlink($local_file);
                if (file_exists($local_file_final))
                    unlink($local_file_final);
            }
        } catch (Exception $e) {
        }
        return false;
    }
}