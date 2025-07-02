<?php
namespace block_revisionmanager\external;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/externallib.php');

use external_function_parameters;
use external_value;
use external_single_structure;
use external_api;

class get_nextreview extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'pageid' => new external_value(PARAM_INT, 'Page ID'),
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'chapterid' => new external_value(PARAM_INT, 'Course ID'),
        ]);
    }

    public static function execute($pageid, $courseid, $chapterid) {
           global $USER, $DB;

        require_login($courseid);

        self::validate_parameters(self::execute_parameters(), [
             'pageid' => $pageid,
            'courseid' => $courseid,
            'chapterid' => $chapterid,
        ]);


        // Check if a record exists.
        $record = $DB->get_record('block_revisionmanager_nextreview', [
            'userid' => $USER->id,
            'courseid' => $courseid,
            'pageid' => $pageid,
            'chapterid' => $chapterid,
        ]);


        if (!$record) {
            return [
                'nextreview' => '',
                'status' => 'not found',
            ];
        }

        return [
            'nextreview' => date('Y-m-d', $record->nextreview),
            'status' => 'found'
        ];

    }

    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status'),
            'nextreview' => new external_value(PARAM_TEXT, 'Next review date'),
        ]);
    }
}