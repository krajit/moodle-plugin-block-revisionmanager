<?php
require_once(__DIR__ . '/../../config.php');
require_login();

$courseid = required_param('courseid', PARAM_INT);

$course = get_course($courseid);
$context = context_course::instance($courseid);
require_capability('moodle/course:view', $context);

$enrolledusers = get_enrolled_users($context, '', 0, 'u.id');
$numUsers = count($enrolledusers);


$PAGE->set_url(new moodle_url('/blocks/revisionmanager/bookchapters.php', ['courseid' => $courseid]));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('bookchapters', 'block_revisionmanager'));
$PAGE->set_heading($course->fullname);

global $DB, $USER;

/**
 * Render a 0→5 stacked horizontal bar from counts.
 *
 * @param array $counts keys: 0..5
 * @return string HTML of the bar
 */
function rm_render_distribution_bar(array $counts): string {
    $labels = [
        -1 => 'No Rating',
        0 => 'Rating 0',
        1 => 'Rating 1',
        2 => 'Rating 2',
        3 => 'Rating 3',
        4 => 'Rating 4',
        5 => 'Rating 5',
    ];
    // $total = 0;
    // for ($i = 0; $i <= 5; $i++) {
    //     $total += (int)($counts[$i] ?? 0);
    // }

    global $numUsers;
    $total = $numUsers;

    $segments = '';
    if ($total > 0) {
        for ($i = -1; $i <= 5; $i++) {
            $val = (int)($counts[$i] ?? 0);
            $pct = $val > 0 ? round(($val / $total) * 100, 2) : 0;
            if ($pct <= 0) continue;

            // Title/tooltip showing exact value and percentage.
            $title = $labels[$i] . ': ' . $val . ' (' . $pct . '%)';
            $segments .= html_writer::tag('span', '',
                [
                    'class' => 'rm-seg rm-' . $i,
                    'style' => "width: {$pct}%;",
                    'title' => $title,
                    'aria-label' => $title,
                ]
            );
        }
    } else {
        // No data: show a subtle empty bar.
        $segments .= html_writer::tag('span', '', ['class' => 'rm-empty']);
    }

    return html_writer::tag('div', $segments, ['class' => 'rm-bar', 'role' => 'img', 'aria-label' => 'Rating distribution 0 to 5']);
}

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

        // Count users whose latest rating (ordered by ratingdate DESC, timemodified DESC) equals each value 0..5.
        $sql = "SELECT COUNT(*)
                  FROM (
                        SELECT r.userid, r.ratingvalue,
                               ROW_NUMBER() OVER (PARTITION BY r.userid ORDER BY r.ratingdate DESC, r.timemodified DESC) AS rn
                          FROM {block_revisionmanager_ratings} r
                         WHERE r.chapterid = :chapterid
                       ) ranked
                 WHERE rn = 1 AND ratingvalue = :rating";

        $numusers0 = $DB->get_field_sql($sql, ['chapterid' => $ch->id, 'rating' => 0]);
        $numusers1 = $DB->get_field_sql($sql, ['chapterid' => $ch->id, 'rating' => 1]);
        $numusers2 = $DB->get_field_sql($sql, ['chapterid' => $ch->id, 'rating' => 2]);
        $numusers3 = $DB->get_field_sql($sql, ['chapterid' => $ch->id, 'rating' => 3]);
        $numusers4 = $DB->get_field_sql($sql, ['chapterid' => $ch->id, 'rating' => 4]);
        $numusers5 = $DB->get_field_sql($sql, ['chapterid' => $ch->id, 'rating' => 5]);
        $numusersEmpty = $numUsers - ($numusers0 + $numusers1 + $numusers2 + $numusers3 + $numusers4 + $numusers5);

        $chapterslist[] = [
            'bookname' => $book->bookname,
            'chaptertitle' => $ch->title,
            'chapterid' => $ch->id,
            'viewurl' => new moodle_url('/mod/book/view.php', ['id' => $book->cmid, 'chapterid' => $ch->id]),
            'yourrating' => $yourrating ?? '-', // dash if no rating
            'numusersEmpty' => (int)($numusersEmpty ?? 0),
            'numusers0' => (int)($numusers0 ?? 0),
            'numusers1' => (int)($numusers1 ?? 0),
            'numusers2' => (int)($numusers2 ?? 0),
            'numusers3' => (int)($numusers3 ?? 0),
            'numusers4' => (int)($numusers4 ?? 0),
            'numusers5' => (int)($numusers5 ?? 0),
        ];
    }
}

echo $OUTPUT->header();

// Minimal CSS for the stacked bar + legend.
echo html_writer::tag('style', <<<CSS
.rm-bar {
  display: flex;
  width: 220px;       /* adjust as you like */
  height: 12px;
  border-radius: 6px;
  overflow: hidden;
  background: #f1f3f5;
  border: 1px solid #e2e8f0;
}
.rm-seg { display:block; height:100%; }
.rm-empty { display:block; width:100%; height:100%; background:#ececec; }
.rm--1 { background:#ececec; } 
.rm-0 { background:#b91c1c; } /* red-700 */
.rm-1 { background:#dc2626; } /* red-600 */
.rm-2 { background:#f59e0b; } /* amber-500 */
.rm-3 { background:#10b981; } /* emerald-500 */
.rm-4 { background:#3b82f6; } /* blue-500 */
.rm-5 { background:#7c3aed; } /* violet-600 */

.rm-legend { display:flex; gap:10px; align-items:center; margin:8px 0 14px; font-size:12px; color:#475569; }
.rm-legend span { display:inline-flex; align-items:center; gap:6px; }
.rm-dot { width:12px; height:12px; border-radius:3px; display:inline-block; }
CSS);

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

    // Legend (optional)
    echo html_writer::tag('div',
        '<span><i class="rm-dot rm--1"></i>Empty</span>'.
        '<span><i class="rm-dot rm-0"></i>0</span>'.
        '<span><i class="rm-dot rm-1"></i>1</span>'.
        '<span><i class="rm-dot rm-2"></i>2</span>'.
        '<span><i class="rm-dot rm-3"></i>3</span>'.
        '<span><i class="rm-dot rm-4"></i>4</span>'.
        '<span><i class="rm-dot rm-5"></i>5</span>',
        ['class' => 'rm-legend']
    );

    $table = new html_table();
    $table->id = 'chapterTable';
    $table->head = ['Book Name', 'Chapter Title', 'Your Rating', 
    #'num0','num1','num2','num3','num4','num5', 
    'Distribution (0→5)'];

    foreach ($chapterslist as $row) {
        $counts = [
            -1 => $row['numusersEmpty'],
            0 => $row['numusers0'],
            1 => $row['numusers1'],
            2 => $row['numusers2'],
            3 => $row['numusers3'],
            4 => $row['numusers4'],
            5 => $row['numusers5'],
        ];
        $barhtml = rm_render_distribution_bar($counts);

        $table->data[] = [
            format_string($row['bookname']),
            html_writer::link($row['viewurl'], format_string($row['chaptertitle'])),
            $row['yourrating'],
            // $row['numusers0'],
            // $row['numusers1'],
            // $row['numusers2'],
            // $row['numusers3'],
            // $row['numusers4'],
            // $row['numusers5'],
            $barhtml,
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
