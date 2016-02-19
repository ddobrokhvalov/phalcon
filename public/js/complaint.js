$(document).ready(function () {
    $("#notice_button").click(function (event) {
        event.preventDefault();

        var auction_id = $('#auction_id').val();
        if (validator.numeric(auction_id, 5, 25)) {
            $('#auction_id').removeClass('c-inp-error');
            $('#auction_id').removeClass('c-inp-done');
            $('.msg_status_parser').remove();
            $('.complaint-main-container').hide();

            $('#notice_button').hide();
            $('.loading-gif').show();

            $.ajax({
                type: 'POST',
                url: '/purchase/get',
                data: 'auction_id=' + auction_id,
                success: function (msg) {

                    var data = $.parseJSON(msg);
                    console.log(data);

                    if (auction.processData(data, auction_id)) {
                        $('#auction_id').addClass('c-inp-done');
                        $('#result_container').append('<b class="msg_status_parser">Данные Получены!</b>');
                        auction.setData();
                        $('.complaint-main-container').show();
                        complaint.setHeader();
                        $('#notice_button').show();
                        $('.loading-gif').hide();
                    } else {
                        $('#auction_id').addClass('c-inp-error');
                        $('#result_container').append('<b style="color:red!important;" class="msg_status_parser">Ошибка!</b>');
                        auction.clearData();
                        auction.setData();
                        $('#notice_button').show();
                        $('.loading-gif').hide();
                    }
                },
                error: function (msg) {
                    alert(msg);
                }

            });

        } else {
            $('#auction_id').addClass('c-inp-error');
        }
    });

    $(".template_checkbox").click(function () {
        if ($(this).is(':checked')) {

            argument.addArgument($(this).val());
        } else {
            argument.removeArgument($(this).val());
        }

    });
    $(".argument_text_container").on("click", "a", function () {
        argument.removeArgument($(this).attr("value"));
    });
    $("#edit_container").on("click", ".remove_template_from_edit", function () {
        argument.removeArgument($(this).attr("value"));
    });

    $("#edit_container").on("click", ".down", function () {
        var pdiv = $(this).parent('div').parent('div').parent('div');
        pdiv.insertAfter(pdiv.next());
        return false
    });
    $("#edit_container").on("click", ".up", function () {
        var pdiv = $(this).parent('div').parent('div').parent('div');
        pdiv.insertBefore(pdiv.prev());
        return false
    });

    $('#complaint_save').click(function () {
        complaint.prepareData();
        complaint.saveAsDraft();
    });

    //bold_button
 //   $("#edit_container").on("mousedown", ".edit-textarea", function () {
  //      currentTextArea = $(this).attr("id");
  //  });
   /* $("#edit_container").on("mouseup", ".edit-textarea", function () {

        currentTextArea = $(this).attr("id");
        selectPosition = $(this).getcaretinfo(this);
    }); */

 /*   $('#bold_button').click(function(){

     if(currentTextArea !== false)
      complaint.setTag('#'+currentTextArea,'<strong>','</strong>');
     //$('#'+currentTextArea).selection('insert', {text: '<strong>', mode: 'before'}).selection('insert', {text: '</strong>', mode: 'after'});
     });
  */


});
//var currentTextArea = false;
////var selectPosition = false;
var complaint = {
    complainName: '',
    complainText: '',
    auctionData: '',
    saveDraft: false,

  /*  setTag:function(element,tag1,tag2){
        var tmp = selectPosition,
            orig = $(element).html();
        var res = orig.substr(0, tmp.start) + tag1 + orig.substr(tmp.start);
        console.log(res);
        $(element).html(res);
        selectPosition.start += tag1.length;
        selectPosition.end += tag1.length;
        //


        console.log(res);
        res =res.substr(0, tmp.end) + tag2 + res.substr(tmp.end);
        console.log(res);
        $(element).html(res);
    }, */
    setHeader:function(){
        var now = new Date();
        var auction_end = new Date(auction.data.date_end.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1'));

        if(now > auction_end){
             $('.complaint-header').html('Жалоба на действия комиссии');
        }else{
             $('.complaint-header').html('Жалоба на документацию или действия/бездействия заказчика');
        }

    },
    prepareData: function () {

        this.complainName = $('#complaint_name').val();
        this.complainText = '';
        for (var key in argument.argumentList) {
            this.complainText += $('#edit_textarea_' + argument.argumentList[key]).val();
        }

        this.auctionData = '';
        var k = 0;
        for (var i in auction.data) {
            if (k == 0) {
                this.auctionData += i + '=' + auction.data[i];
                k += 1;
            } else {
                this.auctionData += '&' + i + '=' + auction.data[i];
            }
        }

    },
    saveAsDraft: function () {
        $.ajax({
            type: 'POST',
            url: '/complaint/create',
            data: this.auctionData + '&complaint_text=' + this.complainText + '&complaint_name=' + this.complainName + '&applicant_id=' + applicant.id,
            success: function (msg) {
                console.log(msg);
                //document.location.href = '/complaint/index';
                alert('Сохранено успешно');
                this.saveDraft = true;
            },
            error: function (msg) {
                alert(msg);
            }

        });

    },
    filterComplaintByApplicant: function (applicant_id) {
        var url = window.location.href;

        if (url.indexOf('applicant_id=' + applicant_id) == -1) {

            if (currentStatus != '0') {
                document.location.href = '/complaint/index?status=' + currentStatus + '&applicant_id=' + applicant_id;
            } else {
                document.location.href = '/complaint/index?applicant_id=' + applicant_id;
            }
        }

    }
};
var drake = false;
var argument = {
    argumentList: [],
    addArgument: function (id) {
        this.argumentList.push(id);
        var templateName = $('#template_' + id).html();
        $('.argument_text_container').append('<span class="atx argument_text_container_' + id + '">' + templateName + ' <a class="remove-argument" value="' + id + '"  ></a></span>');

        var html = '<div class="template_edit" id="template_edit_' + id + '"><div class="c-edit-j-h">' +
            '<span>' + templateName + '</span>' +
            '<div class="c-edit-j-h-ctr">' +
            '<a  class="template-edit-control down c-edit-j-h-ctr1">Переместить ниже</a> <a class="template-edit-control up c-edit-j-h-ctr2">Переместить выше</a>' +
            '<a class="remove_template_from_edit template-edit-control" value="' + id + '" >Удалить</a>' +
            '</div>' +
            '</div>' +
            '<div class="c-edit-j-t"><div contenteditable class="edit-textarea" id="edit_textarea_' + id + '" >' +
            templates[id] +
            '</div></div></div>';
        $('#edit_container').append(html);

        setTimeout(function () {
            if (drake !== false) {
                drake.destroy();

            }
            drake = dragula([document.getElementById('edit_container')]);


        }, 100);

    },
    removeArgument: function (id) {
        var index = this.argumentList.indexOf(id);
        if (index > -1) {
            this.argumentList.splice(index, 1);
        }
        $('.argument_text_container_' + id).remove();
        $('#template_edit_' + id).remove();
        $('#jd2cbb' + id).prop('checked', false);
    }

};
var auction = {
    data: {
        auction_id: '',
        type: '',
        purchases_made: '',
        purchases_name: '',
        contact: '',
        date_start: '',
        date_end: '',
        date_opening: '',
        date_review: ''
    },
    processData: function (data, auction_id) {

        if (data.info.type == undefined || data.contact.name == undefined)
            return false;

        if (validator.text(data.info.type, 3, 200))
            this.data.type = data.info.type;
        else
            return false;

        if (validator.text(data.contact.name), 3, 300)
            this.data.purchases_made = data.contact.name;
        else
            return false;

        if (validator.text(data.info.object_zakupki), 3, 500)
            this.data.purchases_name = data.info.object_zakupki;
        else
            return false;
        this.data.auction_id = auction_id;
        this.data.contact = data.contact.name + '<br>' +
            data.contact.pochtovy_adres + '<br>' +
            data.contact.dolg_lico + '<br>' +
            'E-mail: ' + data.contact.email + '<br>' +
            'Телефон: ' + data.contact.tel + '<br>';
        this.data.date_start = data.procedura.nachalo_podachi;
        this.data.date_end = data.procedura.okonchanie_podachi;
        this.data.date_opening = data.procedura.vskrytie_konvertov;
        this.data.date_review = data.procedura.data_provedeniya  + ' ' + data.procedura.vremya_provedeniya;

        return true;
    },
    setData: function () {
        for (var key in this.data) {
            $('#' + key).html(this.data[key]);
        }
    },
    clearData: function () {
        for (var key in this.data) {
            this.data[key] = '';
        }
    }


};
