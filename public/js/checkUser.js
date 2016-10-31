$(document).ready(function () {
    $('#checkUserFileds').click(function() {
        var form = $(this);
        $.ajax({
            type: 'POST',
            url: '/users/checkUser',
            data: $(this).serialize(),
            dataType: "json",
            context: form,
            success: function(res) {
                if (res.error) {
                    for (error in res.error) {
                        var keyName = error,
                            keyData = res.error[error];
                        $('.ch-r-s-inf-f input').each(function () {
                            var thisName = $(this).attr('name');
                            if (thisName === keyName) {
                                $(this).parent().find('.c-inp-err-t').text(keyData);
                            }
                        });
                    }
                    $('html, body').animate({scrollTop: $('body').offset().top}, 300);
                    $('.ch-r-sett-dd').slideToggle(300);
                }
            }
        });
        return false;
    });
});