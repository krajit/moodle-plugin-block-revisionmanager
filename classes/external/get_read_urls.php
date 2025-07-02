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
use stdClass;

class get_read_urls extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT)
        ]);
    }

    public static function execute($courseid) {
        global $DB, $USER;

        self::validate_parameters(self::execute_parameters(), ['courseid' => $courseid]);

        $records = $DB->get_records('block_revisionmanager_ratings', [
            'userid' => $USER->id,
            'courseid' => $courseid
        ]);

        // Group all rating values by pageurl
        $urlmap = [];
        foreach ($records as $r) {
            $url = $r->pageurl;
            if (!isset($urlmap[$url])) {
                $urlmap[$url] = [];
            }
            $urlmap[$url][] = $r->ratingvalue;
        }

        // Format for return
        $results = [];
        foreach ($urlmap as $pageurl => $ratings) {
            $results[] = [
                'pageurl' => $pageurl,
                'ratingvalues' => $ratings
            ];
        }

        return ['entries' => $results];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'entries' => new external_multiple_structure(
                new external_single_structure([
                    'pageurl' => new external_value(PARAM_RAW),
                    'ratingvalues' => new external_multiple_structure(
                        new external_value(PARAM_INT)
                    )
                ])
            )
        ]);
    }
}
