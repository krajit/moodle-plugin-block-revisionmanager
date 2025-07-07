<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Summary table page for AJAX Forms block.
 *
 * @package   block_revisionmanager
 * @copyright 2024 YOUR NAME
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

use block_revisionmanager\pageslist;

global $DB, $PAGE, $OUTPUT, $USER;

// Get course ID (optional) and retrieve course.
$courseid = optional_param('courseid', 0, PARAM_INT);
$course = $courseid ? $DB->get_record('course', ['id' => $courseid], '*', IGNORE_MISSING) : null;

// Determine context and layout.
if ($course) {
    $coursecontext = context_course::instance($course->id);
    $PAGE->set_context($coursecontext);
     $PAGE->set_course($course);
    $PAGE->set_pagelayout('incourse');
    $PAGE->set_heading(format_string($course->fullname, true, ['context' => $coursecontext]));
    navigation_node::override_active_url(new moodle_url('/course/view.php', ['id' => $course->id]));
} else {
    $PAGE->set_context(context_system::instance());
    $PAGE->set_pagelayout('report');
//    $PAGE->set_heading(get_string('summary', 'block_revisionmanager'));
}

// Page URL and metadata.
$PAGE->set_url(new moodle_url('/blocks/revisionmanager/summary.php', ['courseid' => $courseid]));
$PAGE->set_title(get_string('pluginname', 'block_revisionmanager'));

require_login();
if (isguestuser()) {
    throw new moodle_exception('noguest');
}

// Output starts.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('summary', 'block_revisionmanager'));

// Get user fields for display.
$userfields = \core_user\fields::for_name()->with_identity($PAGE->context);
$userfieldssql = $userfields->get_sql('u');

// Build table object.
$table = new pageslist($USER->id);

$userid = (int)$USER->id;
$where = "m.userid = :userid";
$params = ['userid' => $userid];

if ($course) {
    $where .= " AND m.courseid = :courseid";
    $params['courseid'] = $course->id;
}

// // Define SQL and render table.
// $table->set_sql(
//     "m.id, m.courseid, m.timemodified, m.userid, m.pageurl, n.nextreview, m.pagetitle,
//     m.ratingvalue, m.ratingdate,
//      c.shortname AS coursename {$userfieldssql->selects}",
//     "{block_revisionmanager_ratings} m
//      LEFT JOIN {user} u ON u.id = m.userid
//      LEFT JOIN {course} c ON c.id = m.courseid
//      JOIN {block_revisionmanager_nextreview} n
//       ON m.userid = n.userid
//         AND m.courseid = n.courseid
//         AND m.pageid = n.pageid",
//     $where,
//     $params
// );

$table->set_sql(
    "m.id, m.courseid, m.timemodified, m.userid, m.pageurl, n.nextreview, m.pagetitle,
    m.ratingvalue, m.ratingdate,
    c.shortname AS coursename {$userfieldssql->selects}",
    "{block_revisionmanager_ratings} m
     INNER JOIN (
         SELECT userid, courseid, pageid, chapterid, MAX(ratingdate) AS max_ratingdate
         FROM {block_revisionmanager_ratings}
         GROUP BY userid, courseid, pageid, chapterid
     ) latest ON
         latest.userid = m.userid AND
         latest.courseid = m.courseid AND
         latest.pageid = m.pageid AND
         latest.chapterid = m.chapterid AND
         latest.max_ratingdate = m.ratingdate
     LEFT JOIN {user} u ON u.id = m.userid
     LEFT JOIN {course} c ON c.id = m.courseid
     JOIN {block_revisionmanager_nextreview} n
         ON m.userid = n.userid
         AND m.courseid = n.courseid
         AND m.pageid = n.pageid
         AND m.chapterid = n.chapterid",
    $where,
    $params
);



$table->sortable(true, 'nextreview', SORT_DESC);
$table->define_baseurl($PAGE->url);
$table->out(40, true);

// Output footer.
echo $OUTPUT->footer();
