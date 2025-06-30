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
        
            // Add after loadExistingData()

            function setupRatingPopup() {
                const container = document.getElementById('rating-container');
                const popup = document.getElementById('rating-popup');
                const dateInput = document.getElementById('rating-date');
                const valueInput = document.getElementById('rating-value');
                const saveBtn = document.getElementById('rating-save');

                let currentButton = null;

                container.querySelectorAll('.rating-btn').forEach(button => {
                    button.addEventListener('click', function(e) {
                        currentButton = this;

                        // Fill form with current values
                        valueInput.value = this.dataset.rating;
                        dateInput.value = this.dataset.date || new Date().toISOString().split('T')[0];

                        // Position popup below button
                        const rect = this.getBoundingClientRect();
                        const containerRect = container.getBoundingClientRect();

                        popup.style.position = 'absolute';
                        popup.style.left = `${rect.left - containerRect.left}px`;
                        popup.style.top = `${rect.top - containerRect.top + 45}px`;
                        popup.style.display = 'block';
                    });
                });

                // Save logic
                saveBtn.addEventListener('click', function() {
                    if (currentButton) {
                        const newRating = valueInput.value;
                        const newDate = dateInput.value;

                        currentButton.textContent = newRating;
                        currentButton.dataset.rating = newRating;
                        currentButton.dataset.date = newDate;

                        popup.style.display = 'none';

                                                    // Remove old color classes
                            currentButton.classList.remove('bg-danger', 'bg-warning', 'bg-success', 'bg-orange', 'bg-lightgreen');

                            // Add based on rating
                            let rating = parseInt(newRating);
                            if (rating === 1) {
                                currentButton.classList.add('bg-danger', 'text-white');
                            } else if (rating === 2) {
                                currentButton.classList.add('bg-orange', 'text-white');
                            } else if (rating === 3) {
                                currentButton.classList.add('bg-warning', 'text-dark');
                            } else if (rating === 4) {
                                currentButton.classList.add('bg-lightgreen', 'text-white');
                            } else if (rating === 5) {
                                currentButton.classList.add('bg-success', 'text-white');
                            }

                        // TODO: Add save to DB if needed
                        console.log(`Saved: ${newRating} on ${newDate}`);
                    }
                });

                // Hide if clicked outside
                document.addEventListener('click', function(e) {
                    if (!popup.contains(e.target) && !e.target.classList.contains('rating-btn')) {
                        popup.style.display = 'none';
                    }
                });
            }

            // Then call setupRatingPopup() at the end of init()
            setupRatingPopup();

        
        }
    };
});
