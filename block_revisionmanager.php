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
 * Block revisionmanager is defined here.
 *
 * @package     block_revisionmanager
 * @copyright   2025 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/libclasstracker.php');
class block_revisionmanager extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_revisionmanager');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $OUTPUT, $PAGE, $COURSE, $DB;

        // Block only visible on mod_lesson view.php pages.
        // TODO: Extend this block to be visible on other activity pages
        if (($PAGE->cm->modname !== 'page') && ($PAGE->cm->modname !== 'book')) {
            return null;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = [];
        $this->content->icons = [];
        $this->content->footer = '';

         // Add a footer gear icon linking to course plugin page.
        $url = new moodle_url('/blocks/revisionmanager/summary.php', ['courseid' => $COURSE->id]);

        // $gearicon = $OUTPUT->pix_icon('i/settings', get_string('settings'));
        // $this->content->footer = html_writer::link($url, "Course Review Dashboard", []);

        $chapterid = null;
        // get default chapterid
        if (optional_param('chapterid', 0, PARAM_INT)) {
            $chapterid = optional_param('chapterid', 0, PARAM_INT);
        } else if ($PAGE->cm->modname === 'book') {
                // No chapter specified, get first chapter
                $cm = get_coursemodule_from_id('book', $PAGE->cm->id, 0, false, MUST_EXIST);
                $book = $DB->get_record('book', ['id' => $cm->instance], '*', MUST_EXIST);
                $chapters = $DB->get_records('book_chapters', ['bookid' => $book->id], 'pagenum ASC');
                $firstchapter = reset($chapters);
                $chapterid = $firstchapter->id;
        }

        

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {

            $text = $OUTPUT->render_from_template('block_revisionmanager/learningtracker',['dashboardurl'=>$url]);
            $pageid = $PAGE->cm->id;
            $classdata = block_revisionmanager_get_class_engagement_data($COURSE->id, $pageid, $chapterid);
           
            $text .= $OUTPUT->render_from_template('block_revisionmanager/classtracker',$classdata);
            $this->content->text = $text;
        }

        $params = [
            'courseid' => $COURSE->id,
            'pagetitle' => $PAGE->title,
            'pageid' => $PAGE->cm->id,
            'chapterid' =>$chapterid            
        ];
       

        $PAGE->requires->js_call_amd('block_revisionmanager/databasecommunicator', 'init', [$params]);
        $PAGE->requires->js_call_amd('block_revisionmanager/boooktocmarker', 'init', [$params]);
        $PAGE->requires->css('/blocks/revisionmanager/styles.css');

        $PAGE->requires->js_call_amd('block_revisionmanager/classEngagement', 'init', []);
        
        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_revisionmanager');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return [
            '' => true,
        ];
    }
}
