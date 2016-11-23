$(document).ready(function () {
    $('.podpisatEp').on('click', function () {
        $.ajax({
            type: 'POST',
            url: '/complaint/sendComplaintToUfas',
            data: {complId: $("#complaint_id").val()},
            dataType: "json",
            success: function (res) {
                if(res.status == 'ok'){
                    $('.send-uf').css({'display': 'none'});
                    $('.send-suc').css({'display': 'flex'});
                    setTimeout(function(){
                        location.reload();
                    }, 6000)
                }
            }
        });
    });
});
