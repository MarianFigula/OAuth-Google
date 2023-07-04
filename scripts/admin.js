
$(document).ready(function () {
    $('#admin-oh-sportsmen').DataTable({
        paging: true,
        lengthMenu: [[10, 20, 50, -1], [10, 20, 50, "All"] ],
        columnDefs: [
            { className: "dt-head-center", targets: [8] },
            { orderable: false, targets: [0,8] },
        ]
    });
});