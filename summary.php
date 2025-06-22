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

global $OUTPUT;

$url = new moodle_url('/blocks/ajaxforms/summary.php', []);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('pluginname', 'block_ajaxforms'));
$PAGE->set_heading(get_string('summary', 'block_ajaxforms'));

require_login();

if (isguestuser()) {
    throw new moodle_exception('noguest');
}

echo $OUTPUT->header();


$userfields = \core_user\fields::for_name()->with_identity($context);
$userfieldssql = $userfields->get_sql('u');

$table = new block_ajaxforms\pageslist($USER->id);

// $table->set_sql("m.id, m.courseid, m.timemodified, m.userid, m.pageurl, m.nextreview {$userfieldssql->selects}",
//     "{block_ajaxforms_entries} m LEFT JOIN {user} u ON u.id = m.userid" ,
//     true);

$table->set_sql("m.id, m.courseid, m.timemodified, m.userid, m.pageurl, m.nextreview,
     c.shortname AS coursename {$userfieldssql->selects}",
    "{block_ajaxforms_entries} m
     LEFT JOIN {user} u ON u.id = m.userid
     LEFT JOIN {course} c ON c.id = m.courseid",
     true);

$table->sortable(true, 'nextreview', SORT_DESC);
$table->define_baseurl("$CFG->wwwroot/local/ajaxforms/summary.php");
$table->out(40, true);

echo $OUTPUT->footer();