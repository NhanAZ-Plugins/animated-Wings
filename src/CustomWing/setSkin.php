<?php

declare(strict_types=1);

namespace CustomWing;

use GdImage;
use pocketmine\entity\Skin;
use pocketmine\player\Player;

final class setSkin{
	public function __construct(private Main $plugin){}

	public function setSkin(Player $player, string $wingName) : void{
		$skinPath = $this->plugin->getSkinPath($player->getName());
		if(!is_string($skinPath) || !is_file($skinPath)){
			$player->sendMessage("Your player skin is not available yet. Please reconnect and try again.");
			return;
		}
		$dimensions = @getimagesize($skinPath);
		if($dimensions === false || $dimensions[0] <= 0 || $dimensions[1] <= 0){
			$player->sendMessage("Your saved player skin could not be read.");
			return;
		}

		$geometryPath = $this->plugin->getDataFolder() . "skin/" . $wingName . ".json";
		if(!is_file($geometryPath)){
			$player->sendMessage("The selected wing geometry is unavailable.");
			return;
		}
		$geometry = file_get_contents($geometryPath);
		if(!is_string($geometry)){
			$player->sendMessage("The selected wing geometry could not be read.");
			return;
		}

		$compositePath = $this->imgTricky($skinPath, $wingName, "skin", [$dimensions[0], $dimensions[1], 4]);
		if(!is_string($compositePath) || !is_file($compositePath)){
			$player->sendMessage("The selected wing skin could not be prepared.");
			return;
		}
		$image = @imagecreatefrompng($compositePath);
		@unlink($compositePath);
		if(!$image instanceof GdImage){
			$player->sendMessage("The selected wing skin could not be prepared.");
			return;
		}

		try{
			$skinBytes = $this->skinDataFromImage($image, $dimensions[0], $dimensions[1]);
		}finally{
			imagedestroy($image);
		}
		if($skinBytes === null){
			$player->sendMessage("The selected wing skin could not be read.");
			return;
		}

		$currentSkin = $player->getSkin();
		$player->setSkin(new Skin($currentSkin->getSkinId(), $skinBytes, "", "geometry." . $wingName, $geometry));
		$player->sendSkin();
	}

	/** @param array{0: int, 1: int, 2: int} $size */
	public function imgTricky(string $skinPath, string $wingName, string $directory, array $size) : string|false{
		$skinImage = @imagecreatefrompng($skinPath);
		if(!$skinImage instanceof GdImage){
			return false;
		}

		$overlayPath = $this->plugin->getDataFolder() . $directory . "/" . $wingName . ".png";
		if(!is_file($overlayPath)){
			imagedestroy($skinImage);
			return false;
		}
		$overlay = $this->resize_image($overlayPath, $size[0], $size[1]);
		if(!$overlay instanceof GdImage){
			imagedestroy($skinImage);
			return false;
		}

		$transparent = imagecolorallocatealpha($overlay, 0, 0, 0, 127);
		if($transparent === false){
			imagedestroy($skinImage);
			imagedestroy($overlay);
			return false;
		}
		imagecolortransparent($overlay, $transparent);
		imagealphablending($skinImage, true);
		imagesavealpha($skinImage, true);
		if(!imagecopymerge($skinImage, $overlay, 0, 0, 0, 0, $size[0], $size[1], 100)){
			imagedestroy($skinImage);
			imagedestroy($overlay);
			return false;
		}

		$temporaryPath = tempnam($this->plugin->getDataFolder(), "wing-");
		if($temporaryPath === false || !imagepng($skinImage, $temporaryPath)){
			if(is_string($temporaryPath) && is_file($temporaryPath)){
				unlink($temporaryPath);
			}
			imagedestroy($skinImage);
			imagedestroy($overlay);
			return false;
		}
		imagedestroy($skinImage);
		imagedestroy($overlay);
		return $temporaryPath;
	}

	public function resize_image(string $file, int $width, int $height, bool $crop = false) : GdImage|false{
		if($width <= 0 || $height <= 0){
			return false;
		}
		$sourceDimensions = @getimagesize($file);
		if($sourceDimensions === false || $sourceDimensions[0] <= 0 || $sourceDimensions[1] <= 0){
			return false;
		}
		$source = @imagecreatefrompng($file);
		if(!$source instanceof GdImage){
			return false;
		}
		$target = imagecreatetruecolor($width, $height);
		if(!$target instanceof GdImage){
			imagedestroy($source);
			return false;
		}
		$transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
		if($transparent === false){
			imagedestroy($source);
			imagedestroy($target);
			return false;
		}
		imagefill($target, 0, 0, $transparent);
		imagealphablending($target, false);
		imagesavealpha($target, true);

		$sourceWidth = $sourceDimensions[0];
		$sourceHeight = $sourceDimensions[1];
		$sourceX = 0;
		$sourceY = 0;
		if($crop){
			$sourceAspect = $sourceWidth / $sourceHeight;
			$targetAspect = $width / $height;
			if($sourceAspect > $targetAspect){
				$cropWidth = max(1, (int) round($sourceHeight * $targetAspect));
				$sourceX = intdiv($sourceWidth - $cropWidth, 2);
				$sourceWidth = $cropWidth;
			}else{
				$cropHeight = max(1, (int) round($sourceWidth / $targetAspect));
				$sourceY = intdiv($sourceHeight - $cropHeight, 2);
				$sourceHeight = $cropHeight;
			}
			$destinationWidth = $width;
			$destinationHeight = $height;
		}else{
			$scale = min($width / $sourceWidth, $height / $sourceHeight);
			$destinationWidth = max(1, (int) round($sourceWidth * $scale));
			$destinationHeight = max(1, (int) round($sourceHeight * $scale));
		}

		$copied = imagecopyresampled($target, $source, 0, 0, $sourceX, $sourceY, $destinationWidth, $destinationHeight, $sourceWidth, $sourceHeight);
		imagedestroy($source);
		if(!$copied){
			imagedestroy($target);
			return false;
		}
		return $target;
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
