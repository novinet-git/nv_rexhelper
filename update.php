<?php

$addon = rex_addon::get("nv_rexhelper");

$currentVersion = $addon->getVersion();
$minVersionRequired = "2.0.0";

if (rex_string::versionCompare($currentVersion, $minVersionRequired, "<")) {
    $message = "Version " . $currentVersion . " kann nicht auf Version " . $minVersionRequired . " aktualisiert werden.";
    throw new rex_functional_exception($message);
}