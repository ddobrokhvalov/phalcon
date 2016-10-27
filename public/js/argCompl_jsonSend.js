$(document).ready(function() {
    $('.opacity-cap-compl').click(function() {
        if ($(this).hasClass('search-required')) {
            methodProcurement('hasReq');
        } else {
            methodProcurement();
        }
    });
    $('#argComplSelect .custom-options').on('click', 'li', function() {
        clickSelectLi($(this));
    });
    $('#argComplBtn').click(function(e) {
        $('.btn-div, #argComplBtn').hide();
        nextStep(e);
    });
    $('.word-argCompl-input button').click(function(e) {
        searchStep(e);
    });
    $('.argCompl-review').on('click', 'li', function() {
        ajaxSendObj.showHideBtn($(this));
        argObjSend.choosenArgFunc($(this));
        argGetReq($(this));
    });
    $('.steps-line span').click(function() {
        ajaxSendObj.stepsRewriteData($(this));
        $('.argComp').removeClass('argComp-descr');
        $('.btn-div-showArgDescr').removeClass('hideArgDescr').text('Просмотреть').hide();
    });
    $('.btn-div').click(function() {
        $(this).hide();
        $(".c-jd2-f-edit-h, .c-jd2-f-edit, .c-jadd2-f-z").show();
        $('.c-jd2-f-save input').show();
        argument.addArgument(
            argObjSend.id,
            argObjSend.cat_id,
            argObjSend.complaint_text.replace(/Описание:/, ''),
            argObjReqType
        );
        $('.admin-popup-wrap').fadeOut();
    });
    $('.steps-line span').hover(function() {
        if($(this).hasClass('stepBack')) {
            $(this).parent().find('div').toggleClass('stepBack-hover');
        }
    });
    $('.btn-div-showArgDescr').click(function() {
        showArgDescr($(this));
    });
});

var typeComplicant, datCome;
function methodProcurement(req) {
    if (readyDataCatArg != undefined) {
        ajaxSendObj.firstStepRewriteData();
    }
    typeComplicant = $('.type_complicant').val();
    datCome = $('.dateoff').val();
    if (req === 'hasReq') {
        argObjSend.required = 0;
        $('.template_item').each(function() {
            if ($(this).attr('data-required') === '1') {
                argObjSend.required = 1
                return;
            }
        });
        var data = 'step=' + startSend.step +
            '&type=' + typeComplicant +
            '&dateoff=' + datCome +
            '&checkrequired=' + argObjSend.required;
    } else {
        var data = 'step=' + startSend.step +
            '&type=' + typeComplicant +
            '&dateoff=' + datCome +
            '&checkrequired=' + argObjSend.required;
    }
    startSend.sendRequest(data);
    $('.argComp').removeClass('argComp-descr');
    $('.btn-div-showArgDescr').removeClass('hideArgDescr').text('Просмотреть').hide();
    $('.addArguments').fadeIn().css('display', 'flex');
}
function searchStep(e) {
    ajaxSendObj.step = 6;
    var searchValue = $('.word-argCompl-input input').val();
    if (searchValue.length > 0) {
        data = 'search=' + searchValue +
            '&step=' + ajaxSendObj.step +
            '&type=' + typeComplicant +
            '&dateoff=' + datCome +
            '&checkrequired=' + argObjSend.required;
        ajaxSendObj.sendRequest(data);
    }
    e.preventDefault();
}
function nextStep(e) {
    if (ajaxSendObj.step == 6) {
        ajaxSendObj.step = 2;
    }
    var id = $('#argComplSelect .current-option').attr('data-value'),
        data = 'id=' + id +
            '&step=' + ajaxSendObj.step +
            '&type=' + typeComplicant +
            '&dateoff=' + datCome +
            '&checkrequired=' + argObjSend.required;
    ajaxSendObj.sendRequest(data);
    $('#argComplBtn').slideUp(400);
    e.preventDefault();
}
function clickSelectLi(obj) {
    if (obj.hasClass('required')) {
        obj.parent().parent().parent().parent().find('.current-option span').addClass('required');
    }
    $('#argComplBtn').slideDown(400);
}
function showArgDescr(obj) {
    if (obj.hasClass('hideArgDescr')) {
        $('.argComp').removeClass('argComp-descr');
        obj.removeClass('hideArgDescr');
        obj.text('Просмотреть');
    } else {
        $('.argComp').addClass('argComp-descr');
        obj.addClass('hideArgDescr');
        obj.text('Скрыть');
    }
}
function argGetReq(obj) {
    var newReqFlag = false;
    $('.template_item').each(function() {
        if ($(this).attr('data-required') === '1') {
            newReqFlag = true;
        }
    });
    if (obj.hasClass('required')) {
        argObjSend.required = 1;
        argObjReqType = 1;
    }
    else if (newReqFlag) {
        argObjReqType = 0;
        argObjSend.required = 1;
    } else {
        argObjSend.required = 0;
    }
}
var argObjReqType = '';

var argObjSend = {
    id: 0,
    cat_id: 0,
    complaint_text: '',
    complaint_descr: '',
    complaint_comment: '',
    required: 0,
    choosenArgFunc: function(data) {
        this.id = data.attr('data-value');
        this.cat_id = data.attr('data-parent');
        this.complaint_text = data.find('.argTextCome').html();
        //this.complaint_text = data[0].innerText;
        this.complaint_descr = data.find('.argTextCome').html();
        this.complaint_comment = data.find('.argCommentCome').text();
        $('.argDescrBox_descr').html(this.complaint_descr);
        $('.argDescrBox_comment').text(this.complaint_comment);
    }
};
var readyDataCatArg;
var startSend = {
    step: 1,
    base_url: window.location.origin,
    sendRequest: function(data) {
        $.ajax({
            type: "POST",
            url: ajaxSendObj.base_url + "/complaint/ajaxStepsAddComplaint",
            data: data,
            dataType: 'json',
            success: function(value) {
                readyDataCatArg = value;
                ajaxSendObj.writeSelectLi(value);
            },
            error: function(xhr) {
                alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                    + xhr.statusText + ' ResponseText:' + xhr.responseText);
            }
        });
    }
};
var ajaxSendObj = {
    step: 2,
    stepsCacheArr: [],
    base_url: window.location.origin,
    sendRequest: function(data) {
        $.ajax({
            type: "POST",
            url: ajaxSendObj.base_url + "/complaint/ajaxStepsAddComplaint",
            data: data,
            dataType: 'json',
            success: function(value) {
                if (ajaxSendObj.step != 6) {
                    ajaxSendObj.stepsCacheArr.push(value);
                    ajaxSendObj.showDopBlocks();
                    ajaxSendObj.withoutCatArg(value);
                    ajaxSendObj.writeSelectLi(value);
                    ajaxSendObj.writeListLi(value);
                    ajaxSendObj.changeLineSteps(value);
                    ajaxSendObj.stepIncrease();
                } else {
                    ajaxSendObj.searchWriteStep(value);
                }
            },
            error: function(xhr) {
                alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                + xhr.statusText + ' ResponseText:' + xhr.responseText);
            }
        });
    },
    writeListLi: function(data) {
        if (ajaxSendObj.step == 3 || ajaxSendObj.step == 4) {
            $('.argCompl-review li').remove();
            if (data.arguments.length != 0) {
                for (var i = 0; i < data.arguments.length; i++) {
                    if (data.arguments[i].required == 1) {
                        $('.argCompl-review ul').append(
                            '<li class="required" data-value="' + data.arguments[i].id +
                            '" data-parent="' + data.arguments[i].category_id + '">' +
                            data.arguments[i].name +
                            '<div class="argTextCome">Описание: ' + data.arguments[i].text + '</div>' +
                            '<p class="argCommentCome">Комментарий юриста: ' + data.arguments[i].comment + '</p></li>'
                        );
                    } else {
                        $('.argCompl-review ul').append(
                            '<li data-value="' + data.arguments[i].id +
                            '" data-parent="' + data.arguments[i].category_id + '">' +
                            data.arguments[i].name +
                            '<div class="argTextCome">Описание: ' + data.arguments[i].text + '</div>' +
                            '<p class="argCommentCome">Комментарий юриста: ' + data.arguments[i].comment + '</p></li>'
                        );
                    }
                }
                $('.last-argComplList').slideDown(400);
            } else {
                $('.last-argComplList').slideUp(400);
            }
        }
    },
    writeSelectLi: function(data) {
        $('#argComplSelect .custom-options li').remove();
        if (ajaxSendObj.step == 3 && data.arguments.length != 0) {
            $('#argComplSelect').slideUp(400);
        } else {
            if (data.cat_arguments.length != 0) {
                $('#argComplSelect').slideDown(400);
            } else {
                $('#argComplSelect').slideUp(400);
            }
            for (var i = 0; i < data.cat_arguments.length; i++) {
                if (data.date == 1 && data.cat_arguments[i].required == 1) {
                    $('#argComplSelect .custom-options div div div:first').append(
                        '<li class="argo required"' +
                        ' data-value="' + data.cat_arguments[i].id +
                        '" data-parent="' + data.cat_arguments[i].parent_id +
                        '">' + data.cat_arguments[i].name + '</li>'
                    );
                } else {
                    $('#argComplSelect .custom-options div div div:first').append(
                        '<li class="argo"' +
                        ' data-value="' + data.cat_arguments[i].id +
                        '" data-parent="' + data.cat_arguments[i].parent_id +
                        '">' + data.cat_arguments[i].name + '</li>'
                    );
                }
            }
        }
    },
    withoutCatArg: function(data) {
        if (data.cat_arguments.length === 0) {
            $('#argComplSelect').slideUp(400);
        }
    },
    stepIncrease: function() {
        if (ajaxSendObj.step > 0 && ajaxSendObj.step < 5) {
            ajaxSendObj.step++;
        }
    },
    showDopBlocks: function() {
        if (ajaxSendObj.step < 3 && ajaxSendObj.step > 0) {
            $('.last-argComplList').slideUp(400);
            $('#argComplSelect').slideDown(400);
        } else if (ajaxSendObj.step == 3) {
            $('#argComplSelect .custom-options').on('click', 'li', function() {
                $('.btn-div').slideUp(400);
            });
        }
    },
    showHideBtn: function(obj) {
        $('.argCompl-review li').css('color', '#000');
        obj.css('color', '#00aeef');
        $('.btn-div, .btn-div-showArgDescr').slideDown(400);
        $('#argComplBtn').slideUp(400);
        $('#argComplSelect .custom-options').slideUp();
        $('#argComplSelect .current-option span').removeClass('rotate-icon');
        $('#argComplSelect .current-option div').removeClass('transDiv');
    },
    changeLineSteps: function(data) {
        if (ajaxSendObj.step == 2) {
            changeLine(1, 2);
            changeSteps(1);
            ajaxSendObj.behindParentSelect = $('.argo').attr('data-parent');
        } else if (ajaxSendObj.step == 3) {
            if (data.arguments.length != 0) {
                changeLine(3, 4);
                changeSteps(2);
                $('.steps-line:nth-child(2)').removeClass('arg-nextStep');
                $('.steps-line:nth-child(2)').addClass('arg-dunStep');
            } else {
                changeLine(2, 3);
                changeSteps(2);
            }
        } else if (ajaxSendObj.step == 4) {
            changeLine(3, 4);
            changeSteps(3);
            ajaxSendObj.behindParentSelect = $('.argo').attr('data-parent');
        } else if (ajaxSendObj.step == 5) {
            changeLine(4);
        }
        function changeLine(numb, numb2) {
            $('.steps-line:nth-child(' + numb + ')').removeClass('arg-nextStep');
            $('.steps-line:nth-child(' + numb + ')').addClass('arg-dunStep');
            $('.steps-line:nth-child(' + numb2 + ')').addClass('arg-nextStep');
        }
        function changeSteps(step) {
            $('.steps-line').each(function () {
                if ($(this).find('span').attr('data-step') == step) {
                    $(this).find('span').addClass('stepBack').addClass('back' + step);
                }
            });
        }
    },
    firstStepRewriteData: function() {
        ajaxSendObj.stepsCacheArr = [];
        ajaxSendObj.step = 2;
        $('.last-argComplList').slideUp(400);
        $('#argComplSelect').slideDown(400);
        $('#argComplSelect .current-option span').removeClass('required').text('Выберите категорию');
        $('#argComplSelect .custom-options li').remove();
        for (var i = 0; i < readyDataCatArg.cat_arguments.length; i++) {
            if (readyDataCatArg.date == 1 && readyDataCatArg.cat_arguments[i].required == 1) {
                $('#argComplSelect .custom-options div div:first').append(
                    '<li class="argo required"' +
                    ' data-value="' + readyDataCatArg.cat_arguments[i].id +
                    '" data-parent="' + readyDataCatArg.cat_arguments[i].parent_id +
                    '">' + readyDataCatArg.cat_arguments[i].name + '</li>'
                );
            } else {
                $('#argComplSelect .custom-options div div:first').append(
                    '<li class="argo"' +
                    ' data-value="' + readyDataCatArg.cat_arguments[i].id +
                    '" data-parent="' + readyDataCatArg.cat_arguments[i].parent_id +
                    '">' + readyDataCatArg.cat_arguments[i].name + '</li>'
                );
            }
        }
        ajaxSendObj.reclassStepsLine(1);
        $('.steps-line:first span').removeClass('stepBack back1');
    },
    stepsRewriteData: function(obj) {
        $('.argCompl-review ul').removeClass('withoutArgOnBase');
        obj.parent().find('div').removeClass('stepBack-hover');
        $('.btn-div').slideUp(400);
        if ($(obj).hasClass('back1')) {
            ajaxSendObj.firstStepRewriteData();
        }
        if ($(obj).hasClass('back2')) {
            fillingItems(1);
            lookingToFillSelect(0);
            ajaxSendObj.reclassStepsLine(2);
        }
        if ($(obj).hasClass('back3')) {
            fillingItems(2);
            lookingToFillSelect(1);
            ajaxSendObj.reclassStepsLine(3);
        }
        function lookingToFillSelect(num) {
            for (var i = 0; i < ajaxSendObj.stepsCacheArr[num].cat_arguments.length; i++) {
                $('#argComplSelect .custom-options div div:first').append(function() {
                    if (ajaxSendObj.stepsCacheArr[num].cat_arguments[i].required == 1) {
                        return '<li class="argo required"' +
                            ' data-value="' + ajaxSendObj.stepsCacheArr[num].cat_arguments[i].id +
                            '" data-parent="' + ajaxSendObj.stepsCacheArr[num].cat_arguments[i].parent_id +
                            '">' + ajaxSendObj.stepsCacheArr[num].cat_arguments[i].name + '</li>'
                    } else {
                        return '<li class="argo"' +
                        ' data-value="' + ajaxSendObj.stepsCacheArr[num].cat_arguments[i].id +
                        '" data-parent="' + ajaxSendObj.stepsCacheArr[num].cat_arguments[i].parent_id +
                        '">' + ajaxSendObj.stepsCacheArr[num].cat_arguments[i].name + '</li>'
                    }
                });
            }
        }
        function fillingItems(num) {
            ajaxSendObj.stepsCacheArr.splice(num, ajaxSendObj.stepsCacheArr.length - num);
            ajaxSendObj.step = num + 2;
            if (num == 1 || num == 2) {
                $('.last-argComplList').slideUp(400);
            }
            $('#argComplSelect .current-option span').text(lookingParentId(num - 1));
            $('#argComplSelect').slideDown(400);
            $('#argComplSelect .custom-options li').remove();
        }
        function lookingParentId(num) {
            var behindParId = ajaxSendObj.stepsCacheArr[num].cat_arguments[0].parent_id,
                behindName = '';
            if (num == 0) {
                for (var i = 0; i < readyDataCatArg.cat_arguments.length; i++) {
                    var saveArr = readyDataCatArg.cat_arguments[i].id;
                    if (saveArr == behindParId) {
                        behindName = readyDataCatArg.cat_arguments[i].name;
                    }
                }
            } else if (num == 1) {
                for (var i = 0; i < ajaxSendObj.stepsCacheArr[0].cat_arguments.length; i++) {
                    var saveArr = ajaxSendObj.stepsCacheArr[0].cat_arguments[i].id;
                    if (saveArr == behindParId) {
                        behindName = ajaxSendObj.stepsCacheArr[0].cat_arguments[i].name;
                    }
                }
            }
            return behindName;
        }
    },
    reclassStepsLine: function(num) {
        $('.steps-line').removeClass('arg-dunStep arg-nextStep');
        $('.steps-line:nth-child(' + num + ')').addClass('arg-nextStep');
        if (num == 1) {
            $('.steps-line span').attr('class', '');
        } else if (num == 2) {
            $('.steps-line:nth-child(1)').addClass('arg-dunStep');
            $('.steps-line:nth-child(2) span, .steps-line:nth-child(3) span').attr('class', '');
        } else if (num == 3) {
            $('.steps-line:nth-child(1), .steps-line:nth-child(2)').addClass('arg-dunStep');
            $('.steps-line:nth-child(3) span').attr('class', '');
        }
    },
    searchWriteStep: function(data) {
        $('.argCompl-review li').remove();
        $('#argComplSelect, #argComplBtn, .btn-div').slideUp(400);
        $('.last-argComplList').slideDown(400);
        if (data.arguments.length != 0) {
            $('.argCompl-review ul').removeClass('withoutArgOnBase');
            for (var i = 0; i < data.arguments.length; i++) {
                if (data.arguments[i].required == 1) {
                    $('.argCompl-review ul').append(
                        '<li class="required" data-value="' + data.arguments[i].id +
                        '" data-parent="' + data.arguments[i].category_id + '">' +
                        data.arguments[i].name +
                        '<div class="argTextCome">Описание: ' + data.arguments[i].text + '</div>' +
                        '<p class="argCommentCome">Комментарий юриста: ' + data.arguments[i].comment + '</p></li>'
                    );
                } else {
                    $('.argCompl-review ul').append(
                        '<li data-value="' + data.arguments[i].id +
                        '" data-parent="' + data.arguments[i].category_id + '">' +
                        data.arguments[i].name +
                        '<div class="argTextCome">Описание: ' + data.arguments[i].text + '</div>' +
                        '<p class="argCommentCome">Комментарий юриста: ' + data.arguments[i].comment + '</p></li>'
                    );
                }
            }
        } else {
            $('.argCompl-review ul').addClass('withoutArgOnBase');
            $('.argCompl-review ul.withoutArgOnBase').append(
                '<li>Данный довод отсутствует в базе!</li>'
            );
        }
        $('.steps-line:first').removeClass('arg-nextStep');
        $('.steps-line').addClass('arg-dunStep');
        $('.steps-line:first span').addClass('stepBack back1');
    }
};
