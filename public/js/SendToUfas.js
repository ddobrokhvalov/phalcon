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
                },
                error: function (jqXHR, exception) {
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                   alert(msg);
                   $('.loading_save').css({'display':'none'});
                }
            });
        }
    });
});
