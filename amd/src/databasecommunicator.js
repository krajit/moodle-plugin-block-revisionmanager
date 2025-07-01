// blocks/revisionmanager/amd/src/datacommunicator.js

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {
    return {
        init: function(params) {
            const grid = document.getElementById('rating-grid');
            const plusBtn = document.getElementById('plusBtn');
            const popup = document.getElementById('rating-popup');
            const dateInput = document.getElementById('rating-date');
            const valueInput = document.getElementById('rating-value');
            const saveBtn = document.getElementById('rating-save');

            // === Show popup when clicking plus button ===
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

            // === Save new rating to server ===
            saveBtn?.addEventListener('click', () => {
                const rating = parseInt(valueInput.value);
                const date = dateInput.value;

                if (isNaN(rating) || rating < 0 || rating > 5) {
                    alert("Please enter a rating between 0 and 5");
                    return;
                }

                const timestamp = Math.floor(new Date(date).getTime() / 1000);

                Ajax.call([{
                    methodname: 'block_revisionmanager_save_rating',
                    args: {
                        courseid: params.courseid,
                        pageid: params.pageid,
                        ratingvalue: rating,
                        ratingdate: timestamp
                    },
                    done: function() {
                        const div = document.createElement('div');
                        div.className = `rating-square bg-rating-${rating}`;
                        div.textContent = rating;
                        div.title = `Date: ${date}`;

                        grid.insertBefore(div, plusBtn);
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
                    pageid: params.pageid
                },
                done: function(ratings) {
                    ratings.forEach(r => {
                        const d = new Date(r.ratingdate * 1000);
                        const dateStr = d.toISOString().split('T')[0];

                        const div = document.createElement('div');
                        div.className = `rating-square bg-rating-${r.ratingvalue}`;
                        div.textContent = r.ratingvalue;
                        div.title = `Date: ${dateStr}`;
                        grid.insertBefore(div, plusBtn);
                    });
                },
                fail: Notification.exception
            }]);
        }
    };
});
