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
                    $(this).append(res.error);
                }
            },
        });
        return false;
    });
});