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
        $record->nextreview = $nextreview;
        $record->pagetitle = $pagetitle;
        $record->chapterid = $chapterid;

        if ($DB->record_exists('block_revisionmanager_nextreviewdate', 
                ['userid' => $USER->id, 'pageid' =>$pageid, 'courseid' => $courseid, 'chapterid'=>$chapterid])) {
            $DB->update_record('block_revisionmanager_ratings', $record);
        } else {
            $ratingkey = $DB->insert_record('block_revisionmanager_ratings', $record);
        }

        return ['status' => 'success'];
    }

    public static function save_nextreview_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT),
        ]);
    }
