<?php
namespace block_ajaxforms\external;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/externallib.php');

use external_function_parameters;
use external_value;
use external_single_structure;
use external_api;

class delete_entry extends external_api {

    public static function delete_entry_parameters() {
        return new external_function_parameters([
            'pageurl' => new external_value(PARAM_TEXT),
            'courseid' => new external_value(PARAM_INT)
        ]);
    }

    public static function delete_entry($pageurl, $courseid) {
        global $DB, $USER;

        self::validate_parameters(self::delete_entry_parameters(), [
            'pageurl' => $pageurl,
            'courseid' => $courseid
        ]);

        $DB->delete_records('block_ajaxforms_entries', [
            'userid' => $USER->id,
            'courseid' => $courseid,
            'pageurl' => $pageurl
        ]);

        return ['status' => 'deleted'];
    }

    public static function delete_entry_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT)
        ]);
    }
}