var countSend = 0;
$(document).ready(function () {
    $('.podpisatEp').on('click', function () {
        $('.loading_save').css({'display':'inline-block'});
        if(countSend == 0) {
            countSend++;
            $.ajax({
                type: 'POST',
                url: '/complaint/sendComplaintToUfas',
                data: {complId: $("#complaint_id").val()},
                dataType: "json",
                success: function (res) {
                    if (res.status == 'ok') {
                        $('.loading_save').css({'display':'none'});
                        $('.send-uf').css({'display': 'none'});
                        $('.send-suc').css({'display': 'flex'});
                        setTimeout(function () {
                            location.reload();
                        }, 6000)
                    }
                }
            });
        }
    });
});
