<?php

declare(strict_types=1);

namespace CustomWing;

use GdImage;
use pocketmine\entity\Skin;
use pocketmine\player\Player;

final class resetSkin{
	public function __construct(private Main $plugin){}

	public function setSkin(Player $player) : void{
		$path = $this->plugin->getSkinPath($player->getName());
		if(!is_string($path) || !is_file($path)){
			$player->sendMessage("Your player skin is not available yet. Please reconnect and try again.");
			return;
		}

		$image = @imagecreatefrompng($path);
		$dimensions = @getimagesize($path);
		if(!$image instanceof GdImage || $dimensions === false || $dimensions[0] <= 0 || $dimensions[1] <= 0){
			if($image instanceof GdImage){
				imagedestroy($image);
			}
			$player->sendMessage("Your saved player skin could not be read.");
			return;
		}

		try{
			$skinBytes = $this->skinDataFromImage($image, $dimensions[0], $dimensions[1]);
		}finally{
			imagedestroy($image);
		}
		if($skinBytes === null){
			$player->sendMessage("Your saved player skin could not be read.");
			return;
		}

		$geometryPath = $this->plugin->getDataFolder() . "steve.json";
		if(!is_file($geometryPath)){
			$player->sendMessage("The default player geometry is unavailable.");
			return;
		}
		$geometry = file_get_contents($geometryPath);
		if(!is_string($geometry)){
			$player->sendMessage("The default player geometry could not be read.");
			return;
		}

		$skin = $player->getSkin();
		$player->setSkin(new Skin($skin->getSkinId(), $skinBytes, "", "geometry.humanoid.custom", $geometry));
		$player->sendSkin();
	}

	private function skinDataFromImage(GdImage $image, int $width, int $height) : ?string{
		$skinBytes = "";
		for($y = 0; $y < $height; $y++){
			for($x = 0; $x < $width; $x++){
				$color = imagecolorat($image, $x, $y);
				if($color === false){
					return null;
				}
				$alpha = ((~($color >> 24)) << 1) & 0xff;
				$red = ($color >> 16) & 0xff;
				$green = ($color >> 8) & 0xff;
				$blue = $color & 0xff;
				$skinBytes .= chr($red) . chr($green) . chr($blue) . chr($alpha);
			}
		}
		return $skinBytes;
	}
}
