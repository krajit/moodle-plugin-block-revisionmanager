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

                        // TODO: If you return a list of past ratings, render them here
                        // Example: data.ratings = [{date: '2025-06-30', value: 4}, ...]
                        // populateRatingGrid(data.ratings);
                    },
                    fail: Notification.exception
                }]);
            }

            // === RATING SQUARE UI ===
            const grid = document.getElementById('rating-grid');
            const plusBtn = document.getElementById('plusBtn');
            const popup = document.getElementById('rating-popup');
            const dateInput = document.getElementById('rating-date');
            const valueInput = document.getElementById('rating-value');
            const saveBtn = document.getElementById('rating-save');

            plusBtn?.addEventListener('click', (e) => {
                const rect = plusBtn.getBoundingClientRect();
                const containerRect = grid.getBoundingClientRect();
                popup.style.position = 'absolute';
                popup.style.left = `${rect.left - containerRect.left + grid.scrollLeft}px`;
                popup.style.top = `${rect.top - containerRect.top + 45}px`;
                popup.style.display = 'block';
                dateInput.valueAsDate = new Date();
                valueInput.value = '';
            });

            saveBtn?.addEventListener('click', () => {
                const rating = parseInt(valueInput.value);
                const date = dateInput.value;

                if (isNaN(rating) || rating < 0 || rating > 5) {
                    alert("Please enter a rating between 0 and 5");
                    return;
                }

                const div = document.createElement('div');
                div.className = `rating-square bg-rating-${rating}`;
                div.textContent = rating;
                div.title = `Date: ${date}`;

                grid.insertBefore(div, plusBtn);
                popup.style.display = 'none';

                // TODO: Save rating to server
                console.log("Saved rating:", rating, "on", date);
            });

            document.addEventListener('click', (e) => {
                if (!popup.contains(e.target) && e.target !== plusBtn) {
                    popup.style.display = 'none';
                }
            });

            // === INIT ===
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

            updateLevelColor();
            updateProgressBar();
            loadExistingData();
        }
    };
});
