$(document).ready(function() {
    $('.add-cat').click(function() {
        $('.add-Arguments_category').fadeIn().css('display', 'flex');
    });
    $('.cancel').click(function() {
        popupCancel();
    });
    $('.saveCat').click(function() {
        categorySend();
    });
});

function popupCancel() {
    $('.admin-popup-wrap').fadeOut();
    $('.inputBox input').val('');
}

function categorySend() {
    createNewCategory.newCategorySend();
}

var createNewCategory = {
    newCategorySend: function() {
        $.ajax({
            type: "GET",
            url: "http://fas/admin/arguments/ajaxGetCatArguments",
            data: data,
            dataType: 'json',
            success: function(value) {

            },
            error: function(xhr) {
                alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                    + xhr.statusText + ' ResponseText:' + xhr.responseText);
            }
        });
    }
};