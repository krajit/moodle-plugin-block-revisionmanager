define(['core/templates', 'jquery', 'core/ajax', 'core/notification'],
function(Templates, $, Ajax, Notification) {
    return {
        init: function(params) {
            const chapter = document.querySelector('#mod_book-chapter h3');
            if (!chapter) {
                return;
            }

            Templates.render('block_revisionmanager/revisionbar', {})
                .then(function(html, js) {
                    const bar = document.createElement('div');
                    bar.className = 'block-revisionmanager-injected';
                    bar.innerHTML = html;
                    chapter.parentNode.insertBefore(bar, chapter);

                    Templates.runTemplateJS(js);

                    const grid = bar.querySelector('#rating-grid');
                    const plusBtn = bar.querySelector('#plusBtn');
                    const popup = bar.querySelector('#rating-popup');
                    const dateInput = bar.querySelector('#rating-date');
                    const saveBtn = bar.querySelector('#rating-save');
                    const pageurl = window.location.pathname + window.location.search;

                    if (!grid || !plusBtn || !popup) {
                        console.warn('Revision manager: required elements missing in template.');
                        return;
                    }

                    let editingDiv = null;
                    let currentRating = null; // store selected rating reliably

                     function getSelectedRating() {
                        const selected = bar.querySelector('input[name="rating-value"]:checked');
                        return selected ? parseInt(selected.value) : null;
                    }

                    function openPopup(x, y, rating = '', date = '') {
                        popup.style.left = `${x}px`;
                        popup.style.top = `${y}px`;
                        popup.style.display = 'block';
                        dateInput.value = date;

                        // Only reset if creating new
                        if (!rating) {
                            bar.querySelectorAll('input[name="rating-value"]').forEach(r => r.checked = false);
                        } else {
                            const selected = bar.querySelector(`input[name="rating-value"][value="${rating}"]`);
                            if (selected) selected.checked = true;
                        }
                    }
                    
                    function createRatingSquare(rating, date, uniqueKey) {
                        const div = bar.createElement('div');
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
                            openPopup(x, y, div.dataset.rating, div.dataset.date);
                            e.stopPropagation();
                        });

                        return div;
                    }

                    // Plus button opens popup for new rating
                    plusBtn.addEventListener('click', (e) => {
                        editingDiv = null;
                        const rect = plusBtn.getBoundingClientRect();
                        const containerRect = grid.getBoundingClientRect();
                        const x = rect.left - containerRect.left + grid.scrollLeft;
                        const y = rect.top - containerRect.top + 45;
                        const today = new Date().toISOString().split('T')[0];
                        openPopup(x, y, '', today);
                        e.stopPropagation();
                    });

                    // Save (create or update) rating
                    function saveData() {
                        const rating = getSelectedRating();
                        const date = dateInput.value;
                        const timestamp = date ? Math.floor(new Date(date).getTime() / 1000) : 0;
                        const ratingKey = editingDiv?.dataset?.key || null;

                        if (rating === null || isNaN(rating) || rating < 0 || rating > 5) {
                            alert("Please select a rating between 0 and 5 before saving.");
                            return;
                        }

                        let temppageurl = pageurl;
                        if (pageurl.includes("book") && !pageurl.includes("chapterid")) {
                            temppageurl += `&chapterid=${params.chapterid}`;
                        }

                        window.console.log(rating);

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
                                ratingkey: ratingKey
                            },
                            done: function(response) {
                                if (editingDiv) {
                                    editingDiv.className = `rating-square bg-rating-${rating}`;
                                    editingDiv.textContent = rating;
                                    editingDiv.title = `Date: ${date}`;
                                    editingDiv.dataset.rating = rating;
                                    editingDiv.dataset.date = date;
                                } else {
                                    const div = createRatingSquare(rating, date, response.ratingkey);
                                    grid.insertBefore(div, plusBtn);
                                }
                                popup.style.display = 'none';
                                currentRating = null;
                            },
                            fail: Notification.exception
                        }]);
                    }

                    saveBtn?.addEventListener('click', saveData);

                    // Prevent popup from closing when interacting inside
                    popup.addEventListener('click', e => e.stopPropagation());

                    // Close popup with Escape key
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape' && popup.style.display === 'block') {
                            popup.style.display = 'none';
                        }
                    });

                    // Load existing ratings
                    function loadExistingData() {
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

                    // Next review date save/load
                    function saveNextReviewDate() {
                        let date = $(bar).find('#nextReview').val() || '';

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
                                console.log('Next review date saved:', response.status);
                            },
                            fail: Notification.exception
                        }]);
                    }

                    $(bar).find('#nextReview').on('input change', saveNextReviewDate);

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
                                    $(bar).find('#nextReview').val(data.nextreview);
                                }
                            },
                            fail: Notification.exception
                        }]);
                    }

                    loadExistingData();
                    loadExistingReviewDate();
                })
                .catch(function(err) {
                    console.error('Failed to render revisionbar template:', err);
                });
        }
    };
});
