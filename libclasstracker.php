<?php
// File: libclasstracker.php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/enrollib.php');

/**
 * Get engagement data grouped by rating values and unread users.
 *
 * @param int $courseid
 * @param int $pageid
 * @param int|null $chapterid
 * @return array
 */
function block_revisionmanager_get_class_engagement_data(int $courseid, int $pageid, ?int $chapterid = null): array {
    global $DB;

    // Step 1: Prepare SQL and parameters.
    if ($chapterid !== null) {
        $ratingsql = "
            SELECT r.*
            FROM {block_revisionmanager_ratings} r
            INNER JOIN (
                SELECT userid, MAX(ratingdate) AS maxdate
                FROM {block_revisionmanager_ratings}
                WHERE courseid = :courseid1 AND pageid = :pageid1 AND chapterid = :chapterid1
                GROUP BY userid
            ) latest ON r.userid = latest.userid AND r.ratingdate = latest.maxdate
            WHERE r.courseid = :courseid2 AND pageid = :pageid2 AND chapterid = :chapterid2
        ";
        $ratingsparams = [
            'courseid1' => $courseid,
            'pageid1' => $pageid,
            'chapterid1' => $chapterid,
            'courseid2' => $courseid,
            'pageid2' => $pageid,
            'chapterid2' => $chapterid,
        ];
    } else {
        $ratingsql = "
            SELECT r.*
            FROM {block_revisionmanager_ratings} r
            INNER JOIN (
                SELECT userid, MAX(ratingdate) AS maxdate
                FROM {block_revisionmanager_ratings}
                WHERE courseid = :courseid1 AND pageid = :pageid1
                GROUP BY userid
            ) latest ON r.userid = latest.userid AND r.ratingdate = latest.maxdate
            WHERE r.courseid = :courseid2 AND pageid = :pageid2
        ";
        $ratingsparams = [
            'courseid1' => $courseid,
            'pageid1' => $pageid,
            'courseid2' => $courseid,
            'pageid2' => $pageid,
        ];
    }

    // Step 2: Execute the query.
    $latestratings = $DB->get_records_sql($ratingsql, $ratingsparams);

    // Step 3: Group users by ratingvalue.
    $groupedUserIds = array_fill(0, 6, []);
    $allRatedUserIds = [];

    foreach ($latestratings as $record) {
        $rating = (int)$record->ratingvalue;
        if ($rating >= 0 && $rating <= 5) {
            $groupedUserIds[$rating][] = $record->userid;
            $allRatedUserIds[] = $record->userid;
        }
    }

    // Step 4: Fetch rated users’ details.
    $allRatedUsers = [];
    if (!empty($allRatedUserIds)) {
        $allRatedUsers = $DB->get_records_list('user', 'id', array_unique($allRatedUserIds));
    }

    // Step 5: Create name/email lists per rating value.
    $studentsByRating = [];
    for ($i = 0; $i <= 5; $i++) {
        $studentsByRating[$i] = [];
        foreach ($groupedUserIds[$i] as $uid) {
            if (isset($allRatedUsers[$uid])) {
                $user = $allRatedUsers[$uid];
                $studentsByRating[$i][] = [
                    'fullname' => fullname($user),
                    'email' => $user->email
                ];
            }
        }
    }

    // Step 6: Get enrolled users.
    $context = context_course::instance($courseid);
    $enrolled = get_enrolled_users($context, '', 0, 'u.id, u.firstname, u.lastname, u.email');

    // Step 7: Identify students who haven't opened the page.
    $notOpened = [];
    foreach ($enrolled as $user) {
        if (!in_array($user->id, $allRatedUserIds)) {
            $notOpened[] = [
                'fullname' => fullname($user),
                'email' => $user->email
            ];
        }
    }

    // Step 8: Return array for Mustache.
    return [
        'num0student' => count($studentsByRating[0]),
        'num1student' => count($studentsByRating[1]),
        'num2student' => count($studentsByRating[2]),
        'num3student' => count($studentsByRating[3]),
        'num4student' => count($studentsByRating[4]),
        'num5student' => count($studentsByRating[5]),
        'numstudentsnotopened' => count($notOpened),
        'numtotalstudent' => count($enrolled),

        'studentdata0' => json_encode($studentsByRating[0]),
        'studentdata1' => json_encode($studentsByRating[1]),
        'studentdata2' => json_encode($studentsByRating[2]),
        'studentdata3' => json_encode($studentsByRating[3]),
        'studentdata4' => json_encode($studentsByRating[4]),
        'studentdata5' => json_encode($studentsByRating[5]),
        'studentdatanotopened' => json_encode($notOpened)
    ];
}
