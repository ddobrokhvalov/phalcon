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
                if(res.status && res.status == 'ok'){
                    $(form).parent().css('display', 'none')
                    $('#pop-done').css('display', 'block').animate({opacity: 1, top: '35%'}, 200);
                } else if(res.error){
                    $(this).find('.c-inp-err-t').text(res.error);
                }
            },
        });
        return false;
    });
});
