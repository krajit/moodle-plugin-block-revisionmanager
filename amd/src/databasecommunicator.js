define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {
    return {
        init: function(params) {
            const grid = document.getElementById('rating-grid');
            const plusBtn = document.getElementById('plusBtn');
            const popup = document.getElementById('rating-popup');
            const dateInput = document.getElementById('rating-date');
            const valueInput = document.getElementById('rating-value');
            const saveBtn = document.getElementById('rating-save');
            const pageurl = window.location.pathname + window.location.search;

            let editingDiv = null; // null when adding new, non-null when editing

            /**
             * Opens the rating popup at the given coordinates and pre-fills values.
             *
             * @param {number} x - The x-coordinate for the popup.
             * @param {number} y - The y-coordinate for the popup.
             * @param {string} [rating=''] - The rating value to set in the popup.
             * @param {string} [date=''] - The date value to set in the popup.
             */
            function openPopup(x, y, rating = '', date = '') {
                popup.style.position = 'absolute';
                popup.style.left = `${x}px`;
                popup.style.top = `${y}px`;
                popup.style.display = 'block';
                valueInput.value = rating;
                dateInput.value = date;
            }

            /**
             * Creates a clickable rating square element with attached event listener
             * to open the rating popup.
             *
             * @param {string} rating - The rating value to display.
             * @param {string} date - The associated date for the rating.
             * @param {string} uniqueKey - A unique identifier for the rating.
             * @returns {HTMLDivElement} The created rating square element.
             */
            function createRatingSquare(rating, date, uniqueKey) {
                const div = document.createElement('div');
                div.className = `rating-square bg-rating-${rating}`;
                div.textContent = rating;
                div.title = `Date: ${date}`;
                div.dataset.rating = rating;
                div.dataset.date = date;
                div.dataset.key = uniqueKey;

                div.addEventListener('click', function(e) {
                    editingDiv = div;
                    const rect = div.getBoundingClientRect();
                    const containerRect = grid.getBoundingClientRect();
                    const x = rect.left - containerRect.left + grid.scrollLeft;
                    const y = rect.top - containerRect.top + 45;
                    const currentRating = div.dataset.rating;
                    const currentDate = div.dataset.date;
                    openPopup(x, y, currentRating, currentDate);
                    e.stopPropagation();
                });

                return div;
            }

            // === Show popup when clicking plus button ===
            plusBtn?.addEventListener('click', (e) => {
                editingDiv = null;
                const rect = plusBtn.getBoundingClientRect();
                const containerRect = grid.getBoundingClientRect();
                const x = rect.left - containerRect.left + grid.scrollLeft;
                const y = rect.top - containerRect.top + 45;
                const today = new Date().toISOString().split('T')[0];
                openPopup(x, y, '', today);
                e.stopPropagation();
            });


            /**
             * Saves the rating and date for the current page.
             * If editing an existing rating, updates it; otherwise, creates a new one.
             * Performs basic validation and handles Moodle AJAX call.
             */
            function saveData () {
                const rating = parseInt(valueInput.value);
                const date = dateInput.value;
                //const timestamp = Math.floor(new Date(date).getTime() / 1000);
                const timestamp = date ? Math.floor(new Date(date).getTime() / 1000) : 0;
                const ratingKey = editingDiv?.dataset?.key || null;

                if (isNaN(rating) || rating < 0 || rating > 5) {
                    alert("Please enter a rating between 0 and 5");
                    return;
                }

                var temppageurl = pageurl;
                if (pageurl.includes("book") && !pageurl.includes("chapterid")) {
                    temppageurl += `&chapterid=${params.chapterid}`;
                }
                Ajax.call([{
                    methodname: 'block_revisionmanager_save_rating',
                    args: {
                        courseid: params.courseid,
                        pageid: params.pageid,
                        ratingvalue: rating,
                        ratingdate: timestamp,
                        pageurl: temppageurl,
                        pagetitle: params.pagetitle,
                        chapterid: params.chapterid || null,
                        ratingkey: ratingKey // used to identify old entry if editing
                    },
                    done: function(response) {
                        // If editing, update the existing div
                        if (editingDiv) {
                            editingDiv.className = `rating-square bg-rating-${rating}`;
                            editingDiv.textContent = rating;
                            editingDiv.title = `Date: ${date}`;
                            editingDiv.dataset.rating = rating;
                            editingDiv.dataset.date = date;
                        } else {
                            // Otherwise create new div
                            const div = createRatingSquare(rating, date, response.ratingkey);
                            grid.insertBefore(div, plusBtn);
                        }
                        popup.style.display = 'none';
                        // Optionally refresh the page after short delay
                        location.reload();
                    },
                    fail: Notification.exception
                }]);
                //location.reload();
            }



            // === Save (create or update) rating ===
            saveBtn?.addEventListener('click', saveData);

            // === Hide popup if clicking outside ===
            document.addEventListener('click', (e) => {
                if (!popup.contains(e.target) && e.target !== plusBtn) {
                    popup.style.display = 'none';
                }
            });

            /**
             * Loads existing rating data from the server and populates the rating grid.
             * Each rating is converted to a readable date and displayed using rating squares.
             */
            function loadExistingData() {
                // === Load existing ratings from server ===
                Ajax.call([{
                    methodname: 'block_revisionmanager_get_ratings',
                    args: {
                        courseid: params.courseid,
                        pageid: params.pageid,
                        chapterid: params.chapterid || null
                    },
                    done: function(ratings) {
                        ratings.forEach(r => {
                            const d = new Date(r.ratingdate * 1000);
                            const dateStr = d.toISOString().split('T')[0];
                            const div = createRatingSquare(r.ratingvalue, dateStr, r.ratingkey);
                            grid.insertBefore(div, plusBtn);
                        });
                    },
                    fail: Notification.exception
                }]);
            }

            /**
             * Saves the next review date for the current page by sending it to the server.
             * If no date is selected, an empty string is sent.
             */
            function saveNextReviewDate() {
                var date = $('#nextReview').val();
                if (!date) { date = ''; }

                Ajax.call([{
                    methodname: 'block_revisionmanager_save_nextreview',
                    args: {
                        pageid: params.pageid,
                        courseid: params.courseid,
                        nextreview: date,
                        pageurl: pageurl,
                        chapterid: params.chapterid || null
                    },
                    done: function(response) {
                        window.console.log('next review date tweaked:', response.status);
                    },
                    fail: Notification.exception
                }]);
            }

            loadExistingData();

            $('#nextReview').on('input change', saveNextReviewDate);

            /**
             * Loads the saved next review date from the server and populates the input field.
             */
            function loadExistingReviewDate() {
                Ajax.call([{
                    methodname: 'block_revisionmanager_get_nextreview',
                    args: {
                        pageid: params.pageid,
                        courseid: params.courseid,
                        chapterid: params.chapterid || null
                    },
                    done: function(data) {
                        if (data.nextreview) {
                            $('#nextReview').val(data.nextreview);
                        }
                    },
                    fail: Notification.exception
                }]);
            }
            loadExistingReviewDate();
        }
    };
});
