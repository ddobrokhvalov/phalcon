$(document).ready(function () {
    //SAVE
    $('#send_yfas').on('submit', function(){
        $.ajax({
            type: 'POST',
            url: '/complaint/checkDateOnOverdueComplaint',
            data: auction.data.okonchanie_rassmotreniya,
            dataType: "json",
            success: function(res) {

            }
        });
    });

    //RECALL
    $('').click(function(){
        $.ajax({
            type: 'POST',
            url: '/complaint/checkDateOnRecallComplaint',
            data: $('.complaint-checkbox:checked').val(),
            dataType: "json",
            success: function(res) {
            }
        });
    });

});
