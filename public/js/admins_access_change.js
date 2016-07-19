$(document).ready(function() {
    $('#editUser').click(function() {
        changeAdminsAccess($(this), $('#usersReading'));
    });
    $('#editingComplaints').click(function() {
        changeAdminsAccess($(this), $('#readingComplaints'));
    });
});

function changeAdminsAccess(obj, inheritance) {
    if (obj.prop('checked')) {
        inheritance.prop('checked', true);
        inheritance.parent().find('div').css('background-position', '0px bottom');
    } else {
        inheritance.prop('checked', false);
        inheritance.parent().find('div').css('background-position', '0px 0px');
    }
}