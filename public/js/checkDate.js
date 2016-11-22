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
    $('.button-recallRec').click(function(){

        var idComp = $('.complaint-checkbox:checked').val()
        $.ajax({
            type: 'POST',
            url: '/complaint/checkDateOnRecallComplaint',
            data: {'date':idComp },
            dataType: "json",
            success: function(res) {
                $('.recall-compl-popup').fadeIn().css('display', 'flex');
                if(res.status == 0){
                    $('.recall-compl-popup').find('.pop-done-txt').text(
                        'Уверены, что хотите отозвать жалобу "' + res.complaint.name
                        + '" на закупку №'+ res.complaint.auction_id + '?'
                    );
                }
                if(res.status == 1){
                    $('.recall-compl-popup').find('.pop-done-txt').text(
                        'Регламентированный срок принятия жалобы истек и, возможно, Ваша жалоба уже была рассмотрена по существу. УФАС вправе не принять направляемый отзыв.' +
                            'Уверены, что хотите отозвать жалобу "' + res.complaint.name + '" на закупку №'+ res.complaint.auction_id
                    );
                }
            }
        });
    });

    /*

     $('.recall-compl-popup').find('.pop-done-txt').text(
     'Уверены, что хотите отозвать жалобу "' +
     infoComplaint.ufas_name + '" на закупку №' +
     infoComplaint.auction_id + ' ?'
     );
     */
});
