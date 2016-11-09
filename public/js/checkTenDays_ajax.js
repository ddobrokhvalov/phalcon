$(document).ready(function () {
    $('#pop-order form').on('submit', function(){
        $.ajax({
            type: 'POST',
            url: '/complaint/checkDateOnTenDays',
            data: $(this),
            dataType: "json",
            success: function(res) {

            }
        });
        return false;
    });
});
