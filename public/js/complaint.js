$(document).ready(function () {
    $('body').on('change','#ufas-checked', function(){
        $(this).find('option:selected').each(function() {
            var option = this;
            $('input[name="ufas_id"]').each(function(){
               $(this).val($(option).val());
            })
        });
    });

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
            auction.sendRequest(auction_id, false);
        } else {
            $('#auction_id').addClass('c-inp-error');
        }
    });


    $(".argument_text_container").on("click", "a", function () {
        argument.removeArgument($(this).attr("value"));
    });
    $("#edit_container").on("click", ".remove_template_from_edit", function () {
        argument.removeArgumentReq($(this).parent().parent().parent().attr('data-required'));
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

    $('#complaint_save').click(function (evt) {
        stopSaveCompl();
        evt.preventDefault();
    });
    $('#back_complaint_save').click(function (evt) {
        evt.preventDefault();
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

    $('body').on('click', '.add-popup-wrap--btn', function() {
        $(this).parent().parent().fadeOut();
        var obj = $(this);
        setTimeout(function() {
            $('.add-popup-wrap p').css({
                'font-size': '25px',
                'padding': '0'
            });
            obj.remove();
        }, 500);
    });

    $('#complaint_name').focusout(function() {
        var cmpl_name = $(this).val();
        checkComplaintName(cmpl_name);
    });

});

function checkComplaintName(name) {
    var name_allowed = true;
    if (name.length > 0) {
        $.ajax({
            type: "POST",
            url: '/complaint/isComplaintNameUnic',
            dataType: 'json',
            async: false,
            cache: false,
            data: {complaint_name: name, complaint_id: $("#complaint_id").val()},
            success: function(data) {
                if (!data.name_unic) {
                    complaint.showError('#complaint_name', 'Жалоба с таким именем уже существует в системе', 'before');
                    name_allowed = false;
                } else {
                    $('#complaint_name').parent().children('.c-inp-err-t').remove();
                    $('#complaint_name').addClass('c-inp-done');
                    name_allowed = true;
                }
            },
        });
    }
    return name_allowed;
}

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
    inn: '',
    arguments_data: '',

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
        if (applicant.id == 'All' || applicant.id == '' || applicant.id == ' ' || applicant.id == undefined || applicant.id.length == 0 ) {
            //$("html, body").animate({ scrollTop: 0 }, 1);
            showStyledPopupMessage("#pop-before-ask-question", "Ошибка", "Заявитель не выбран");
            return false;
        }
        for(var i = 0; i < applicant.id.length; i++){
            if(applicant.id[i] == 'All' || applicant.id[i] == '' || applicant.id[i] == " "){
                applicant.id.splice(i, 1);
            }
        }
        if (applicant.id.length > 1) {
            $(".ch-left").click();
            $("html, body").animate({ scrollTop: 0 }, 1);
            showStyledPopupMessage("#pop-before-ask-question", "Ошибка", "Пожалуйста, выберите только одного заявителя");
            return false;
        }
        if (this.needCat3 === true && this.selectCat3 !== true) {
            showStyledPopupMessage("#pop-before-ask-question", "Ошибка", "Прием заявок по данной закупке завершен, выберите хотя бы один довод «на отклонение заявки»");
            return false;
        }

        if (!auction.auctionReady)
            return false;

        $('#complaint_name').removeClass('c-inp-done');
        $('#complaint_name').removeClass('c-inp-error');
        this.complainName = $('#complaint_name').val();
        if (!validator.text(this.complainName, 3, 255)) {
            this.showError('#complaint_name', 'Ошибка! Полное наименование должно быть от 3 до 255 символов', 'before');
            return false;
        }
        if (!checkComplaintName(this.complainName)) {
            return false;
        }
        $('#complaint_name').addClass('c-inp-done');
        var ind = 0;
        $("#edit_container .template_edit").each(function() {
            /*var row_obj = {};
            row_obj["order"] = ind;
            row_obj["argument_id"] = $(this).attr("data-argument-id");
            row_obj["argument_text"] = $(this).find(".edit-textarea").html();*/
            complaint.arguments_data += "order===" + ind + "?|||?category_id===" + $(this).attr("data-category-id") +  "?|||?argument_id===" + $(this).attr("data-argument-id") + "?|||?argument_text===" + $(this).find(".edit-textarea").html() + "_?_";
            ++ind;
        });
        this.complainText = '';
        for (var key in argument.argumentList) {
            console.log(key);
            this.complainText += $('#edit_textarea_' + argument.argumentList[key]).html();
        }

        if (!validator.text(this.complainText, 2, 20000)) {
            // alert('текст жалобы должен быть');
            showStyledPopupMessage("#pop-before-ask-question", "Уведомление", "Не добавлен довод!");
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
        $("#auctionData").val(this.auctionData);
        $("#arguments_data").val(complaint.arguments_data + "");
        //$("#complaint_text").val(this.complainText);
        $("#complaint_name").val(this.complainName);
        $("#applicant_id").val(applicant.id);

        $(".edit-status p").text("Жалоба успешно сохранена!");
        $('.admin-popup-close, .admin-popup-bg').on('click', function() {
            $("#add-complaint-form").submit();
        });
        $(".edit-status").show();
        setTimeout( function(){
            $("#add-complaint-form").submit();
        }, 2000);


        /*$.ajax({
            type: 'POST',
            url: '/complaint/create',
            data: this.auctionData + '&complaint_text=' + this.complainText + '&complaint_name=' + this.complainName + '&applicant_id=' + applicant.id,
            success: function (data) {
                console.log(data);
                if (data.result == "success") {
                    window.location.href = "/complaint/edit/" + data.id;
                }
            },
            error: function (msg) {
                console.log(msg);
            }
        });*/

    },
    showError: function (element, msg, insert_here) {
        this.result = false;
        $(element).removeClass('c-inp-done');
        $(element).addClass('c-inp-error');
        $(element).parent().children('.c-inp-err-t').remove();
        switch (insert_here) {
            case 'before':
                $(element).before('<div class="c-inp-err-t">' + msg + '</div>');
                break;
            default:
                $(element).parent().append('<div class="c-inp-err-t">' + msg + '</div>');
                break;
        }

    },
    filterComplaintByApplicant: function (applicant_id) {
        var url = window.location.href;
/*return false;
        var splitter = url.split('applicant_id=' + applicant_id.join(","));*/
        if (!url.endsWith('applicant_id=' + applicant_id.join(","))){
        //if ((url.indexOf('/complaint/add') == -1 && url.indexOf('applicant_id=' + applicant_id.join(",")) == -1) || splitter[1] != "") {

            if (typeof currentStatus != "undefined" && currentStatus != '0') {
                $.ajax({
                    type: 'POST',
                    url: '/applicant/ajaxSetApplicantId',
                    data: 'applicant_id=' + applicant_id.join(","),
                    success: function (msg) {
                        applicant.applicant_info = msg.applicant_info;
                    },
                    error: function (msg) {
                        console.log(msg);
                    }

                });
                //window.location.href = '/complaint/index?status=' + currentStatus + '&applicant_id=' + applicant_id.join(",");
            } else {
                $.ajax({
                    type: 'POST',
                    url: '/applicant/ajaxSetApplicantId',
                    data: 'applicant_id=' + applicant_id.join(","),
                    success: function (msg) {
                        applicant.applicant_info = msg.applicant_info;
                    },
                    error: function (msg) {
                        console.log(msg);
                    }

                });
                //var to_url = '/complaint/index?applicant_id=' + applicant_id.join(",");
                //console.log(to_url);
                //window.location.href = to_url;
            }
        }

    }
};
var regFlags;
var drake = false;
var currTextArea = 0;
var argument = {
    argumentList: [],
    addArgument: function (id, cat_id, complaint_text, objReq) {
        regFlags = objReq;
        complaint_text = complaint_text || "";
        //templates["just_text"] = "Вы можете ввести свой текст здесь";
        templates["just_text"] = "";
        if (complaint.needCat3 === true && cat_id == 3) {

            complaint.selectCat3 = true;
        }


        if (id != "just_text") {
            this.argumentList.push(id);
        }
        if (id != "just_text") {
            var templateName = temp_name[id];
        } else {
            var templateName = "Пользовательский текст";
            c_text = "Пользовательский текст";
        }
        if (id != "just_text") {
            $('.argument_text_container').append('<span id="argument_text_container_' + id + '" class="atx argument_text_container_' + id + '">' + templateName + ' <a class="remove-argument" value="' + id + '"  ></a></span>');
        }

        var c_text = templates[id];
        if (complaint_text.length) {
            c_text = complaint_text;
        }

        if(id == "just_text"){
            c_text = "Пользовательский текст";
        }
        c_text = c_text.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">")
        if(cat_id != undefined && templateName != undefined) {
            var currTextArea = 'edit_textarea_' + id;
            if ($("#" + currTextArea).length) {
                currTextArea = currTextArea + (parseInt(Math.random() * 100000));
            }
            var html = '<div data-category-id="' + cat_id + '" data-argument-id="' + id + '" data-required="' + objReq + '" class="template_edit template_item" id="template_edit_' + id + '"><div class="c-edit-j-h">' +
                (( id != 'just_text' ) ? '<span>' + templateName + '</span>' : '') +
                '<div class="c-edit-j-h-ctr">' +
                '<a  class="template-edit-control down c-edit-j-h-ctr1">Переместить ниже</a> <a class="template-edit-control up c-edit-j-h-ctr2">Переместить выше</a>' +
                (( id != 'just_text' ) ? '<a class="remove_template_from_edit template-edit-control" value="' + id + '" >Удалить</a>' : '') +
                '</div>' +
                '</div>' +
                '<div class="c-edit-j-t"><div contenteditable class="edit-textarea" id="' + currTextArea + '" >' +
                c_text +
                '</div></div></div>';
            $('#edit_container').append(html);
            
            setTimeout(function () {
                if (drake !== false) {
                    drake.destroy(true);
                }
                //  drake = dragula([document.getElementById('edit_container')]);
                drake = dragula([document.getElementById('argument_text_container')]);


                initEditor(currTextArea);
            }, 100);

            /*if (objReq == 1 && $('#template_edit_just_text .c-edit-j-t p').text() == 'Пользовательский текст' ||
                $('#template_edit_just_text .c-edit-j-t p').text() == 'Вам необходимо выбрать хотябы одну обязательную жалобу!') {
                $('#template_edit_just_text .c-edit-j-t p').text('Пользовательский текст');
            }*/
        }
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
        if($(".template_edit").length <= 1){
            $(".c-jd2-f-edit-h, .c-jd2-f-edit, .c-jadd2-f-z").hide();
        }
    },
    removeArgumentReq: function(obj) {
        var reqItem = 0;
        var btnPush = '<div class="add-popup-wrap--btn">Ок</div>';
        $('.template_item').each(function() {
            if ($(this).attr('data-required') === '1') {
                reqItem++;
            }
        });
        if (obj === '1' && reqItem < 2) {
            $('.add-popup-wrapNew .admin-popup-content').append(btnPush);
            $('.add-popup-wrapNew h6').text('Внимание!');
            $('.add-popup-wrapNew p').css({
                'font-size': '20px',
                'padding': '0 20px'
            }).text('Срок окончания подачи заявок прошел, как минимум один довод должен быть на действие (бездействие) комиссии');
            $('.add-popup-wrapNew').fadeIn().css('display', 'flex');
            argObjSend.required = 0;
            //$('#template_edit_just_text .c-edit-j-t p').text('Вам необходимо выбрать хотябы одну обязательную жалобу!');
        }
    }
};
var auction = {
        auctionReady: false,
        responseData: {},
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
        sendRequest: function (auction_id, only_info) {
            $.ajax({
                type: 'POST',
                url: '/purchase/get',
                data: 'auction_id=' + auction_id,
                success: function (msg) {
                    var data = $.parseJSON(msg);
                    zakupka.info.type = data.info.type;
                    procedura.info.okonchanie = data.procedura.okonchanie_podachi;
                    complaint.inn = data.info.zakupku_osushestvlyaet_inn.substr(0, 2);
                    auction.responseData = data;
                    console.log(data);
                    if (!only_info) {
                        $('#edit_container').empty();
                        auction.succesRequest(data,auction_id);
                        auction.overdueData(data.procedura.okonchanie_podachi);
                    }
                },
                error: function (msg) {
                    console.log(msg);
                }

            });
        },
        overdueData: function(overdue) {
            var now = new Date(), mainArr = [], setTodayDate = [], flag = false;
            var getDateSplit = overdue.split(' '),
                getDateSplit1 = getDateSplit[0].split('.'),
                getDateSplit2 = getDateSplit[1].split(':');
            pushArr(getDateSplit1);
            pushArr(getDateSplit2);
            function pushArr(arr) {
                for (var i = 0; i < arr.length; i++) {
                    var newKey = parseInt(arr[i]);
                    mainArr.push(newKey);
                }
            }
            var day = mainArr[0], year = mainArr[2];
            mainArr[0] = year, mainArr[2] = day;
            setTodayDate.push(now.getFullYear());
            setTodayDate.push(now.getMonth());
            setTodayDate.push(now.getDate());
            setTodayDate.push(now.getHours());
            setTodayDate.push(now.getMinutes());
            for (var i = 0; i < 5; i++) {
                if (mainArr[i] < setTodayDate[i]) flag = true;
            }
            /*if (flag) {
                setTimeout(function () {
                    $('.c-edit-j-t p').text('Вам необходимо выбрать хотябы одну обязательную жалобу!');
                }, 1000);
            } else {
                setTimeout(function() {
                    $('.c-edit-j-t p').text('Пользовательский текст');
                }, 1000);
            }*/
        },
        succesRequest: function (data,auction_id) {
            if (auction.processData(data, auction_id)) {
                $('#auction_id').addClass('c-inp-done');
                $('#notice_button').css('display', 'none');
                $('#result_container').append('<b class="msg_status_parser">Данные Получены!</b>');
                auction.setData();
                $('.complaint-main-container').show();
                $('.more-information-block').show();
                $('.category-tamplate').show();
                $('.c-jadd3').show();
                //complaint.setHeader();
                $('.loading-gif').hide();
                auction.auctionReady = true;
               // argument.addArgument("just_text", "just_text");
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
            var str_type = this.data.type;
            str_type = str_type.toLowerCase();

            if(str_type.indexOf('электронный') != -1){
                $('.addArguments .type_complicant').val('electr_auction');
            } else if(str_type.indexOf('конкурс') != -1){
                $('.addArguments .type_complicant').val('concurs');
            } else if(tr_type.indexOf('котировок') != -1){
                $('.addArguments .type_complicant').val('kotirovok');
            } else if(str_type.indexOf('предложений') != -1){
                $('.addArguments .type_complicant').val('offer');
            } else{
                $('.addArguments .type_complicant').val('error');
            }

            $('#type').html(this.data.type);
            //$('.addArguments .type_complicant').val(this.data.type);
            $('.addArguments .dateoff').val(this.data.okonchanie_podachi);
            $('#purchases_made').html(this.data.purchases_made);
            $('#purchases_name').html(this.data.purchases_name);
            $('#contact').html(this.data.contact);

            var ufas_name = $("#ufas-list li[ufas-number='" + complaint.inn + "']").text();
            if (ufas_name.length == 0) {
                ufas_name = "УФАС не определен";
            }
            if (this.data.ufas_name){
                ufas_name = this.data.ufas_name;
            }
//ufas_name <input type="hidden" name="ufas_id" value="' + complaint.inn +'">
            if(window.ufasArr) {
                debugger;
                var ufasNonDetected = true;
                var temp_ufas = {
                   'name': ufasArr[0].name,
                    'number': ufasArr[0].number
                };
                var html = '<div class="c-jadd-lr-row"><span>Подведомственность УФАС</span><div class="c-jadd-lr-sel"><select id="ufas-checked">';
                for (var i = 0; i < ufasArr.length; i++) {
                    if((ufas_name == 'Уфас не определен' || ufas_name == 'УФАС не определен') && ufasNonDetected){
                        html += '<option selected>Уфас не определен</option>';
                        ufasNonDetected = false;
                    }
                    html += '<option '+ ((ufasArr[i].number == complaint.inn || ufasArr[i].number == comp_inn)? 'selected' : '') +' value="' + ufasArr[i].number + '">' + ufasArr[i].name + '</option>'
                }
                html += '</select></div></div><input type="hidden" name="ufas_id" value="' + ((complaint.inn) ? complaint.inn : (comp_inn) ? comp_inn : null)  + '">';
            } else {
                var html = '<div class="c-jadd-lr-row"><span>Подведомственность УФАС</span><div class="c-jadd-lr-sel">' + ufas_name + '</div></div> <input type="hidden" name="ufas_id" value="' + complaint.inn + '">';
            }


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

        },
        processHTML: function (text, value) {
            return '<div class="c-jadd-lr-row"><span>' + text + '</span><span class="auction-data" >' + value + '</span></div>';

        },
        clearData: function () {
            for (var key in this.data) {
                this.data[key] = '';
            }
        }
    };

function saveComplaintToDocxFile() {
    var loadFile = function(url, callback) {
        JSZipUtils.getBinaryContent(url, callback);
    };
    var custom_text = "";
    var custom_text_unformatted = "";
    var search_tags = ['span', 'strong', 'em', 'u'];
    var wrong_ck_formatting = [];
    var assoc_wrong_ck_formatting = {};
    var docx_generator_allowed = true;

    $(".edit-textarea.cke_editable").each(function(index, elem) {
        $(search_tags).each(function(s_tag_index, s_tag_value) {
            var entered_text = $(elem).html();
            $(entered_text).find(s_tag_value).each(function(inner_ind, inner_elem){
                var find_search_tags = $.grep(search_tags, function(value) {
                    return value != s_tag_value;
                });
                $(find_search_tags).each(function(f_s_index, f_s_value) {
                    if ($(inner_elem).html()[0] != '<' && !$(inner_elem).html().startsWith('<' + f_s_value) && $(inner_elem).html().search(f_s_value) > 0) {
                        docx_generator_allowed = false;
                        if ($.inArray($(inner_elem).html(), wrong_ck_formatting) == -1) {
                            wrong_ck_formatting.push($(inner_elem).html());
                            assoc_wrong_ck_formatting[s_tag_value] = $(inner_elem).html();
                        }
                    }
                });
            });
        });
    });
    if (docx_generator_allowed) {
        $(".edit-textarea.cke_editable").each(function(index, elem){
            custom_text += replaceWordTags($(elem).html() + "<br>", $(elem).attr("id"));
            custom_text_unformatted += replaceWordTags($(elem).text() + "<br>", $(elem).attr("id"));
        });
    
        if ($("#operator_etp").is(":checked")) {
            $file_to_load = "operator_etp.docx";
        } else {
            if (compare_dates(procedura.info.okonchanie)) {
                $file_to_load = "documentation.docx";
            } else {
                $file_to_load = "decline.docx";
            }
        }
        loadFile("/js/docx_generator/docx_templates/" + $file_to_load, function(err, content) {
            if (err) { console.log("eee"); throw e };
            doc = new Docxgen(content);
            doc.setData({
                "applicant_fio": applicant.applicant_info.fio_applicant,
                "applicant_address": applicant.applicant_info.address,
                "applicant_phone": applicant.applicant_info.telefone,
                "applicant_position": applicant.applicant_info.position,
                "applicant_email": applicant.applicant_info.email,
                "tip_zakupki": zakupka.info.type,
                "ufas": "г. Санкт-Петербургу (тестовое)",
                "dovod": custom_text,
                "zakaz_phone": auction.responseData.zakazchik[0] == null ? '' : auction.responseData.zakazchik[0].tel,
                "zakaz_kontaktnoe_lico": auction.responseData.zakazchik[0] == null ? '' : auction.responseData.zakazchik[0].kontaktnoe_lico,
                "zakaz_kontaktnoe_name": auction.responseData.zakazchik[0] == null ? '' : auction.responseData.zakazchik[0].name,
                "zakaz_address": auction.responseData.zakazchik[0] == null ? '' : auction.responseData.zakazchik[0].pochtovy_adres,
                "zakaz_mesto": "TEST mesto",
                "organiz_fio": auction.responseData.contact.dolg_lico,
                "organiz_phone": auction.responseData.contact.tel,
                "organiz_mesto": auction.responseData.contact.mesto_nahogdeniya,
                "organiz_address": auction.responseData.contact.pochtovy_adres,
                "izveshchenie": $("#auction_id").val(),
                "zakupka_name": auction.responseData.info.object_zakupki
                }
            );
            doc.render();
            out = doc.getZip().generate({type:"blob"});
              var data = new FormData();
              data.append('file', out);
              data.append('complaint_name', $('#complaint_name').val());
              data.append('complaint_id', $("#complaint_id").val());
              $.ajax({
                url :  "/complaint/saveBlobFile",
                type: 'POST',
                data: data,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data) {
                },
                error: function() {
                }
              });
        });
        
        loadFile("/js/docx_generator/docx_templates/" + $file_to_load, function(err, content) {
            if (err) { console.log("eee"); throw e };
            doc = new Docxgen(content);
            doc.setData({
                "applicant_fio": applicant.applicant_info.fio_applicant,
                "applicant_address": applicant.applicant_info.address,
                "applicant_phone": applicant.applicant_info.telefone,
                "applicant_position": applicant.applicant_info.position,
                "applicant_email": applicant.applicant_info.email,
                "tip_zakupki": zakupka.info.type,
                "ufas": $('.c-jadd-lr-sel').text(),
                "dovod": custom_text_unformatted,
                "zakaz_phone": auction.responseData.zakazchik[0] == null ? auction.responseData.contact.tel : auction.responseData.zakazchik[0].tel,
                "zakaz_kontaktnoe_lico": auction.responseData.zakazchik[0] == null ? auction.responseData.contact.dolg_lico : auction.responseData.zakazchik[0].kontaktnoe_lico,
                "zakaz_kontaktnoe_name": auction.responseData.zakazchik[0] == null ? auction.responseData.contact.name + ', ' + auction.responseData.contact.dolg_lico : auction.responseData.zakazchik[0].name,
                "zakaz_address": auction.responseData.zakazchik[0] == null ? auction.responseData.contact.pochtovy_adres : auction.responseData.zakazchik[0].pochtovy_adres,
                "zakaz_mesto": auction.responseData.zakazchik[0] == null ? auction.responseData.contact.mesto_nahogdeniya : "TEST mesto",
                "organiz_fio": auction.responseData.contact.name + ', ' + auction.responseData.contact.dolg_lico,
                "organiz_phone": auction.responseData.contact.tel,
                "organiz_mesto": auction.responseData.contact.mesto_nahogdeniya,
                "organiz_address": auction.responseData.contact.pochtovy_adres,
                "izveshchenie": $("#auction_id").val(),
                "zakupka_name": auction.responseData.info.object_zakupki
                }
            );
            doc.render();
            out = doc.getZip().generate({type:"blob"});
              var data = new FormData();
              data.append('file', out);
              data.append('complaint_name', $('#complaint_name').val());
              data.append('complaint_id', $("#complaint_id").val());
              $.ajax({
                url :  "/complaint/saveBlobFile?unformatted=1",
                type: 'POST',
                data: data,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data) {
                },
                error: function() {
                }
              });
        });
        return true;
    } else {
        var wrong_format_text = '';
        var open_close_tag = {
            '<span>':'</span>',
            '<strong>':'</strong>',
            '<em>':'</em>',
            '<u>':'</u>'
        };
        $.each(assoc_wrong_ck_formatting, function( key, value ) {
            var _en_text = '<' + key + '>' + value + open_close_tag['<' + key + '>'];
            wrong_format_text += '&bull;&nbsp;' + _en_text + '</br>';
            $(".edit-textarea.cke_editable").each(function(index, elem) {
                var entered_text = $(elem).html();
                entered_text = entered_text.replace(_en_text, '<font class="marker_red">' + _en_text + '</font>');
                $(elem).html(entered_text);
            });
        });
        showStyledPopupMessage("#pop-before-ask-question", "Ошибка", "Такое форматирование недопустимо:</br>" + wrong_format_text);
        return false;
    }
}
function incrementMenuCount() {
    var countAll = $('.menu-status-all').html();
    $('.menu-status-all').html(parseInt(countAll) + 1);

    var countDraft = $('.menu-status-draft').html();
    $('.menu-status-draft').html(parseInt(countDraft) + 1);
}
function initEditor(id) {
    if ( CKEDITOR.instances[id] ) {
        CKEDITOR.remove(CKEDITOR.instances[id]);
    }
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
        removeButtons: 'Blockquote,Indent,Outdent,About,RemoveFormat,Format,Font,Styles,Strike,Subscript,Superscript,Cut,Copy,Paste,PasteText,PasteFromWord,Undo,Redo,Scayt,Link,Unlink,Anchor,Image,Table,HorizontalRule,SpecialChar,Maximize,Source,BulletedList',
        removePlugins: 'Styles,Format',
        sharedSpaces: {
            top: 'itselem',
            left: 'itselem'
        }
    });
    // };
    editor.disableAutoInline = true;
    editor.config.extraPlugins = 'sharedspace,font';
    editor.config.allowedContent = true;

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

function stopSaveCompl() {
    var flag;
    if (regFlags == 1) {
        flag = false;
    } else {
        flag = true;
    }
    $('.template_item').each(function() {
        if ($(this).attr('data-required') == 1) flag = true;
    });
    if (flag) {
        if (complaint.prepareData()) {
            if (saveComplaintToDocxFile()) {
                complaint.saveAsDraft();
            }
        }
    } else {
        showStyledPopupMessage("#pop-before-ask-question", "Ошибка", "Необходимо выбрать обязательный довод");
    }
}


var navbar = (function () {

   return {
       test: function () {
           console.log(123);
       },
       test1: function () {

       }
   }
}());
// oop(инкапсуляция и наследование, полиморфизм), module,
// test app
// template (lodash, ...), module structure/ module data(setData(name, value), getData)
// Bem