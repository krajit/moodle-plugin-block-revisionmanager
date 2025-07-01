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

            function openPopup(x, y, rating = '', date = '') {
                popup.style.position = 'absolute';
                popup.style.left = `${x}px`;
                popup.style.top = `${y}px`;
                popup.style.display = 'block';
                valueInput.value = rating;
                dateInput.value = date;
            }

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
                    openPopup(x, y, rating, date);
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

            // === Save (create or update) rating ===
            saveBtn?.addEventListener('click', () => {
                const rating = parseInt(valueInput.value);
                const date = dateInput.value;
                const timestamp = Math.floor(new Date(date).getTime() / 1000);
                const nextreview = $('#nextReview').val();
                const ratingKey = editingDiv?.dataset?.key || null;

                if (isNaN(rating) || rating < 0 || rating > 5) {
                    alert("Please enter a rating between 0 and 5");
                    return;
                }

                Ajax.call([{
                    methodname: 'block_revisionmanager_save_rating',
                    args: {
                        courseid: params.courseid,
                        pageid: params.pageid,
                        ratingvalue: rating,
                        ratingdate: timestamp,
                        pageurl: pageurl,
                        nextreview: nextreview,
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
                    },
                    fail: Notification.exception
                }]);
            });

            // === Hide popup if clicking outside ===
            document.addEventListener('click', (e) => {
                if (!popup.contains(e.target) && e.target !== plusBtn) {
                    popup.style.display = 'none';
                }
            });

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
    };
});
