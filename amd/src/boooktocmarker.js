define(['jquery', 'core/ajax'], function($, Ajax) {
    return {
        init: function(params) {
            const pageurl = window.location.pathname + window.location.search;
         Ajax.call([{
            methodname: 'block_revisionmanager_get_read_urls',
            args: { courseid: params.courseid },
            done: function(result) {
                // Build a map: fullpath → ratingvalues[]
                const urlRatingsMap = {};
                result.entries.forEach(entry => {
                    const cleanUrl = entry.pageurl.replace(/^\/+/, ''); // remove leading slashes
                    urlRatingsMap[cleanUrl] = entry.ratingvalues;
                });

                $('div.book_toc a').each(function() {
                    const href = $(this).attr('href');
                    const fullpath = 'mod/book/' + href;
                    if (urlRatingsMap[fullpath]) {
                        const ratings = urlRatingsMap[fullpath];

                        // Build span-wrapped ratings with background classes
                        const ratingHtml = ratings.map(rating => {
                            const clampedRating = Math.max(0, Math.min(5, rating)); // Ensure 0–5
                            return `<span class="bg-rating-toc-${clampedRating}">${clampedRating}</span>`;
                        }).join('');

                        $(this).prepend(ratingHtml+' ');
                    }
                });

              $('div.book_toc li:has(strong)').each(function() {
                 const $strong = $(this).find('strong');
                    console.log(pageurl);
                    const cleanUrl = pageurl.startsWith('/') ? pageurl.substring(1) : pageurl;

                 if (urlRatingsMap[cleanUrl]) {
                        const ratings = urlRatingsMap[cleanUrl];

                        // Build span-wrapped ratings with background classes
                        const ratingHtml = ratings.map(rating => {
                            const clampedRating = Math.max(0, Math.min(5, rating)); // Ensure 0–5
                            return `<span class="bg-rating-toc-${clampedRating}">${clampedRating}</span>`;
                        }).join('');

                        $strong.prepend(ratingHtml+' ');
                        
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
