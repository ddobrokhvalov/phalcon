$(document).ready(function () {
    $('#pop-callback form').on('submit', function(){
        var form = $(this);
        $.ajax({
            type: 'POST',
            url: '/register/callback',
            data: $(this).serialize(),
            dataType: "json",
            context: form,
            success: function(res) {
                if (res.error) {
                    $(this).find('.c-inp-err-t').text(res.error);
                    $(this).find('input[type="text"]').addClass('c-inp-error');
                } else {
                    $('#pop-callback').hide();
                    $(this).find('input[type="text"]').removeClass('c-inp-error');
                    $('.pop-done-txt').html('В ближайшее время мы вам перезвоним!');
                    $('#pop-done').show().animate({opacity: 1, top: '35%'}, 200);
                }
            }
        });
        return false;
    });
});
