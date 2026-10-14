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

namespace {
    require dirname(__DIR__) . "/src/CustomWing/Main.php";
    require dirname(__DIR__) . "/src/CustomWing/WingMenu.php";
    require dirname(__DIR__) . "/src/CustomWing/saveSkin.php";

    function check($condition, $message) {
        if(!$condition) { throw new \RuntimeException($message); }
    }

    $plugin = new \CustomWing\Main();
    \CustomWing\Main::$instance = $plugin;
    $traversalName = "../../outside\\folder";
    $path = $plugin->getSkinPath($traversalName);
    check(is_string($path), "Non-empty client names should map to safe generated paths.");
    check(strpos($path, "..") === false && strpos($path, "outside") === false && strpos($path, "\\folder") === false, "Client-supplied path text must not enter a filename.");
    check($path === $plugin->getSkinPath(strtoupper($traversalName)), "Case variants should map consistently.");
    check($path !== $plugin->getSkinPath("another-player"), "Different players should receive separate filenames.");
    check($plugin->getSkinPath("") === false, "Empty client names must be rejected.");
    check($plugin->getSkinPath(str_repeat("a", 257)) === false, "Overlong client names must be rejected.");
    check($plugin->getSkinPath("bad\nname") === false, "Control characters in client names must be rejected.");
    check((new \CustomWing\saveSkin())->skinDataToImage("invalid") === null, "Unsupported skin payload lengths must be rejected before image conversion.");

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
