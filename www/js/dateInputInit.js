function initDateInput() {
    $('input[data-dateinput-type]').dateinput({
        datetime: {
            dateFormat: 'd.m.yy',
            timeFormat: 'H:mm',
            options: { // options for type=datetime
                changeYear: true
            }
        },
        'datetime-local': {
            dateFormat: 'd.m.yy',
            timeFormat: 'H:mm'
        },
        date: {
            dateFormat: 'd.m.yy'
        },
        month: {
            dateFormat: 'MM yy'
        },
        week: {
            dateFormat: "w. 'week of' yy"
        },
        time: {
            timeFormat: 'H:mm'
        },
        options: { // global options
            closeText: "Close"
        }
    });
}
    
$(document).ready(function() {
    initDateInput();
});