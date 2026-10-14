<?php

namespace CustomWing;

use pocketmine\entity\Skin;
use pocketmine\player\Player;

class resetSkin
{


    public function setSkin(Player $player)
    {
        $skin = $player->getSkin();
        $name = $player->getName();
        $path = Main::$instance->getSkinPath($name);
        if ($path === false || !is_file($path)) {
            $player->sendMessage("Your player skin is not available yet. Please reconnect and try again.");
            return;
        }

        $img = @imagecreatefrompng($path);
        $size = @getimagesize($path);
        if ($img === false || !is_array($size) || !isset($size[0], $size[1]) || $size[0] <= 0 || $size[1] <= 0) {
            if ($img !== false) {
                imagedestroy($img);
            }
            $player->sendMessage("Your saved player skin could not be read.");
            return;
        }
        $skinbytes = "";
        for ($y = 0; $y < $size[1]; $y++) {
            for ($x = 0; $x < $size[0]; $x++) {
                $colorat = @imagecolorat($img, $x, $y);
                $a = ((~((int)($colorat >> 24))) << 1) & 0xff;
                $r = ($colorat >> 16) & 0xff;
                $g = ($colorat >> 8) & 0xff;
                $b = $colorat & 0xff;
                $skinbytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
        $geometryPath = Main::$instance->getDataFolder() . "steve.json";
        if (!is_file($geometryPath)) {
            $player->sendMessage("The default player geometry is unavailable.");
            return;
        }
        $geometry = file_get_contents($geometryPath);
        if (!is_string($geometry)) {
            $player->sendMessage("The default player geometry could not be read.");
            return;
        }
        $player->setSkin(new Skin($skin->getSkinId(), $skinbytes, "", "geometry.humanoid.custom", $geometry));
        $player->sendSkin();
    }
}
