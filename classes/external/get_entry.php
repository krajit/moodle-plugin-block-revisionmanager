<?php
namespace block_revisionmanager\external;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/externallib.php');

use external_function_parameters;
use external_value;
use external_single_structure;
use external_api;

class get_entry extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'pageurl' => new external_value(PARAM_RAW, 'Page URL'),
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
        ]);
    }

    public static function execute($pageurl,$courseid) {
        error_log("DEBUG: get_entry called with $pageurl, $courseid");

        global $USER, $DB;

        require_login($courseid);

        self::validate_parameters(self::execute_parameters(), [
            'pageurl' => $pageurl,
            'courseid' => $courseid
        ]);


        $record = $DB->get_record('block_revisionmanager_entries', ['userid' => $USER->id, 'pageurl' => $pageurl, 'courseid' => $courseid]);

        if (!$record) {
            return [
                'nextreview' => '',
                'status' => 'not found',
                'learninglevel' => "Not Started", // TODO This should not be hardcoded
                'revisioncount' => 0, // TODO This should not be hardcoded
                'targetcount' => 10, // TODO This should not be hardcoded
            ];
        }

        return [
            'nextreview' => date('Y-m-d', $record->nextreview),
            'status' => 'found',
            'learninglevel' => $record->learninglevel, 
            'revisioncount' => $record->revisioncount,
            'targetcount' => $record->targetcount,
        ];

    }

    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status'),
            'nextreview' => new external_value(PARAM_TEXT, 'Next review date'),
            'learninglevel' => new external_value(PARAM_TEXT, 'Learning Level'),
            'revisioncount' => new external_value(PARAM_INT, 'Revision Count'),
            'targetcount' => new external_value(PARAM_INT, 'Target Count'),

        ]);
    }
}