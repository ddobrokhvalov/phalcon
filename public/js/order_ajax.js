$(document).ready(function () {
    $('#pop-order form').on('submit', function(){
        var form = $(this);
        $.ajax({
            type: 'POST',
            url: '/order/order',
            data: $(this).serialize(),
            dataType: "json",
            context: form,
            success: function(res) {
                if (res.status && res.status == 'ok'){
                    $(form).parent().hide();
                    $('#pop-done').show().animate({opacity: 1}, 200);
                } else if (res.error) {
                    $(this).find('.c-inp-err-t').text(res.error);
                    $(this).find('input[type="text"]').addClass('c-inp-error');
                }
            }
        });
        return false;
    });
});
