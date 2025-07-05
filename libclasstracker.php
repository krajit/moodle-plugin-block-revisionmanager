<?php
/**
 * Get rating distribution for a specific course + page + optional chapter.
 *
 * @param int $courseid
 * @param int $pageid
 * @param int|null $chapterid Optional chapter ID (for mod_book pages)
 * @return array
 */
function block_revisionmanager_get_class_engagement_data(int $courseid, int $pageid, ?int $chapterid = null): array {
    global $DB;

    // 1. Total enrolled students.
    $numtotalstudent = $DB->count_records_sql("
        SELECT COUNT(DISTINCT ue.userid)
        FROM {user_enrolments} ue
        JOIN {enrol} e ON ue.enrolid = e.id
        WHERE e.courseid = ?", [$courseid]);

    // 2. Unique parameter names for subquery and outer query
    $where_sub = "courseid = :courseid1 AND pageid = :pageid1";
    $where_main = "r.courseid = :courseid2 AND r.pageid = :pageid2";

    $params = [
        'courseid1' => $courseid,
        'pageid1' => $pageid,
        'courseid2' => $courseid,
        'pageid2' => $pageid
    ];

    if (!is_null($chapterid)) {
        $where_sub .= " AND chapterid = :chapterid1";
        $where_main .= " AND r.chapterid = :chapterid2";
        $params['chapterid1'] = $chapterid;
        $params['chapterid2'] = $chapterid;
    }

    $ratingsql = "
        SELECT r.userid, r.ratingvalue
        FROM {block_revisionmanager_ratings} r
        JOIN (
            SELECT userid, MAX(ratingdate) AS maxdate
            FROM {block_revisionmanager_ratings}
            WHERE $where_sub
            GROUP BY userid
        ) latest ON latest.userid = r.userid AND latest.maxdate = r.ratingdate
        WHERE $where_main
    ";

    $latestratings = $DB->get_records_sql($ratingsql, $params);

    // 3. Count by rating buckets.
    $counters = array_fill(0, 6, 0);
    foreach ($latestratings as $record) {
        $val = (int)$record->ratingvalue;
        if ($val >= 0 && $val <= 5) {
            $counters[$val]++;
        }
    }

    $numstudentsnotopened = $numtotalstudent - $counters[0] - $counters[1] - $counters[2] - $counters[3] - $counters[4] - $counters[5];

    return [
        'numstudentsnotopened' => $numstudentsnotopened,
        'num0student' => $counters[0],
        'num1student' => $counters[1],
        'num2student' => $counters[2],
        'num3student' => $counters[3],
        'num4student' => $counters[4],
        'num5student' => $counters[5],
        'numtotalstudent' => $numtotalstudent
    ];
}
