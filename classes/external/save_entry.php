<?php
namespace block_ajaxforms\external;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/externallib.php');

use external_function_parameters;
use external_value;
use external_single_structure;
use external_api;

class save_entry extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'nextreview' => new external_value(PARAM_TEXT, 'Next review date (Y-m-d)'),
            'pageurl' => new external_value(PARAM_RAW, 'Page URL'),
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
        ]);
    }

    public static function execute($nextreview, $pageurl,$courseid) {
        error_log("DEBUG: save_entry called with $nextreview, $pageurl");

        global $USER, $DB;

        require_login($courseid);

        self::validate_parameters(self::execute_parameters(), [
            'nextreview' => $nextreview,
            'pageurl' => $pageurl,
            'courseid' => $courseid
        ]);

        $timestamp = strtotime($nextreview);

        $existing = $DB->get_record('block_ajaxforms_entries', ['userid' => $USER->id, 'pageurl' => $pageurl, 'courseid' => $courseid]);

        $record = new \stdClass();
        $record->userid = $USER->id;
        $record->courseid = $courseid;
        $record->nextreview = $timestamp;
        $record->pageurl = $pageurl;
        $record->timemodified = time();

        if ($existing) {
            $record->id = $existing->id;
            $DB->update_record('block_ajaxforms_entries', $record);
        } else {
            $DB->insert_record('block_ajaxforms_entries', $record);
        }

        return ['status' => 'saved'];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status')
        ]);
    }
}