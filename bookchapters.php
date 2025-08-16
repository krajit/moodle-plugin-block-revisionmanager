<?php
require_once(__DIR__ . '/../../config.php');
require_login();

$courseid = required_param('courseid', PARAM_INT);

$course = get_course($courseid);
$context = context_course::instance($courseid);
require_capability('moodle/course:view', $context);

$PAGE->set_url(new moodle_url('/blocks/revisionmanager/bookchapters.php', ['courseid' => $courseid]));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('bookchapters', 'block_revisionmanager'));
$PAGE->set_heading($course->fullname);

global $DB, $USER;

// Get all book modules in the course.
$sql = "SELECT cm.id AS cmid, b.id AS bookid, b.name AS bookname
        FROM {course_modules} cm
        JOIN {modules} m ON m.id = cm.module
        JOIN {book} b ON b.id = cm.instance
        WHERE cm.course = ? AND m.name = 'book'
        ORDER BY b.name ASC";
$books = $DB->get_records_sql($sql, [$courseid]);

$chapterslist = [];

foreach ($books as $book) {
    $chapters = $DB->get_records('book_chapters', ['bookid' => $book->bookid], 'pagenum ASC');
    foreach ($chapters as $ch) {
        // Get the latest rating for this user+chapter.
        $sql = "SELECT r.ratingvalue
                  FROM {block_revisionmanager_ratings} r
                 WHERE r.chapterid = :chapterid
                   AND r.userid = :userid
              ORDER BY r.ratingdate DESC, r.timemodified DESC
                 LIMIT 1";
        $params = ['chapterid' => $ch->id, 'userid' => $USER->id];
        $yourrating = $DB->get_field_sql($sql, $params);

        $chapterslist[] = [
            'bookname' => $book->bookname,
            'chaptertitle' => $ch->title,
            'chapterid' => $ch->id,
            'viewurl' => new moodle_url('/mod/book/view.php', ['id' => $book->cmid, 'chapterid' => $ch->id]),
            'yourrating' => $yourrating ?? '-' // dash if no rating
        ];
    }
}

echo $OUTPUT->header();

if (empty($chapterslist)) {
    echo html_writer::tag('p', get_string('nobookchapters', 'block_revisionmanager'));
} else {
    // Search filter input
    echo html_writer::tag('input', '', [
        'type' => 'text',
        'id' => 'chapterFilter',
        'placeholder' => 'Filter chapters...',
        'style' => 'margin-bottom:10px; padding:5px; width:300px;'
    ]);

    $table = new html_table();
    $table->id = 'chapterTable';
    $table->head = ['Book Name', 'Chapter Title', 'Your Rating'];

    foreach ($chapterslist as $row) {
        $table->data[] = [
            format_string($row['bookname']),
            html_writer::link($row['viewurl'], format_string($row['chaptertitle'])),
            $row['yourrating']
        ];
    }

    echo html_writer::table($table);

    // Add JS filter logic
    $filterjs = <<<JS
    document.getElementById('chapterFilter').addEventListener('keyup', function() {
        var filter = this.value.toLowerCase();
        var rows = document.querySelectorAll('#chapterTable tr');

        rows.forEach(function(row, index) {
            if (index === 0) return; // skip table header
            var cells = row.getElementsByTagName('td');
            var match = false;
            for (var i = 0; i < cells.length; i++) {
                if (cells[i].textContent.toLowerCase().includes(filter)) {
                    match = true;
                    break;
                }
            }
            row.style.display = match ? '' : 'none';
        });
    });
    JS;

    $PAGE->requires->js_amd_inline($filterjs);
}

echo $OUTPUT->footer();
