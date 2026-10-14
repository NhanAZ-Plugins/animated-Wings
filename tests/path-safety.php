<?php

namespace pocketmine\event {
    interface Listener {}
}

namespace pocketmine\plugin {
    class PluginBase {
        public function getDataFolder() { return "C:/alias-test/plugin-data/"; }
    }
}

namespace pocketmine\form {
    interface Form extends \JsonSerializable {
        public function handleResponse(\pocketmine\player\Player $player, $data): void;
    }

    class FormValidationException extends \RuntimeException {}
}

namespace pocketmine\player {
    class Player {}
}

namespace pocketmine\network\mcpe {
    class JwtException extends \RuntimeException {}

    class JwtUtils {
        /** @var array<string, mixed> */
        public static array $claims = [];

        /** @return array{string, array<string, mixed>, string} */
        public static function parse(string $token): array {
            return ["header", self::$claims, "signature"];
        }
    }
}

namespace pocketmine\network {
    class PacketHandlingException extends \RuntimeException {
        public static function wrap(\Throwable $exception): self {
            return new self($exception->getMessage(), 0, $exception);
        }
    }
}

namespace {
    require dirname(__DIR__) . "/src/CustomWing/Main.php";
    require dirname(__DIR__) . "/src/CustomWing/WingMenu.php";
    require dirname(__DIR__) . "/src/CustomWing/saveSkin.php";

    function check($condition, $message) {
        if(!$condition) { throw new \RuntimeException($message); }
    }

    $plugin = new \CustomWing\Main();
    $traversalName = "../../outside\\folder";
    $path = $plugin->getSkinPath($traversalName);
    check(is_string($path), "Non-empty client names should map to safe generated paths.");
    check(strpos($path, "..") === false && strpos($path, "outside") === false && strpos($path, "\\folder") === false, "Client-supplied path text must not enter a filename.");
    check($path === $plugin->getSkinPath(strtoupper($traversalName)), "Case variants should map consistently.");
    check($path !== $plugin->getSkinPath("another-player"), "Different players should receive separate filenames.");
    check($plugin->getSkinPath("") === false, "Empty client names must be rejected.");
    check($plugin->getSkinPath(str_repeat("a", 257)) === false, "Overlong client names must be rejected.");
    check($plugin->getSkinPath("bad\nname") === false, "Control characters in client names must be rejected.");
    check((new \CustomWing\saveSkin($plugin))->skinDataToImage("invalid") === null, "Unsupported skin payload lengths must be rejected before image conversion.");
    if (extension_loaded("gd")) {
        foreach ([[64, 32], [64, 64], [128, 128], [128, 256]] as [$width, $height]) {
            $image = (new \CustomWing\saveSkin($plugin))->skinDataToImage(str_repeat("\xff\x00\x00\xff", $width * $height));
            check($image instanceof \GdImage, "Supported skin dimensions should convert to an image.");
            check(imagesx($image) === $width && imagesy($image) === $height, "Skin image dimensions should be preserved.");
            imagedestroy($image);
        }
    }

    \pocketmine\network\mcpe\JwtUtils::$claims = ["ThirdPartyName" => "Player", "PersonaSkin" => false, "SkinData" => "encoded"];
    check(\CustomWing\Main::decodeClientData("valid") === ["playerName" => "Player", "personaSkin" => false, "skinData" => "encoded"], "Validated login skin claims should be returned as typed data.");
    \pocketmine\network\mcpe\JwtUtils::$claims = ["ThirdPartyName" => 12, "PersonaSkin" => "false", "SkinData" => []];
    try {
        \CustomWing\Main::decodeClientData("invalid");
        check(false, "Malformed skin claims must be rejected.");
    } catch (\pocketmine\network\PacketHandlingException) {
    }

    $selections = [];
    $menu = new \CustomWing\WingMenu("Wings", "Select one", ["Kagune", "Exit"], static function (\pocketmine\player\Player $player, int $selection) use (&$selections): void {
        $selections[] = $selection;
    });
    $encodedMenu = $menu->jsonSerialize();
    check($encodedMenu["type"] === "form" && count($encodedMenu["buttons"]) === 2, "The built-in API 5 menu must serialize a simple form.");
    check($encodedMenu["buttons"][0]["text"] === "Kagune", "Form button labels must remain visible in the serialized response.");
    $menu->handleResponse(new \pocketmine\player\Player(), null);
    check($selections === [], "Closing the form must not trigger a selection.");
    $menu->handleResponse(new \pocketmine\player\Player(), 0);
    check($selections === [0], "A valid menu response must reach the selection handler.");
    try {
        $menu->handleResponse(new \pocketmine\player\Player(), 2);
        check(false, "Out-of-range form responses must be rejected.");
    } catch (\pocketmine\form\FormValidationException) {
    }

    echo "CustomWing API 5 menu, path safety and invalid skin payload checks passed.\n";
}
