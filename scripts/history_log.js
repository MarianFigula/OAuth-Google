$(document).ready(function () {
    $('#login-table').DataTable({
        paging: true,
        lengthMenu: [[10, 20, 50, -1], [10, 20, 50, "All"] ],
        columnDefs: [{ orderable: false, targets: [0,1] }]
    });
});

$(document).ready(function () {
    $('#activity-table').DataTable({
        paging: true,
        lengthMenu: [[10, 20, 50, -1], [10, 20, 50, "All"] ],
        columnDefs: [{ orderable: false, targets: [0] }]
    });
});