

$(document).ready(function () {
    $('#edit-user').on('submit', function(){
        var form = $(this);
        $.ajax({
            type: 'POST',
            url: '/users/changePassword',
            data: $(this).serialize(),
            dataType: "json",
            context: form,
            success: function(res) {
                
                if (res.status && res.status == 'ok') {
                    $('.ch-r-sett-dd').slideToggle(300);
                    $('.opacity-cap-compl').attr('data-userfields', '0');
                    $('.ch-r-s-inf-f.userData .c-inp-err-t').text('');
                } else if (res.error) {
                   
                    $('.ch-r-s-inf-f.userData .c-inp-err-t').text('');
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
                }
            },
        });
        return false;
    });
});