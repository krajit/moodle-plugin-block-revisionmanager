define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {
    return {
        init: function(params) {
            function saveData() {
                const date = $('#nextReview').val();
                const pageurl = window.location.pathname + window.location.search;

                if (!date) {
                    // Nothing to save
                    return;
                }

                Ajax.call([{
                    methodname: 'block_ajaxforms_save_entry',
                    args: {
                        nextreview: date,
                        pageurl: pageurl,
                        courseid: params.courseid
                    },
                    done: function(response) {
                        window.console.log('Saved:', response.status);
                    },
                    fail: Notification.exception
                }]);
                
            }

            // Attach listeners for autosave
            $('#nextReview').on('input change', saveData);
        }
    };
});