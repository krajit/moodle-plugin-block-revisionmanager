<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin version and other meta-data are defined here.
 *
 * @package     block_ajaxforms
 * @copyright   2025 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function block_ajaxforms_extend_navigation_frontpage(navigation_node $frontpage) {
    if (isloggedin() && !isguestuser()) {
        $frontpage->add(
            get_string('navigationlabel', 'block_ajaxforms'),
            new moodle_url('/blocks/ajaxforms/summary.php'),
            navigation_node::TYPE_CUSTOM,
        );
    }
}

function block_ajaxforms_extend_navigation_course(navigation_node $coursenode, stdClass $course, context_course $context) {
    if (isloggedin() && !isguestuser()) {
        $coursenode->add(
            get_string('navigationlabel', 'block_ajaxforms'),
            new moodle_url('/blocks/ajaxforms/summary.php', ['courseid' => $course->id]),
            navigation_node::TYPE_CUSTOM
        );
    }
}