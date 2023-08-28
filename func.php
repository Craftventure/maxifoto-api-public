<?php

error_reporting(E_ALL);

function prepare()
{
    if (!is_dir("skins"))
        mkdir("skins");
    if (!is_dir("image"))
        mkdir("image");
    if (!is_dir("render"))
        mkdir("render");
}
