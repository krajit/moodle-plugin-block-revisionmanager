define(['jquery', 'core/ajax'], function($, Ajax) {
    return {
        init: function(params) {
            Ajax.call([{
                methodname: 'block_revisionmanager_get_read_urls',
                args: { courseid: params.courseid },
                done: function(result) {
                    // Normalize DB entries
                    const visited = result.urls.map(url => url.replace(/^\/+/, '')); // remove leading slash

                    console.log('Visited URLs from DB:', visited);

                    $('div.book_toc a').each(function() {
                        const href = $(this).attr('href'); // e.g., "view.php?id=28&chapterid=130"
                        const fullpath = 'mod/book/' + href;

                        console.log('Checking:', fullpath);
                        if (visited.includes(fullpath)) {
                            console.log('Matched:', fullpath);
                            $(this).addClass('read-chapter');
                        }
                    });
                },
                fail: function(err) {
                    console.error("Could not fetch read URLs", err);
                }
            }]);
        }
    };
});
