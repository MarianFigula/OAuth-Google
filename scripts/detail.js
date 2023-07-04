$(document).ready(function () {
    $('#person-detail').DataTable({
        paging: true,
        lengthMenu: [[10, 20, 50, -1], [10, 20, 50, "All"] ],

    });
});