var auction_id = 0;
var comp_id = null;
$(document).ready(function () {
    $('.recall-compl').on('click', function () {
        if($(this).hasClass('button_copy_deactive')) return false;
        var input = $('.complaint-checkbox:checked').val();
        if(!input) input = $("#complaint_id").val();
        comp_id = input;
        $.ajax({
            url: '/complaint/getInfoComplaint',
            type: 'POST',
            dataType: 'json',
            data: {date: input},
            success: function (infoComplaint) {
                console.log(infoComplaint);
                if (infoComplaint) {
                    var loadFile = function (url, callback) {
                        window.JSZipUtils.getBinaryContent(url, callback);
                    };
                    loadFile("/js/docx_generator/docx_templates/" + 'recall.docx', function (err, content) {
                        if (err) {
                            console.log("eee");
                            throw e;
                        }
                        doc = new Docxgen(content);
                        auction_id = infoComplaint.auction_id;
                        doc.setData({
                                "applicant_name": infoComplaint.applicant_name,
                                "applicant_position": infoComplaint.applicant_position,
                                "applicant_address": infoComplaint.applicant_address,
                                "applicant_phone": infoComplaint.applicant_phone,
                                "applicant_email": infoComplaint.applicant_email,
                                "auction_id": infoComplaint.auction_id,
                                "date_create": infoComplaint.date_create,
                                'applicant_fio': infoComplaint.applicant_fio,
                                'ufas_name': infoComplaint.ufas_name
                            }
                        );
                        doc.render();
                        out = doc.getZip().generate({type: "blob"});
                        var data = new FormData();
                        data.append('file', out);
                        data.append('complaint_name', 'recall');
                        data.append('complaint_id', input);
                        if (signSavedComplaint == true) {
                            data.append('applicant_id', applicant.applicant_id);
                        }
                        $.ajax({
                            url: "/complaint/saveBlobFile?recall=true&complaint_id=" + input,
                            type: 'POST',
                            data: data,
                            async: false,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function (data) {
                                data = JSON.parse(data);
                                signFileOriginName = data[2];
                                signFile(data[0], infoComplaint.thumbprint, refresh);
                            },
                            error: function () {
                            }
                        });
                    });
                }
            }
        });
        return false;
    });
});


function refresh(){
    $('.podpisatEp-popup').css({'display': 'none'});
    $('.button-recallRec').css({'display': 'none'});
    $('.recall-compl-popup').find('.pop-done-txt').text(
        'Жалоба на закупку №'+ auction_id +' была успешно отозвана'
    );
    $('.button-recallRec').css({'display': 'flex'});
    $('.recall-compl, .cancel-recall').css({'display': 'none'});
     $.ajax({
         url: "/complaint/recallChangeStaAndSendUfas",
         type: 'POST',
         data: {complaint_id: comp_id},
         dataType: 'json',
         success: function (data) {
             if(data.status == 'ok') {
                 setTimeout(function () {
                     location.reload();
                 }, 1000);
             }
         },
         error: function () {
         }
     });
}