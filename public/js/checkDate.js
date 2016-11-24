$(document).ready(function () {
    //SAVE
    $('#send_yfas, .send_to_ufas').on('click', function(){
        if(!$('#send_yfas').hasClass('skyColor')) return false;
        $.ajax({
            type: 'POST',
            url: '/complaint/checkDateOnOverdueComplaint',
            data: {date: auction.data.okonchanie_rassmotreniya},
            dataType: "json",
            success: function(res) {
                $('.send-uf').fadeIn().css('display', 'flex');
                if(res.status == 0){
                    $('.send-uf').find('.pop-done-txt').text(
                        'Дата не просрочена'
                    );
                }
                if(res.status == 1){
                    $('.send-uf').find('.pop-done-txt').text(
                       'Обратите внимание, сроки обжалования действий (бездействий) по закупке составляют десять дней с момента размещения в ЕИС соответствующего протокола.Подробная информация о сроках обжалования изложена в разделе «ЧАСТО ЗАДАВАЕМЫЕ ВОПРОСЫ», а также в ч. 3 и 4 статьи 105 Федерального закона от 05.04.2013 № 44-ФЗ  «О контрактной системе в сфере закупок товаров, работ, услуг для обеспечения государственных и муниципальных нужд».'
                    );
                }
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
