<?php

namespace block_revisionmanager\event;

defined('MOODLE_INTERNAL') || die();

use core\event\base;

class rating_saved extends base {

    protected function init() {
        $this->data['objecttable'] = 'block_revisionmanager_ratings';
        $this->data['crud'] = 'c'; // Create or Update
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    public function get_description() {
        return 'A rating has been saved in the revision manager.';
    }

    public static function get_name() {
        return get_string('event_rating_saved', 'block_revisionmanager');
    }

    public function get_url() {
        return new moodle_url('/blocks/revisionmanager/summary.php', ['courseid' => $this->courseid]);
    }
}


