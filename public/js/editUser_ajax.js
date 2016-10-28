

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
                if(res.status && res.status == 'ok'){
                    console.log(res.status);
                }else if(res.error){
                    console.log(res.error);
                }
            },
        });
        return false;
    });
});