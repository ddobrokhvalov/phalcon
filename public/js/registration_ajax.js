$(document).ready(function () {
    $('.reg-new-user').on('submit', function(){
        var form = $(this);
        $.ajax({
            type: 'POST',
            url: '/register/index',
            data: $(this).serialize(),
            dataType: "json",
            context: form,
            success: function(res) {
                if (res.status){
                    switch (res.status) {
                        case 'ok':
                            $(this).find('input').each(function () {
                                $(this).val('');
                            })
                            $(this).parent().hide();
                            $('#overlay').hide();
                        break;
                    }
                } else if(res.error){
                    for(mess in res.error){
                        $(this).find('.error-reg').html('<p>'+ res.error[mess] + '</p>');
                    }
                }
            },
        });
        return false;
    });
});