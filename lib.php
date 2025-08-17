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
 * @package     block_revisionmanager
 * @copyright   2025 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function block_revisionmanager_extend_navigation_frontpage(navigation_node $frontpage) {
    global $DB, $USER;
    // Check if the block is present in the course.
    $hasblock = $DB->record_exists('block_instances', [
        'blockname' => 'revisionmanager',
    ]);
    if (isloggedin() && !isguestuser() && $hasblock) {
        $frontpage->add(
            get_string('navigationlabel', 'block_revisionmanager'),
            new moodle_url('/blocks/revisionmanager/summary.php'),
            navigation_node::TYPE_CUSTOM,
        );
    }
}

function block_revisionmanager_extend_navigation_course(navigation_node $coursenode, stdClass $course, context_course $context) {
    global $DB, $USER;

    // Check if the block is present in the course.
    $hasblock = $DB->record_exists('block_instances', [
        'blockname' => 'revisionmanager',
        'parentcontextid' => $context->id,
    ]);
    
    if ($hasblock && isloggedin() && !isguestuser()) {
        $url = new moodle_url('/blocks/revisionmanager/summary.php', ['courseid' => $course->id]);
        $coursenode->add(
            get_string('navigationlabel', 'block_revisionmanager'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            null,
            new pix_icon('i/report', '') // optional icon
        );

        // add block for course class performance
        $url = new moodle_url('/blocks/revisionmanager/bookchapters.php', ['courseid' => $course->id]);
        $coursenode->add(
            get_string('classpartipationlabel', 'block_revisionmanager'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            null,
            new pix_icon('i/report', '') // optional icon
        );


    }
}