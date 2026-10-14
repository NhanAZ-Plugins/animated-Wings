<?php

namespace pocketmine\event {
    interface Listener {}
}

namespace pocketmine\plugin {
    class PluginBase {
        public function getDataFolder() { return "C:/alias-test/plugin-data/"; }
    }
}

namespace {
    require dirname(__DIR__) . "/src/CustomWing/Main.php";
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

    echo "CustomWing path and invalid skin payload checks passed.\n";
}
