<?php

$functions = [
    'block_ajaxforms_save_entry' => [
        'classname' => 'block_ajaxforms\external\save_entry',
        'methodname' => 'execute',
        'description' => 'Saves ajaxforms form data',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ],
    'block_ajaxforms_get_entry' => [
        'classname' => 'block_ajaxforms\external\get_entry',
        'methodname' => 'execute',
        'description' => 'Get existing ajaxforms data for a page',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => ''
    ],
];