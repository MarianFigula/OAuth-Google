$(document).ready(function () {
    $('#sk-oh-winners').DataTable({
        //scrollY: '200px',
        //scrollCollapse: true,
        paging: true,
        lengthMenu: [[10, 20, 50, -1], [10, 20, 50, "All"] ],
        columnDefs: [
            { orderable: false, targets: [0, 3, 5] },
            {orderData: [4,2], targets: [4]},

        ]
    });
});


$(document).ready(function () {
    $('#top-ten-by-gold-medals').DataTable({
        //scrollY: '200px',
        //scrollCollapse: true,
        paging: true,
        lengthMenu: [[10, 20, 50, -1], [10, 20, 50, "All"] ],
        order: [[1, 'desc']],
    });
});


$('#exampleModalCenter').on('shown.bs.modal', function () {
    $('#tlacidlo').trigger('focus')
})
