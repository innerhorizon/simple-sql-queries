jQuery(document).ready( function () {
    jQuery('#dt-default').DataTable( {
        "dom": 'Bfrtip',
        "deferRender": true,
        "orderMulti": true,
        "colReorder": true,
        "select": true,
        "rowReorder": true,
        "rowReorder": {
            "update": false
        },
        "buttons": [
            {
                extend:    'print',
                text:      '<i class="fa fa-print"></i>',
                titleAttr: 'Print'
            },
            {
                extend:    'csv',
                text:      '<i class="fa fa-file-text-o"></i>',
                titleAttr: 'Export'
            },
            {
                extend:    'pdf',
                text:      '<i class="fa fa-file-pdf-o"></i>',
                titleAttr: 'PDF'
            },
            {
                extend:    'colvis',
                text:      'Show/Hide Columns',
                titleAttr: 'ShowHide'
            }
        ],
    });
});