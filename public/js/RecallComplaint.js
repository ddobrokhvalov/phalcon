$(document).ready(function () {
    $('.button-recall').on('click', function () {
        var input = $('.complaint-checkbox:checked').val();
        $.ajax({
            url: '/complaint/getInfoComplaint',
            type: 'POST',
            dataType: 'json',
            data: {date: input},
            success: function (infoComplaint) {
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
                        doc.setData({
                                "applicant_name": infoComplaint.applicant_name,
                                "applicant_position": infoComplaint.applicant_position,
                                "auction_id": infoComplaint.auction_id,
                                "date_create": infoComplaint.date_create,
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
                                signFile(data[0], infoComplaint.thumbprint);
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
