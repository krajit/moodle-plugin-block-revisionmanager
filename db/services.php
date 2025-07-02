<?php

$functions = [
    'block_revisionmanager_save_rating' => [
        'classname' => 'block_revisionmanager\external\save_rating',
        'methodname' => 'save_rating',
        'description' => 'Save rating for a page',
        'type' => 'write',
        'ajax' => true,
    ],
    'block_revisionmanager_get_ratings' => [
        'classname' => 'block_revisionmanager\external\save_rating',
        'methodname' => 'get_ratings',
        'description' => 'Get all ratings for a page',
        'type' => 'read',
        'ajax' => true,
    ],
    'block_revisionmanager_get_read_urls' => [
        'classname'   => 'block_revisionmanager\external\get_read_urls',
        'methodname'  => 'execute',
        'description' => 'Returns read URLs for a user in a course',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => ''
    ],
    'block_revisionmanager_save_nextreview' => [
        'classname' => 'block_revisionmanager\external\save_nextreview',
        'methodname' => 'save_nextreview',
        'description' => 'Save next review date for a page',
        'type' => 'write',
        'ajax' => true,

    ],
    'block_revisionmanager_get_nextreview' => [
        'classname' => 'block_revisionmanager\external\get_nextreview',
        'methodname' => 'execute',
        'description' => 'extract review date for a page',
        'type' => 'read',
        'ajax' => true,
    ],
];