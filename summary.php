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
 * All messages report
 *
 * @package    block_ajaxforms
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
global $OUTPUT, $DB;

// Determine course context (if any).
$courseid = optional_param('courseid', 0, PARAM_INT);
$course = null;
$coursecontext = null;
$inacourse = false;

if ($courseid > 0 && ($course = $DB->get_record('course', ['id' => $courseid], '*', IGNORE_MISSING))) {
    $coursecontext = context_course::instance($course->id);
    $PAGE->set_context($coursecontext);
    $PAGE->set_pagelayout('incourse');
    $PAGE->set_heading(format_string($course->fullname, true, ['context' => $coursecontext]));
    $inacourse = true;
} else {
    $PAGE->set_context(context_system::instance());
    $PAGE->set_pagelayout('report');
    $PAGE->set_heading(get_string('summary', 'block_ajaxforms'));
}

$PAGE->set_url(new moodle_url('/blocks/ajaxforms/summary.php', ['courseid' => $courseid]));
$PAGE->set_title(get_string('pluginname', 'block_ajaxforms'));

if ($inacourse) {
    navigation_node::override_active_url(
        new moodle_url('/course/view.php', ['id' => $course->id])
    );
}

require_login();
$filtercourseid = optional_param('courseid', 0, PARAM_INT);


if (isguestuser()) {
    throw new moodle_exception('noguest');
}

// $homenode = $PAGE->navigation->add(
//     get_string('pluginname', 'block_ajaxforms'),
//     new moodle_url('/blocks/ajaxforms/summary.php')
// );

// $allmessagesnode = $homenode->add(
//    get_string('summary', 'block_ajaxforms'),
//    $url
// );

// $allmessagesnode->make_active();

echo $OUTPUT->header();


$userfields = \core_user\fields::for_name()->with_identity($context);
$userfieldssql = $userfields->get_sql('u');

$table = new block_ajaxforms\pageslist($USER->id);

// $table->set_sql("m.id, m.courseid, m.timemodified, m.userid, m.pageurl, m.nextreview {$userfieldssql->selects}",
//     "{block_ajaxforms_entries} m LEFT JOIN {user} u ON u.id = m.userid" ,
//     true);

$userid = (int) $USER->id;  // Always cast to int for safety
$where = "m.userid = $userid";
if ($filtercourseid > 0) {
    $where .= " AND m.courseid = $filtercourseid";
}

$table->set_sql("m.id, m.courseid, m.timemodified, m.userid, m.pageurl, m.nextreview, m.pagetitle,
     c.shortname AS coursename {$userfieldssql->selects}",
    "{block_ajaxforms_entries} m
     LEFT JOIN {user} u ON u.id = m.userid
     LEFT JOIN {course} c ON c.id = m.courseid",
     $where
    );

$table->sortable(true, 'nextreview', SORT_DESC);
//$table->define_baseurl("$CFG->wwwroot/blocks/ajaxforms/summary.php");
$table->define_baseurl(new moodle_url('/blocks/ajaxforms/summary.php', ['courseid' => $filtercourseid]));
$table->out(40, true);

echo $OUTPUT->footer();