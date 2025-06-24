define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {
    return {
        init: function(params) {
            function saveData() {
                const date = $('#nextReview').val();
                const pageurl = window.location.pathname + window.location.search;

                if (!date) {
                    // Call delete endpoint when date is cleared
                    Ajax.call([{
                        methodname: 'block_revisionmanager_delete_entry',
                        args: {
                            pageurl: pageurl,
                            courseid: params.courseid
                        },
                        done: function(response) {
                            console.log('Entry deleted:', response.status);
                        },
                        fail: Notification.exception
                    }]);
                } else {
                    // Save date normally
                    Ajax.call([{
                        methodname: 'block_revisionmanager_save_entry',
                        args: {
                            nextreview: date,
                            pageurl: pageurl,
                            courseid: params.courseid,
                            pagetitle: params.pagetitle
                        },
                        done: function(response) {
                            console.log('Saved:', response.status);
                        },
                        fail: Notification.exception
                    }]);
                }
            }

            function loadExistingData() {
                const pageurl = window.location.pathname + window.location.search;

                Ajax.call([{
                    methodname: 'block_revisionmanager_get_entry',
                    args: { 
                        pageurl: pageurl,
                        courseid: params.courseid
                     },
                    done: function(data) {
                        if (data.nextreview) {
                            $('#nextReview').val(data.nextreview);
                        }
                    },
                    fail: Notification.exception
                }]);
            }

            // Attach listeners for autosave
            $('#nextReview').on('input change', saveData);
            loadExistingData();
        }
    };
});