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
        global $OUTPUT, $PAGE, $COURSE;

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

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            $text = $OUTPUT->render_from_template('block_revisionmanager/learningtracker',
            ['dashboardurl'=>$url]);
            $this->content->text = $text;
        }

        $params = [
            'courseid' => $COURSE->id,
            'pagetitle' => $PAGE->title
        ];
        $PAGE->requires->js_call_amd('block_revisionmanager/formhandler', 'init', [$params]);
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
