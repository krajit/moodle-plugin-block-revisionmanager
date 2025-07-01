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
            'pageurl' => new external_value(PARAM_RAW, 'Page URL'),
            'nextreview' => new external_value(PARAM_TEXT, 'Next review date (Y-m-d)'),
            'pagetitle' => new external_value(PARAM_TEXT, 'Page Title'),
            'chapterid' => new external_value(PARAM_INT, 'Chapter Id'),
            'ratingkey' => new external_value(PARAM_INT, 'Existing rating id to update (optional)', VALUE_DEFAULT, 0)
        ]);
    }

    public static function save_rating($courseid, $pageid, $ratingvalue, $ratingdate,
        $pageurl, $nextreview, $pagetitle, $chapterid, $ratingkey = 0) {
        global $DB, $USER;

        $record = new \stdClass();
        $record->userid = $USER->id;
        $record->courseid = $courseid;
        $record->pageid = $pageid;
        $record->ratingvalue = $ratingvalue;
        $record->ratingdate = $ratingdate;
        $record->timemodified = time();
        $record->pageurl = $pageurl;
        $record->nextreview = $nextreview;
        $record->pagetitle = $pagetitle;
        $record->chapterid = $chapterid;

        if ($ratingkey > 0 && $DB->record_exists('block_revisionmanager_ratings', ['id' => $ratingkey, 'userid' => $USER->id])) {
            $record->id = $ratingkey;
            $DB->update_record('block_revisionmanager_ratings', $record);
        } else {
            $ratingkey = $DB->insert_record('block_revisionmanager_ratings', $record);
        }

        return ['status' => 'success', 'ratingkey' => $ratingkey];
    }

    public static function save_rating_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT),
            'ratingkey' => new external_value(PARAM_INT, 'The id of the saved record')
        ]);
    }

    public static function get_ratings_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
            'pageid' => new external_value(PARAM_INT),
            'chapterid' => new external_value(PARAM_INT),
        ]);
    }

    public static function get_ratings($courseid, $pageid, $chapterid) {
        global $DB, $USER;

        $records = $DB->get_records('block_revisionmanager_ratings', [
            'userid' => $USER->id,
            'courseid' => $courseid,
            'pageid' => $pageid,
            'chapterid' => $chapterid,
        ], 'ratingdate ASC');

        $result = [];
        foreach ($records as $r) {
            $result[] = [
                'ratingkey' => $r->id,
                'ratingdate' => $r->ratingdate,
                'ratingvalue' => $r->ratingvalue
            ];
        }

        return $result;
    }

    public static function get_ratings_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'ratingkey' => new external_value(PARAM_INT, 'Primary key of rating'),
                'ratingdate' => new external_value(PARAM_INT),
                'ratingvalue' => new external_value(PARAM_INT),
            ])
        );
    }
}
