<?php
namespace block_revisionmanager\external;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/externallib.php');

use external_function_parameters;
use external_value;
use external_single_structure;
use external_api;

class save_nextreview extends external_api {
    public static function save_nextreview_parameters() {
        return new external_function_parameters([
            'pageid' => new external_value(PARAM_INT),
            'courseid' => new external_value(PARAM_INT),
            'nextreview' => new external_value(PARAM_TEXT, 'Next review date (Y-m-d)'),
            'pageurl' => new external_value(PARAM_RAW, 'Page URL'),
            'chapterid' => new external_value(PARAM_INT, 'Chapter Id'),
        ]);
    }

    public static function save_nextreview($pageid, $courseid, $nextreview,
        $pageurl, $chapterid) {
        global $DB, $USER;

        $record = new \stdClass();
        $record->userid = $USER->id;
        $record->courseid = $courseid;
        $record->pageid = $pageid;
        $record->timemodified = time();
        $record->pageurl = $pageurl;
        $record->nextreview = strtotime($nextreview);
        $record->chapterid = $chapterid;

        // Check if a record already exists.
        $existing = $DB->get_record('block_revisionmanager_nextreview', [
            'userid' => $USER->id,
            'courseid' => $courseid,
            'pageid' => $pageid,
            'chapterid' => $chapterid,
        ]);

        if ($existing) {
            $record->id = $existing->id; // ✅ Ensure ID is set before update
            $DB->update_record('block_revisionmanager_nextreview', $record);
        } else {
            $DB->insert_record('block_revisionmanager_nextreview', $record);
        }
        return ['status' => 'success'];
    }

    public static function save_nextreview_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT),
        ]);
    }


}