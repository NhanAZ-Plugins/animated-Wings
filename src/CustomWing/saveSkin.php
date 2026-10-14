<?php

declare(strict_types=1);

namespace CustomWing;

use GdImage;

final class saveSkin{
	/** @var array<int, array{int, int}> */
	private const SKIN_DIMENSIONS = [
		64 * 32 * 4 => [64, 32],
		64 * 64 * 4 => [64, 64],
		128 * 128 * 4 => [128, 128],
		128 * 256 * 4 => [128, 256]
	];

	public function __construct(private Main $plugin){}

	public function saveSkin(string $skinData, string $name) : bool{
		$path = $this->plugin->getSkinPath($name);
		if(!is_string($path)){
			return false;
		}

		$image = $this->skinDataToImage($skinData);
		if(!$image instanceof GdImage){
			return false;
		}
		try{
			return imagepng($image, $path);
		}finally{
			imagedestroy($image);
		}
	}

	public function skinDataToImage(string $skinData) : ?GdImage{
		$dimensions = self::SKIN_DIMENSIONS[strlen($skinData)] ?? null;
		if($dimensions === null){
			return null;
		}
		[$width, $height] = $dimensions;
		if($width * $height * 4 !== strlen($skinData)){
			return null;
		}

		$image = imagecreatetruecolor($width, $height);
		if(!$image instanceof GdImage){
			return null;
		}
		$transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
		if($transparent === false){
			imagedestroy($image);
			return null;
		}
		imagefill($image, 0, 0, $transparent);
		$position = 0;
		for($y = 0; $y < $height; $y++){
			for($x = 0; $x < $width; $x++){
				$red = ord($skinData[$position++]);
				$green = ord($skinData[$position++]);
				$blue = ord($skinData[$position++]);
				$alpha = max(0, min(127, intdiv(255 - ord($skinData[$position++]), 2)));
				$color = imagecolorallocatealpha($image, $red, $green, $blue, $alpha);
				if($color === false || !imagesetpixel($image, $x, $y, $color)){
					imagedestroy($image);
					return null;
				}
			}
		}
		imagesavealpha($image, true);
		return $image;
	}
}
