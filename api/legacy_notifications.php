<?php

# Begin standard auth
require "../classes/_autoload.php";

$system = new System();
$system->is_api_request = true;

try {
    $player = Auth::getUserFromSession($system);
} catch(Exception $e) {
    API::exitWithError($e->getMessage());
}
# End standard auth

$player->loadData(User::UPDATE_NOTHING);

Notifications::displayNotifications($system, $player, true);
