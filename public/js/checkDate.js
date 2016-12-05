$(document).ready(function () {
    //SAVE
    $('#send_yfas, .send_to_ufas').on('click', function(){
        if (!$('#send_yfas').hasClass('skyColor')) return false;
        $('.confirm-compl-popup').css({'display': 'flex'});
    });

    $('.compl-send').on('click', function(){
        sendToUfas();
    })


    function sendToUfas() {
        $.ajax({
            type: 'POST',
            url: '/complaint/checkDateComplaint',
            data: {
                type: auction.data.type,
                okonchanie_rassmotreniya: auction.data.okonchanie_rassmotreniya,
                okonchanie_podachi: auction.data.okonchanie_podachi,
                data_rassmotreniya: auction.data.data_rassmotreniya,
                vskrytie_konvertov: auction.data.vskrytie_konvertov,
                complaint_id: $("#complaint_id").val(),
            },
            dataType: "json",
            success: function (res) {
                $('.confirm-compl-popup').css({'display': 'none'});
                if (res.status == 0) {
                    if (res.rule == 1 || res.rule == 2) {
                        $('.send-suc').css({'display': 'none'});
                        $('.podpisatEp').trigger("click");
                    }
                }
                if (res.status == 1) {
                    if (res.rule == 1) {
                        $('.send-uf').fadeIn().css('display', 'flex');
                        $('.send-uf').find('.pop-done-txt').text(
                            'Отправить жалобу невозможно, так как срок окончания подачи заявок истёк. В силу ч. 3 и 4 статьи 105 Закона о контрактной системе после окончания срока подачи заявок жалоба может быть подана только на действия (бездействия) заказчика, уполномоченного органа, уполномоченного учреждения, специализированной организации, комиссии по осуществлению закупок, ее членов, должностного лица контрактной службы, контрактного управляющего. Чтобы отправить жалобу на данную закупку нажмите "КОПИРОВАТЬ" и добавьте один из предложенных системой доводов.'
                        );
                        $('.podpisatEp').css('display', 'none');
                    }

                    if (res.rule == 2) {
                        $('.send-uf').fadeIn().css('display', 'flex');
                        $('.send-uf').find('.pop-done-txt').text(
                            'Обратите внимание, сроки обжалования действий (бездействий) по закупке составляют десять дней с момента размещения в ЕИС соответствующего протокола.Подробная информация о сроках обжалования изложена в разделе «ЧАСТО ЗАДАВАЕМЫЕ ВОПРОСЫ», а также в ч. 3 и 4 статьи 105 Федерального закона от 05.04.2013 № 44-ФЗ  «О контрактной системе в сфере закупок товаров, работ, услуг для обеспечения государственных и муниципальных нужд».'
                        );
                    }
                }
            }
        });
    }


    //RECALL
    $('.button-recallRec').click(function(){
        checkPlugin();
        var idComp = $('.complaint-checkbox:checked').val()
        if(!idComp) idComp = $("#complaint_id").val();
        if(!pluginNotFound) return false;
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
