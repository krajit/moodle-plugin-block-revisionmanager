<?php
namespace block_revisionmanager\external;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/externallib.php');

use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;
use external_api;
use context_system;

class save_rating extends external_api {

    public static function save_rating_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
            'pageid' => new external_value(PARAM_INT),
            'ratingvalue' => new external_value(PARAM_INT),
            'ratingdate' => new external_value(PARAM_INT),
        ]);
    }

    public static function save_rating($courseid, $pageid, $ratingvalue, $ratingdate) {
        global $DB, $USER;

        $record = new \stdClass();
        $record->userid = $USER->id;
        $record->courseid = $courseid;
        $record->pageid = $pageid;
        $record->ratingvalue = $ratingvalue;
        $record->ratingdate = $ratingdate;
        $record->timemodified = time();

        $DB->insert_record('block_revisionmanager_ratings', $record);

        return ['status' => 'success'];
    }

    public static function save_rating_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT),
        ]);
    }

    public static function get_ratings_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
            'pageid' => new external_value(PARAM_INT),
        ]);
    }

    public static function get_ratings($courseid, $pageid) {
        global $DB, $USER;

        $records = $DB->get_records('block_revisionmanager_ratings', [
            'userid' => $USER->id,
            'courseid' => $courseid,
            'pageid' => $pageid
        ], 'ratingdate ASC');

        $result = [];
        foreach ($records as $r) {
            $result[] = [
                'ratingdate' => $r->ratingdate,
                'ratingvalue' => $r->ratingvalue
            ];
        }

        return $result;
    }

    public static function get_ratings_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'ratingdate' => new external_value(PARAM_INT),
                'ratingvalue' => new external_value(PARAM_INT),
            ])
        );
    }
}
