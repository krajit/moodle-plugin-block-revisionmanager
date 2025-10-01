define(['core/templates'], function(Templates) {
    return {
        init: function(params) {
            const chapter = document.querySelector('#mod_book-chapter h3');
            if (chapter) {
                // Parse URL parameters
                const url = new URL(window.location.href);
                const pageId = url.searchParams.get('id') || 'N/A';
                const chapterId = url.searchParams.get('chapterid') || 'N/A';

                const user = params.user;

                // Render Mustache template from external file
                Templates.render('block_revisionmanager/revisionbar', {})
                    .then(function(html, js) {
                        const bar = document.createElement('div');
                        bar.innerHTML = html;
                        chapter.parentNode.insertBefore(bar, chapter);

                        // If template includes JS, execute it
                        Templates.runTemplateJS(js);
                    });
            }
        }
    };
});