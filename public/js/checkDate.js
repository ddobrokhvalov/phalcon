$(document).ready(function () {
    //SAVE
    $('').on('submit', function(){
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
    $('').on('submit', function(){
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
