define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {
    return {
        init: function(params) {
            const levelColors = {
                "Not Started": "#f8d7da",
                "Fresh": "#fce5cd",
                "Learning": "#fff3cd",
                "Expert": "#d1ecf1",
                "Done": "#d4edda"
            };

            function updateLevelColor() {
                const levelSelect = document.getElementById('learninglevel');
                const selectedLevel = levelSelect?.value;
                if (selectedLevel && levelColors[selectedLevel]) {
                    levelSelect.style.backgroundColor = levelColors[selectedLevel];
                } else if (levelSelect) {
                    levelSelect.style.backgroundColor = 'white';
                }
            }

            function updateProgressBar() {
                const revisionInput = document.getElementById('revisioncount');
                const targetInput = document.getElementById('targetcount');
                const progressBar = document.getElementById('progressBar');

                const revisioncount = parseInt(revisionInput?.value || 0);
                const targetcount = parseInt(targetInput?.value || 1);
                const percent = Math.min(100, Math.round((revisioncount / targetcount) * 100));
                if (progressBar) {
                    progressBar.style.width = percent + '%';
                    progressBar.textContent = percent + '%';
                }
            }

            function saveData() {
                var date = $('#nextReview').val();
                const learninglevel = $('#learninglevel').val();
                const revisioncount = $('#revisioncount').val();
                const targetcount = $('#targetcount').val();
                const pageurl = window.location.pathname + window.location.search;

                if (!date) {
                    date = '';
                    //window.console.log("date cleared. TODO: Update when to delete page row from the table");
                }
                Ajax.call([{
                    methodname: 'block_revisionmanager_save_entry',
                    args: {
                        nextreview: date,
                        pageurl: pageurl,
                        courseid: params.courseid,
                        pagetitle: params.pagetitle,
                        learninglevel: learninglevel,
                        revisioncount: revisioncount,
                        targetcount: targetcount,
                    },
                    done: function(response) {
                        console.log('Saved:', response.status);
                    },
                    fail: Notification.exception
                }]);
                
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
                        if (data.learninglevel) {
                            $('#learninglevel').val(data.learninglevel);
                            updateLevelColor();
                        }
                        if (data.revisioncount) {
                            $('#revisioncount').val(data.revisioncount);
                        }
                        if (data.targetcount) {
                            $('#targetcount').val(data.targetcount);
                        }
                        updateProgressBar();
                    },
                    fail: Notification.exception
                }]);
            }

            // Attach listeners for autosave
            $('#nextReview').on('input change', saveData);
            $('#learninglevel').on('input change', function() {
                updateLevelColor();
                saveData();
            });
            $('#revisioncount').on('input change', function() {
                updateProgressBar();
                saveData();
            });
            $('#targetcount').on('input change', function() {
                updateProgressBar();
                saveData();
            });

            // Initial setup
            updateLevelColor();
            updateProgressBar();
            loadExistingData();
        }
    };
});
