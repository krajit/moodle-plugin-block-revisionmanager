<?php

$functions = [
    'block_revisionmanager_save_entry' => [
        'classname' => 'block_revisionmanager\external\save_entry',
        'methodname' => 'execute',
        'description' => 'Saves revisionmanager form data',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ],
    'block_revisionmanager_get_entry' => [
        'classname' => 'block_revisionmanager\external\get_entry',
        'methodname' => 'execute',
        'description' => 'Get existing revisionmanager data for a page',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => ''
    ],
    'block_revisionmanager_delete_entry' => [
        'classname'   => 'block_revisionmanager\external\delete_entry',
        'methodname'  => 'delete_entry',
        'description' => 'Deletes a review entry for the current user and page',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => ''
    ],
    'block_revisionmanager_get_read_urls' => [
        'classname'   => 'block_revisionmanager\external\get_read_urls',
        'methodname'  => 'execute',
        'description' => 'Returns read URLs for a user in a course',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => ''
    ]
];