<?php

namespace CustomWing;

use pocketmine\entity\Skin;
use pocketmine\player\Player;

class setSkin
{

    public function setSkin(Player $player, string $stuffName)
    {
        $locate = "skin";
        $skin = $player->getSkin();
        $name = $player->getName();
        $path = Main::$instance->getSkinPath($name);
        if ($path === false || !is_file($path)) {
            $player->sendMessage("Your player skin is not available yet. Please reconnect and try again.");
            return;
        }

        $size = @getimagesize($path);
        if (!is_array($size) || !isset($size[0], $size[1]) || $size[0] <= 0 || $size[1] <= 0) {
            $player->sendMessage("Your saved player skin could not be read.");
            return;
        }

        $geometryPath = Main::$instance->getDataFolder() . $locate . "/" . $stuffName . ".json";
        if (!is_file($geometryPath)) {
            $player->sendMessage("The selected wing geometry is unavailable.");
            return;
        }
        $geometry = file_get_contents($geometryPath);
        if (!is_string($geometry)) {
            $player->sendMessage("The selected wing geometry could not be read.");
            return;
        }

        $path = $this->imgTricky($path, $stuffName, $locate, [$size[0], $size[1], 4]);
        if (!is_string($path) || !is_file($path)) {
            $player->sendMessage("The selected wing skin could not be prepared.");
            return;
        }
        $img = @imagecreatefrompng($path);
        if ($img === false) {
            @unlink($path);
            $player->sendMessage("The selected wing skin could not be prepared.");
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
        @unlink($path);
        $player->setSkin(new Skin($skin->getSkinId(), $skinbytes, "", "geometry." . $stuffName, $geometry));
        $player->sendSkin();
    }
    
    public function imgTricky(string $skinPath, string $stuffName, string $locate, array $size)
    {
        $path = Main::$instance->getDataFolder();
        $down = @imagecreatefrompng($skinPath);
        if ($down === false) {
            return false;
        }
        $overlayPath = $path . $locate . "/" . $stuffName . ".png";
        if (!is_file($overlayPath)) {
            imagedestroy($down);
            return false;
        }
        $upper = null;
        if ($size[0] * $size[1] * $size[2] == 65536) {
            $upper = $this->resize_image($overlayPath, 128, 128);
        } else {
            $upper = $this->resize_image($overlayPath, 64, 64);
        }
        if ($upper === false) {
            imagedestroy($down);
            return false;
        }
        //Remove black color out of the png
        imagecolortransparent($upper, imagecolorallocatealpha($upper, 0, 0, 0, 127));

        imagealphablending($down, true);
        imagesavealpha($down, true);
        imagecopymerge($down, $upper, 0, 0, 0, 0, $size[0], $size[1], 100);
        $temporaryPath = tempnam($path, "wing-");
        if ($temporaryPath === false || !imagepng($down, $temporaryPath)) {
            if (is_string($temporaryPath) && is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
            imagedestroy($down);
            imagedestroy($upper);
            return false;
        }
        imagedestroy($down);
        imagedestroy($upper);
        return $temporaryPath;

    }

    public function resize_image($file, $w, $h, $crop = FALSE)
    {
        $dimensions = @getimagesize($file);
        if (!is_array($dimensions) || !isset($dimensions[0], $dimensions[1]) || $dimensions[0] <= 0 || $dimensions[1] <= 0) {
            return false;
        }
        list($width, $height) = $dimensions;
        $r = $width / $height;
        if ($crop) {
            if ($width > $height) {
                $width = ceil($width - ($width * abs($r - $w / $h)));
            } else {
                $height = ceil($height - ($height * abs($r - $w / $h)));
            }
            $newwidth = $w;
            $newheight = $h;
        } else {
            if ($w / $h > $r) {
                $newwidth = $h * $r;
                $newheight = $h;
            } else {
                $newheight = $w / $r;
                $newwidth = $w;
            }
        }
        $src = @imagecreatefrompng($file);
        if ($src === false) {
            return false;
        }
        $dst = imagecreatetruecolor($w, $h);
        if ($dst === false) {
            imagedestroy($src);
            return false;
        }
        imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        imagedestroy($src);

        return $dst;
    }
}
