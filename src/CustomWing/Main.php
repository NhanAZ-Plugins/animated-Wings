<?php

declare(strict_types=1);

namespace CustomWing;

use JsonMapper;
use JsonMapper_Exception;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\form\FormValidationException;
use pocketmine\network\mcpe\JwtException;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\types\login\ClientData;
use pocketmine\network\PacketHandlingException;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use function array_keys;
use function count;
use function extension_loaded;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function glob;
use function hash;
use function imagecreatefrompng;
use function is_dir;
use function is_file;
use function is_string;
use function json_decode;
use function json_encode;
use function strtolower;
use function unlink;
use function mkdir;
use function dirname;

final class Main extends PluginBase implements Listener{
	/** @var array<string, string> */
	private const WING_PERMISSIONS = [
		"kagune" => "kagune.wing",
		"kakuja" => "kakuja.wing",
		"mercy" => "mercy.wing",
		"balrog" => "balrog.wing",
		"blazingelectro" => "blazingelectro.wing",
		"darkaurora" => "darkaurora.wing",
		"davinci" => "davinci.wing",
		"devil" => "devil.wing",
		"diamond" => "diamond.wing",
		"legendarydragonknight" => "legendarydragonknight.wing",
		"monarchbutterfly" => "monarchbutterfly.wing",
		"razor" => "razor.wing",
		"robotic" => "robotic.wing"
	];

	/** @var list<string> */
	private const MENU_BUTTONS = [
		"§cKagune §4Kaneki",
		"§0Kakuja §4Kaneki",
		"§6Mercy §awing",
		"§cBalrog §awing",
		"§eBlazing §fElectro §awing",
		"§5Dark §dPurple §awing",
		"§6Da§fVinci §awing",
		"§4Devil §awing",
		"§bDiamond §awing",
		"§cLegendary §4Dragon §fKnight",
		"§bMonarch §cButter§dfly §awing",
		"§eRazor §awing",
		"§bRobo§3tic §awing",
		"Reset Skin",
		"Exit"
	];

	public static ?self $instance = null;

	public int $json = 0;

	protected function onEnable() : void{
		self::$instance = $this;
		if(!$this->checkRequirements()){
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}

		$this->checkSkin();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info($this->json . " Geometry Skin Confirmed");
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if(strtolower($command->getName()) !== "customwing"){
			return false;
		}
		if(!$sender instanceof Player){
			$sender->sendMessage(TextFormat::RED . "You do not have permission to use this command.");
			return false;
		}

		$this->Form($sender, TextFormat::YELLOW . "Select Your Wings:");
		return true;
	}

	public function dataReceiveEv(DataPacketReceiveEvent $event) : void{
		$packet = $event->getPacket();
		if(!$packet instanceof LoginPacket){
			return;
		}

		$data = self::decodeClientData($packet->clientDataJwt);
		$name = $data->ThirdPartyName;
		$skinPath = $this->getSkinPath($name);
		if($skinPath === false){
			$this->getLogger()->warning("Ignored login skin data with an invalid player name.");
			return;
		}

		$saveDirectory = dirname($skinPath);
		if(!is_dir($saveDirectory) && !mkdir($saveDirectory, 0777, true) && !is_dir($saveDirectory)){
			$this->getLogger()->error("Could not create the player skin directory.");
			return;
		}

		if($data->PersonaSkin){
			$defaultSkin = $this->getDataFolder() . "steve.png";
			if(!is_file($defaultSkin) || !copy($defaultSkin, $skinPath)){
				$this->getLogger()->warning("Could not save the fallback skin for a persona-skin player.");
			}
			return;
		}

		$skinBytes = base64_decode($data->SkinData, true);
		if(!is_string($skinBytes)){
			$this->getLogger()->warning("Ignored invalid encoded player skin data.");
			return;
		}
		if(!(new saveSkin())->saveSkin($skinBytes, $name)){
			$this->getLogger()->warning("Could not save an unsupported or invalid player skin.");
		}
	}

	public function onQuit(PlayerQuitEvent $event) : void{
		if($this->getConfig()->getNested("DeleteSkinAfterQuitting") !== true){
			return;
		}
		$skinPath = $this->getSkinPath($event->getPlayer()->getName());
		if($skinPath !== false && is_file($skinPath)){
			unlink($skinPath);
		}
	}

	public function getSkinPath(mixed $name) : string|false{
		if(!is_string($name) || $name === "" || strlen($name) > 256 || preg_match('/[\x00-\x1f\x7f]/', $name)){
			return false;
		}
		return $this->getDataFolder() . "saveskin/" . hash("sha256", strtolower($name)) . ".png";
	}

	public function Form(Player $sender, string $text) : WingMenu{
		$form = new WingMenu(
			TextFormat::RED . "Custom" . TextFormat::WHITE . "Wing",
			$text,
			self::MENU_BUTTONS,
			function(Player $player, int $response) : void{
				$this->handleMenuSelection($player, $response);
			}
		);
		$sender->sendForm($form);
		return $form;
	}

	private function handleMenuSelection(Player $player, int $response) : void{
		if($response === 13){
			$this->resetSkin($player);
			return;
		}
		$wingNames = array_keys(self::WING_PERMISSIONS);
		if(!isset($wingNames[$response])){
			return;
		}
		$wingName = $wingNames[$response];
		$permission = self::WING_PERMISSIONS[$wingName];
		if(!$player->hasPermission($permission) && !$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)){
			$this->Form($player, TextFormat::RED . "You do not have permission to use this wing.");
			return;
		}
		(new setSkin())->setSkin($player, $wingName);
	}

	public function resetSkin(Player $player) : void{
		$player->sendMessage("Reset to original skin successfully.");
		(new resetSkin())->setSkin($player);
	}

	public function checkSkin() : void{
		$available = [];
		$path = $this->getDataFolder() . "skin/";
		if(!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)){
			$this->getLogger()->error("Could not create the bundled wing asset directory.");
			$this->json = 0;
			return;
		}
		foreach(glob($path . "*.json") ?: [] as $geometryFile){
			$name = pathinfo($geometryFile, PATHINFO_FILENAME);
			if(is_file($path . $name . ".png")){
				$available[$name] = true;
			}
		}
		$this->json = count($available);
	}

	private function checkRequirements() : bool{
		if(!extension_loaded("gd")){
			$this->getLogger()->error("The GD extension is required to process player skins.");
			return false;
		}

		$this->saveDefaultConfig();
		foreach($this->getResources() as $resourceName => $resource){
			$destination = $this->getDataFolder() . $resourceName;
			if(is_file($destination)){
				continue;
			}
			if(!$this->saveResource($resourceName)){
				$this->getLogger()->error("Could not install required plugin resource " . $resourceName . ".");
				return false;
			}
		}

		foreach(["config.yml", "steve.png", "steve.json"] as $requiredResource){
			if(!is_file($this->getDataFolder() . $requiredResource)){
				$this->getLogger()->error("Required plugin resource " . $requiredResource . " is missing.");
				return false;
			}
		}
		return true;
	}

	public static function decodeClientData(string $clientDataJwt) : ClientData{
		try{
			[, $clientDataClaims, ] = JwtUtils::parse($clientDataJwt);
		}catch(JwtException $exception){
			throw PacketHandlingException::wrap($exception);
		}

		$mapper = new JsonMapper();
		$mapper->bEnforceMapType = false;
		$mapper->bExceptionOnMissingData = true;
		$mapper->bExceptionOnUndefinedProperty = true;
		try{
			$clientData = $mapper->map($clientDataClaims, new ClientData());
		}catch(JsonMapper_Exception $exception){
			throw PacketHandlingException::wrap($exception);
		}
		if(!$clientData instanceof ClientData){
			throw new \UnexpectedValueException("Could not map login client data");
		}
		return $clientData;
	}
}
