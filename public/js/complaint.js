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
            auction.sendRequest(auction_id);
        } else {
            $('#auction_id').addClass('c-inp-error');
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
        if (complaint.prepareData())
            complaint.saveAsDraft();
    });
    $('.alert-box').on('click', 'div', function () {
        $('.alert-wrap, .alert-box').fadeOut(400);
    });
    $('.alert-substrate').on('click', function () {
        $('.alert-wrap, .alert-box').fadeOut(400);
    });


    $('.category-container').on('click', '.template_checkbox', function () {
        if ($(this).is(':checked')) {

            argument.addArgument($(this).val(), $(this).attr("category"));
        } else {
            argument.removeArgument($(this).val(), $(this).attr("category"));
        }
    });

    $('.argument_text_container').on('mouseup', '.atx', function () {
        alert('done');
    });

});

var complaint = {
    complainName: '',
    complainText: '',
    auctionData: '',
    complaint_id: false,
    cat1: false,
    cat2: false,
    cat3: false,
    needCat3: false,
    selectCat3: false,


    setHeader: function () {
        var now = new Date();
        //   var auction_end = new Date(auction.data.data_rassmotreniya.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1'));
        if (this.cat1 === false) {
            this.cat1 = $('.category-1').html();
            this.cat2 = $('.category-2').html();
            this.cat3 = $('.category-3').html();
        }
        var prehtml = ' <div class="c-jd2-cb-b category-tamplate category-';
        //  if (now < auction_end) {
        if (true) {
            $('.category-container').html(prehtml + '1">' + this.cat1 + '</div>' + prehtml + '2">' + this.cat2 + '</div>');
            $('.category-tamplate').show();
            this.needCat3 = false;

        } else {


            $('.category-container').html(prehtml + '3">' + this.cat3 + '</div>' + prehtml + '1">' + this.cat1 + '</div>' + prehtml + '2">' + this.cat2 + '</div>');
            $('.category-tamplate').show();
            this.needCat3 = true;

        }

    },
    prepareData: function () {

        if (this.needCat3 === true && this.selectCat3 !== true) {
            showSomePopupMessage('error', 'Прием заявок по данной закупке завершен, выберите хотя бы один довод «на отклонение заявки»');
            return false;
        }

        if (!auction.auctionReady)
            return false;

        $('#complaint_name').removeClass('c-inp-done');
        $('#complaint_name').removeClass('c-inp-error');
        this.complainName = $('#complaint_name').val();
        if (!validator.text(this.complainName, 3, 200)) {
            $('#complaint_name').addClass('c-inp-error');
            return false;
        }
        $('#complaint_name').addClass('c-inp-done');
        this.complainText = '';
        for (var key in argument.argumentList) {
            this.complainText += $('#edit_textarea_' + argument.argumentList[key]).html();
        }

        if (!validator.text(this.complainText, 2, 20000)) {
            // alert('текст жалобы должен быть');
            showSomePopupMessage('warning', 'текст жалобы должен быть');
            return false;
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
        return true;
    },
    saveAsDraft: function () {
        $.ajax({
            type: 'POST',
            url: '/complaint/create',
            data: this.auctionData + '&complaint_text=' + this.complainText + '&complaint_name=' + this.complainName + '&applicant_id=' + applicant.id,
            success: function (msg) {
                console.log(msg);
                //document.location.href = '/complaint/index';
                // alert('Сохранено успешно');
                showSomePopupMessage('info', 'Сохранено успешно');
                complaint.complaint_id = msg;
                incrementMenuCount();
            },
            error: function (msg) {
                console.log(msg);
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
var currTextArea = 0;
var argument = {
    argumentList: [],
    addArgument: function (id, cat_id) {

        if (complaint.needCat3 === true && cat_id == 3) {

            complaint.selectCat3 = true;
        }


        this.argumentList.push(id);
        var templateName = $('#template_' + id).html();
        $('.argument_text_container').append('<span id="argument_text_container_' + id + '" class="atx argument_text_container_' + id + '">' + templateName + ' <a class="remove-argument" value="' + id + '"  ></a></span>');

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
        currTextArea = 'edit_textarea_' + id;
        setTimeout(function () {
            if (drake !== false) {
                drake.destroy();
            }
          //  drake = dragula([document.getElementById('edit_container')]);
            drake = dragula([document.getElementById('argument_text_container')]);


            initEditor(currTextArea);
        }, 100);

    },
    removeArgument: function (id, cat_id) {
        if (complaint.needCat3 === true && cat_id == 3) {

            complaint.selectCat3 = false;
        }
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
        auctionReady: false,
        /* data: {
         auction_id: '',
         type: '',
         purchases_made: '',
         purchases_name: '',
         contact: '',
         date_start: '',
         date_end: '',
         date_opening: '',
         date_review: ''
         }, */

        data: {},
        sendRequest: function (auction_id) {
            $.ajax({
                type: 'POST',
                url: '/purchase/get',
                data: 'auction_id=' + auction_id,
                success: function (msg) {

                    var data = $.parseJSON(msg);
                    console.log(data);
                    auction.succesRequest(data,auction_id);

                },
                error: function (msg) {
                    console.log(msg);
                }

            });
        },
        succesRequest: function (data,auction_id) {
            if (auction.processData(data, auction_id)) {
                $('#auction_id').addClass('c-inp-done');
                $('#result_container').append('<b class="msg_status_parser">Данные Получены!</b>');
                //auction.setData();
                $('.complaint-main-container').show();
                $('.category-tamplate').show();
                //complaint.setHeader();
                $('#notice_button').show();
                $('.loading-gif').hide();
                auction.auctionReady = true;
            } else {
                $('#auction_id').addClass('c-inp-error');
                $('#result_container').append('<b style="color:red!important;" class="msg_status_parser">Ошибка!</b>');
                auction.clearData();
                auction.setData();
                $('#notice_button').show();
                $('.loading-gif').hide();
                auction.auctionReady = false;
            }
        },
        processData: function (data, auction_id) {

            if (data.info.type == undefined || data.contact.name == undefined)
                return false;

            if (validator.text(data.info.type, 3, 200))
                this.data['type'] = data.info.type;
            else
                return false;

            if (validator.text(data.contact.name), 3, 300)
                this.data['purchases_made'] = data.contact.name;
            else
                return false;

            if (validator.text(data.info.object_zakupki), 3, 500)
                this.data['purchases_name'] = data.info.object_zakupki;
            else
                return false;
            this.data['auction_id'] = auction_id;
            this.data['contact'] = data.contact.name + '<br>' +
                data.contact.pochtovy_adres + '<br>' +
                data.contact.dolg_lico + '<br>' +
                'E-mail: ' + data.contact.email + '<br>' +
                'Телефон: ' + data.contact.tel + '<br>';


            for (var key in data.procedura) {
                this.data[key] = data.procedura[key];
            }

            return true;
        }
        ,
        setData: function () {

            $('#type').html(this.data.type);
            $('#purchases_made').html(this.data.purchases_made);
            $('#purchases_name').html(this.data.purchases_name);
            $('#contact').html(this.data.contact);

            var html = '<div class="c-jadd-lr-row"><span>Наименование закупки</span><div class="c-jadd-lr-sel"><select><option>УФАС по г. Санкт-Петербургу</option><option>УФАС по г. Санкт-Петербургу</option><option>УФАС по г. Санкт-Петербургу</option></select></div></div>';
            if (this.data.type == 'Открытый конкурс') {
                html += this.processHTML('Дата и время начала подачи заявок', this.data.nachalo_podachi);
                html += this.processHTML('Дата и время окончания подачи заявок', this.data.okonchanie_podachi);
                html += this.processHTML('Дата и время вскрытия конвертов', this.data.vskrytie_konvertov);
                html += this.processHTML('Дата рассмотрения и оценки заявок', this.data.data_rassmotreniya);
            }
            if (this.data.type == 'Электронный аукцион') {
                html += this.processHTML('Дата и время начала подачи заявок', this.data.nachalo_podachi);
                html += this.processHTML('Дата и время окончания подачи заявок', this.data.okonchanie_podachi);
                html += this.processHTML('Дата проведения электронного аукциона', this.data.data_provedeniya);
                html += this.processHTML('Дата окончания срока рассмотрения первых частей заявок', this.data.okonchanie_rassmotreniya);
                html += this.processHTML('Время проведения электронного аукциона', this.data.vremya_provedeniya);
            }
            if (this.data.type == 'Конкурс с ограниченным участием') {
                html += this.processHTML('Дата и время начала подачи заявок', this.data.nachalo_podachi);
                html += this.processHTML('Дата и время окончания подачи заявок', this.data.okonchanie_podachi);
                html += this.processHTML('Дата и время вскрытия конвертов', this.data.vskrytie_konvertov);
                html += this.processHTML('Дата проведения предквалификационного отбора', this.data.data_provedeniya);
                html += this.processHTML('Дата рассмотрения и оценки заявок', this.data.data_rassmotreniya);
            }
            if (this.data.type == 'Запрос котировок') {
                html += this.processHTML('Дата и время начала подачи заявок', this.data.nachalo_podachi);
                html += this.processHTML('Дата и время окончания подачи заявок', this.data.okonchanie_podachi);
                html += this.processHTML('Дата и время проведения вскрытия конвертов, открытия доступа к электронным документам заявок', this.data.vskrytie_konvertov);
            }
            if (this.data.type == 'Повторный конкурс с ограниченным участием') {
                html += this.processHTML('Дата и время начала подачи заявок', this.data.nachalo_podachi);
                html += this.processHTML('Дата и время окончания подачи заявок', this.data.okonchanie_podachi);
                html += this.processHTML('Дата и время проведения вскрытия конвертов, открытия доступа к электронным документам заявок', this.data.vskrytie_konvertov);

                html += this.processHTML('Дата проведения предквалификационного отбора', this.data.data_provedeniya);
                html += this.processHTML('Дата рассмотрения и оценки заявок на участие в конкурсе', this.data.data_rassmotreniya);
            }
            if (this.data.type == 'Закрытый конкурс') {
                html += this.processHTML('Дата и время начала подачи заявок', this.data.nachalo_podachi);
                html += this.processHTML('Дата и время окончания подачи заявок', this.data.okonchanie_podachi);
                html += this.processHTML('Дата и время вскрытия конвертов', this.data.vskrytie_konvertov);
                html += this.processHTML('Дата рассмотрения и оценки заявок на участие в конкурсе', this.data.data_rassmotreniya);
            }
            if (this.data.type == 'Закрытый конкурс с ограниченным участием') {
                html += this.processHTML('Дата и время начала подачи заявок', this.data.nachalo_podachi);
                html += this.processHTML('Дата и время окончания подачи заявок', this.data.okonchanie_podachi);
                html += this.processHTML('Дата и время вскрытия конвертов', this.data.vskrytie_konvertov);
                html += this.processHTML('Дата проведения предквалификационного отбора', this.data.data_provedeniya);
                html += this.processHTML('Дата рассмотрения и оценки заявок на участие в конкурсе', this.data.data_rassmotreniya);
            }
            if (this.data.type == 'Запрос предложений') {
                html += this.processHTML('Дата и время начала подачи заявок', this.data.nachalo_podachi);
                html += this.processHTML('Дата и время окончания подачи заявок', this.data.okonchanie_podachi);
                html += this.processHTML('Дата и время вскрытия конвертов, открытия доступа к электронным документам заявок', this.data.vskrytie_konvertov);
                html += this.processHTML('Дата и время рассмотрения и оценки заявок участников', this.data.data_rassmotreniya);
                html += this.processHTML('Дата и время вскрытия конвертов с окончательными предложениями, открытия доступа к электронным документам окончательных документов', this.data.okonchanie_rassmotreniya);
            }
            if (this.data.type == 'Предварительный отбор') {
                html += this.processHTML('Дата и время начала подачи заявок', this.data.nachalo_podachi);
                html += this.processHTML('Дата и время окончания подачи заявок', this.data.okonchanie_podachi);
                html += this.processHTML('Дата и время проведения предварительного отбора', this.data.data_provedeniya);
            }


            $('.date-container').html(html);


            /* for (var key in this.data) {
             $('#' + key).html(this.data[key]);
             } */

        }
        ,
        processHTML: function (text, value) {
            return '<div class="c-jadd-lr-row"><span>' + text + '</span><span class="auction-data" >' + value + '</span></div>';

        }
        ,
        clearData: function () {
            for (var key in this.data) {
                this.data[key] = '';
            }
        }


    }
    ;

function incrementMenuCount() {
    var countAll = $('.menu-status-all').html();
    $('.menu-status-all').html(parseInt(countAll) + 1);

    var countDraft = $('.menu-status-draft').html();
    $('.menu-status-draft').html(parseInt(countDraft) + 1);
}
function initEditor(id) {
    var editor = CKEDITOR.inline(document.getElementById(id), {
        toolbarGroups: [
            {name: 'clipboard', groups: ['clipboard', 'undo']},
            {name: 'editing', groups: ['find', 'selection', 'spellchecker', 'editing']},
            {name: 'links', groups: ['links']},
            {name: 'insert', groups: ['insert']},
            {name: 'forms', groups: ['forms']},
            {name: 'tools', groups: ['tools']},
            {name: 'document', groups: ['mode', 'document', 'doctools']},
            {name: 'others', groups: ['others']},
            '/',
            {name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
            {name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi', 'paragraph']},
            {name: 'styles', groups: ['styles']},
            {name: 'colors', groups: ['colors']},
            {name: 'about', groups: ['about']}
        ],
        removeButtons: 'Underline,Subscript,Superscript,Cut,Copy,Paste,PasteText,PasteFromWord,Undo,Redo,Scayt,Link,Unlink,Anchor,Image,Table,HorizontalRule,SpecialChar,Maximize,Source',
        sharedSpaces: {
            top: 'itselem',
            left: 'itselem'
        }
    });
    // };
    editor.disableAutoInline = true;
    editor.config.extraPlugins = 'sharedspace';

}

function getOffsetSum(elem) {
    var object = document.getElementById('j-jd2-f-edit');
    var top=0, left=0
    while(elem) {
        top = top + parseFloat(elem.offsetTop)
        left = left + parseFloat(elem.offsetLeft)
        elem = elem.offsetParent
    }

    return {top: Math.round(top), left: Math.round(left)}
}

function getOffsetRect(elem) {
    // (1)
    var box = elem.getBoundingClientRect()

    // (2)
    var body = document.body
    var docElem = document.documentElement

    // (3)
    var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop
    var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft

    // (4)
    var clientTop = docElem.clientTop || body.clientTop || 0
    var clientLeft = docElem.clientLeft || body.clientLeft || 0

    // (5)
    var top  = box.top +  scrollTop - clientTop
    var left = box.left + scrollLeft - clientLeft

    return { top: Math.round(top), left: Math.round(left) }
}

function getOffset(elem) {
    if (elem.getBoundingClientRect) {
        // "правильный" вариант
        return getOffsetRect(elem)
    } else {
        // пусть работает хоть как-то
        return getOffsetSum(elem)
    }
}

function showSomePopupMessage(type, message) {
    $('.alert-wrap').fadeIn(400);
    setTimeout(function () {
        $('.alert-box').fadeIn(200).text(message);
        $('.alert-box').append('<div></div>');
    }, 400);
    if (type == 'info') {
        $('.alert-box').addClass('alert-info');
    }
}


function ajaxFileUpload(url, fileelementid) {
    var formData = new FormData();
    formData.append('file', $("#" + fileelementid)[0].files[0]);
    $.ajax({
        url : '/ajax/fileupload',
        type : 'POST',
        data : formData,
        processData: false,  // tell jQuery not to process the data
        contentType: false,  // tell jQuery not to set contentType
        enctype: 'multipart/form-data',
        success : function(data) {
            var postdata = {id:1, 'token':token}
            /*$.ajax({
                type: "POST",
                url: '/ajax/trusted?command=upload',
                dataType: 'json',
                cache: false,
                data: postdata,
                error: function(data){
                    console.log(data);
                },
                success: function(data) {
                    console.log(data);
                },
                timeout: 120000 // sets timeout to 2 minutes
            });*/
        }
    });
    return false;
}