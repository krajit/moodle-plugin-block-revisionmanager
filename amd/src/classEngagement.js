define(['core/modal_factory'], function(ModalFactory) {
    return {
        init: function () {
            document.querySelectorAll('.segment').forEach(segment => {
                segment.addEventListener('click', async function () {
                    const json = this.getAttribute('data-students');
                    const title = this.getAttribute('data-title') || 'Students';
                    let students;

                    try {
                        students = JSON.parse(json);
                    } catch (e) {
                        students = [];
                    }

                    const modalRoot = document.getElementById('studentModal');
                    const tbody = document.getElementById('studentModalTableBody');
                    const titleEl = document.getElementById('studentModalLabel');

                    titleEl.textContent = title;
                    tbody.innerHTML = '';

                    students.forEach(student => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td>${student.fullname}</td><td>${student.email}</td>`;
                        tbody.appendChild(tr);
                    });

                    // Use Moodle's modal_factory to handle modal logic cleanly
                    const modalInstance = await ModalFactory.create({
                        title: title,
                        body: modalRoot.querySelector('.modal-body'),
                        type: ModalFactory.types.DEFAULT
                    });

                    modalInstance.show();

                    // Optional: remove the default one if needed
                    modalRoot.classList.remove('show');
                });
            });
        }
    };
});
