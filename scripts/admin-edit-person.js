$(document).ready(function () {
    $('#admin-edit-placement').DataTable({
        paging: true,
        lengthMenu: [[10, 20, 50, -1], [10, 20, 50, "All"] ],
        columnDefs: [
            { orderable: false, targets: [4] },
            { className: "dt-head-center", targets: [4] },
        ]
    });
});