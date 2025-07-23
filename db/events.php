<?php

defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname' => '\block_revisionmanager\event\rating_saved',
        'callback' => '\block_xp\local\observer\observer::handle_revisionmanager_rating_saved',
        'includefile' => '/blocks/xp/classes/local/observer/observer.php',
        'internal' => false,
    ),
);


