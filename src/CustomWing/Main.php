<?php

namespace CustomWing;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Human;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\JwtException;
use pocketmine\network\PacketHandlingException;
use pocketmine\network\mcpe\protocol\types\login\ClientData;
use pocketmine\plugin\PluginBase;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {
	
	/** @var self $instance */
    public static $instance;
    
    /** @var int*/
    public $json;
	
	public function onEnable(): void{
		self::$instance = $this;
    	$this->checkRequirement();
        if (!$this->isEnabled()) {
            return;
        }
        $this->checkSkin();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    	$this->getLogger()->info($this->json . " Geometry Skin Confirmed");
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
		if($sender instanceof Player){
			if($cmd->getName() == "customwing"){
				$this->Form($sender, TextFormat::YELLOW . "Select Your Wings:");
				return true;
			}
		} else {
			$sender->sendMessage(TextFormat::RED . "You dont Have Permission to use this Command");
			return false;
		}
        return false;
	}
	
	public function dataReceiveEv(DataPacketReceiveEvent $ev)
    {
        $packet = $ev->getPacket();
        if ($packet instanceof LoginPacket) {
            $data = self::decodeClientData($packet->clientDataJwt);
            $name = $data->ThirdPartyName;
            $skinPath = $this->getSkinPath($name);
            if ($skinPath === false) {
                $this->getLogger()->warning("Ignored login skin data with an invalid player name.");
                return;
            }

            $saveDirectory = dirname($skinPath);
            if (!is_dir($saveDirectory) && !mkdir($saveDirectory, 0777, true) && !is_dir($saveDirectory)) {
                $this->getLogger()->error("Could not create the player skin directory.");
                return;
            }

            if ($data->PersonaSkin) {
                $defaultSkin = $this->getDataFolder() . "steve.png";
                if (!is_file($defaultSkin) || !copy($defaultSkin, $skinPath)) {
                    $this->getLogger()->warning("Could not save the fallback skin for a persona-skin player.");
                }
                return;
            }

            $skinBytes = base64_decode($data->SkinData, true);
            if (!is_string($skinBytes)) {
                $this->getLogger()->warning("Ignored invalid encoded player skin data.");
                return;
            }
            $saveSkin = new saveSkin();
            if (!$saveSkin->saveSkin($skinBytes, $name)) {
                $this->getLogger()->warning("Could not save an unsupported or invalid player skin.");
            }
        }
    }
    
    public function onQuit(PlayerQuitEvent $ev)
    {
        $willDelete = $this->getConfig()->getNested('DeleteSkinAfterQuitting');
        if ($willDelete) {
            $skinPath = $this->getSkinPath($ev->getPlayer()->getName());
            if ($skinPath !== false && is_file($skinPath)) {
                unlink($skinPath);
            }
        }
    }

    /**
     * Maps untrusted client names to a safe, stable filename without exposing them in the path.
     *
     * @return string|false
     */
    public function getSkinPath($name)
    {
        if (!is_string($name) || $name === "" || strlen($name) > 256 || preg_match('/[\x00-\x1f\x7f]/', $name)) {
            return false;
        }
        $key = hash("sha256", strtolower($name));
        return $this->getDataFolder() . "saveskin/" . $key . ".png";
    }
    
    public function Form($sender, string $txt){
    	$form = new SimpleForm(function (Player $sender, $data = null){
    		if($data === null){
    			return false;
    		}
    		switch($data){
    			case 0:
    			if($sender->hasPermission("kagune.wing") or $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){

    			    $setskin = new setSkin();
    			    $setskin->setSkin($sender, "kagune");
    			  } else {
    			    $this->Form($sender, TextFormat::RED . "You dont have Permission to Use This Wing");
    			  }
    			break;
    			case 1:
    			if($sender->hasPermission("kakuja.wing") or $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){

    			    $setskin = new setSkin();
    			    $setskin->setSkin($sender, "kakuja");
    			  } else {
    			    $this->Form($sender, TextFormat::RED . "You dont have Permission to Use This Wing");
    			  }
    			break;
    			case 2:
    			if($sender->hasPermission("mercy.wing") or $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){

    			    $setskin = new setSkin();
    			    $setskin->setSkin($sender, "mercy");
    			  } else {
    			    $this->Form($sender, TextFormat::RED . "You dont have Permission to Use This Wing");
    			  }
    			break;
    			case 3:
    			if($sender->hasPermission("balrog.wing") or $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){

    			    $setskin = new setSkin();
    			    $setskin->setSkin($sender, "balrog");
    			  } else {
    			    $this->Form($sender, TextFormat::RED . "You dont have Permission to Use This Wing");
    			  }
    			break;
    			case 4:
    			if($sender->hasPermission("blazingelectro.wing") or $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){

    			    $setskin = new setSkin();
    			    $setskin->setSkin($sender, "blazingelectro");
    			  } else {
    			    $this->Form($sender, TextFormat::RED . "You dont have Permission to Use This Wing");
    			  }
    			break;
    			case 5:
    			if($sender->hasPermission("darkaurora.wing") or $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){

    			    $setskin = new setSkin();
    			    $setskin->setSkin($sender, "darkaurora");
    			  } else {
    			    $this->Form($sender, TextFormat::RED . "You dont have Permission to Use This Wing");
    			  }
    			break;
    			case 6:
    			if($sender->hasPermission("davinci.wing") or $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){

    			    $setskin = new setSkin();
    			    $setskin->setSkin($sender, "davinci");
    			  } else {
    			    $this->Form($sender, TextFormat::RED . "You dont have Permission to Use This Wing");
    			  }
    			break;
    			case 7:
    			if($sender->hasPermission("devil.wing") or $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){

    			    $setskin = new setSkin();
    			    $setskin->setSkin($sender, "devil");
    			  } else {
    			    $this->Form($sender, TextFormat::RED . "You dont have Permission to Use This Wing");
    			  }
    			break;
    			case 8:
    			if($sender->hasPermission("diamond.wing") or $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){

    			    $setskin = new setSkin();
    			    $setskin->setSkin($sender, "diamond");
    			  } else {
    			    $this->Form($sender, TextFormat::RED . "You dont have Permission to Use This Wing");
    			  }
    			break;
    			case 9:
    			if($sender->hasPermission("legendarydragonknight.wing") or $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){

    			    $setskin = new setSkin();
    			    $setskin->setSkin($sender, "legendarydragonknight");
    			  } else {
    			    $this->Form($sender, TextFormat::RED . "You dont have Permission to Use This Wing");
    			  }
    			break;
    			case 10:
    			if($sender->hasPermission("monarchbutterfly.wing") or $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){

    			    $setskin = new setSkin();
    			    $setskin->setSkin($sender, "monarchbutterfly");
    			  } else {
    			    $this->Form($sender, TextFormat::RED . "You dont have Permission to Use This Wing");
    			  }
    			break;
    			case 11:
    			if($sender->hasPermission("razor.wing") or $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){

    			    $setskin = new setSkin();
    			    $setskin->setSkin($sender, "razor");
    			  } else {
    			    $this->Form($sender, TextFormat::RED . "You dont have Permission to Use This Wing");
    			  }
    			break;
    			case 12:
    			if($sender->hasPermission("robotic.wing") or $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){

    			    $setskin = new setSkin();
    			    $setskin->setSkin($sender, "robotic");
    			  } else {
    			    $this->Form($sender, TextFormat::RED . "You dont have Permission to Use This Wing");
    			  }
    			break;
    			case 13:
    			  $this->resetSkin($sender);
    			break;
    			case 14:
    			break;
    		}
            return false;
    	});
    	$form->setTitle(TextFormat::RED . "Custom" . TextFormat::WHITE . "Wing");
    	$form->setContent($txt);
    	$form->addButton("§cKagune §4Kaneki");
    	$form->addButton("§0Kakuja §4Kaneki");
    	$form->addButton("§6Mercy §awing");
    	$form->addButton("§cBalrog §awing");
    	$form->addButton("§eBlazing §fElectro §awing");
    	$form->addButton("§5Dark §dPurple §awing");
    	$form->addButton("§6Da§fVinci §awing");
    	$form->addButton("§4Devil §awing");
    	$form->addButton("§bDiamond §awing");
    	$form->addButton("§cLegendary §4Dragon §fKnight");
    	$form->addButton("§bMonarch §cButter§dfly §awing");
    	$form->addButton("§eRazor §awing");
    	$form->addButton("§bRobo§3tic §awing");
    	$form->addButton("Reset Skin");
    	$form->addButton("Exit");
    	$form->sendToPlayer($sender);
    	return $form;
    }
    
    public function resetSkin(Player $player){
      $player->sendMessage("Reset To Original Skin Successfully");
      $reset = new resetSkin();
      $reset->setSkin($player);
    }
    
    public function checkSkin(){
      $available = [];
      $path = $this->getDataFolder() . "skin/";
      if(!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)){
        $this->getLogger()->error("Could not create the bundled wing asset directory.");
        $this->json = 0;
        return;
      }
      foreach(glob($path . "*.json") as $geometryFile){
        $name = pathinfo($geometryFile, PATHINFO_FILENAME);
        if(is_file($path . $name . ".png")){
          $available[$name] = true;
        }
      }
      $this->json = count($available);
    }
    
    public function checkRequirement(){
      if(!extension_loaded("gd")){
        $this->getServer()->getLogger()->info("§6Clothes: Uncomment gd2.dll (remove symbol ';' in ';extension=php_gd2.dll') in bin/php/php.ini to make the plugin working");
        $this->getServer()->getPluginManager()->disablePlugin($this);
      }
      if(!class_exists(SimpleForm::class)){
        $this->getServer()->getLogger()->info("§6Clothes: FormAPI class missing,pls use .phar from poggit!");
        $this->getServer()->getPluginManager()->disablePlugin($this);
        return;
      }
      if (!file_exists($this->getDataFolder() . "steve.png") || !file_exists($this->getDataFolder() . "steve.json") || !file_exists($this->getDataFolder() . "config.yml")) {
            if (file_exists(str_replace("config.yml", "", $this->getResources()["config.yml"]))) {
                $this->recurse_copy(str_replace("config.yml", "", $this->getResources()["config.yml"]), $this->getDataFolder());
            } else {
                $this->getServer()->getLogger()->info("§6Clothes: Something wrong with the resources");
                $this->getServer()->getPluginManager()->disablePlugin($this);
                return;
            }
      }
    }
    
    public function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public static function decodeClientData(string $clientDataJwt): ClientData{
        try{
            [, $clientDataClaims, ] = JwtUtils::parse($clientDataJwt);
        }catch(JwtException $e){
            throw PacketHandlingException::wrap($e);
        }

        $mapper = new \JsonMapper;
        $mapper->bEnforceMapType = false;
        $mapper->bExceptionOnMissingData = true;
        $mapper->bExceptionOnUndefinedProperty = true;
        try{
            $clientData = $mapper->map($clientDataClaims, new ClientData);
        }catch(\JsonMapper_Exception $e){
            throw PacketHandlingException::wrap($e);
        }
        return $clientData;
    }
}
