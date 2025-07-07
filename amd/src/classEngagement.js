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

                    // Build HTML table dynamically
                    let tableHtml = `
                        <table class="table table-striped">
                            <thead>
                                <tr><th>Name</th><th>Email</th></tr>
                            </thead>
                            <tbody>
                    `;
                    students.forEach(student => {
                        tableHtml += `<tr><td>${student.fullname}</td><td>${student.email}</td></tr>`;
                    });
                    tableHtml += '</tbody></table>';

                    try {
                        const modal = await ModalFactory.create({
                            title: title,
                            body: tableHtml,
                            type: ModalFactory.types.DEFAULT
                        });

                        modal.show();

                        modal.getRoot().on('hidden.bs.modal', () => {
                            modal.destroy();
                        });
                    } catch (e) {
                        window.console.error("Modal creation failed:", e);
                    }
                });
            });
        }
    };
});
