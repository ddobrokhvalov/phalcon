$(document).ready(function() {
    startCatArguments($('#argComplSelect .custom-options li'));
    $('#argComplSelect .custom-options').on('click', 'li', function() {
        $('#argComplBtn').slideDown(400);
        $('.argCompl-review li').css('color', '#000');
    });
    $('#argComplBtn').click(function(e) {
        nextStep(e);
    });
    $('.word-argCompl-input button').click(function(e) {
        searchStep(e)
    });
    $('.argCompl-review').on('click', 'li', function() {
        ajaxSendObj.showHideBtn($(this));
    });
    $('.steps-line span').click(function() {
        ajaxSendObj.stepsRewriteData($(this));
    });
    $('.argCompl-review').on('click', 'li', function() {
        argObjSend.choosenArgFunc($(this));
    });
    $('.btn-div').click(function() {
        argument.addArgument(argObjSend.id, argObjSend.cat_id, argObjSend.complaint_text);
        $('.admin-popup-wrap').fadeOut();
    });
});

function searchStep(e) {
    ajaxSendObj.step = 6;
    var searchValue = $('.word-argCompl-input input').val(),
        data = '?search=' + searchValue + '&step=' + ajaxSendObj.step;
    ajaxSendObj.sendRequest(data);
    e.preventDefault();
}

function nextStep(e) {
    if (ajaxSendObj.step == 6) {
        ajaxSendObj.step = 2;
    }
    var id = $('#argComplSelect .current-option').attr('data-value'),
        data = '?id=' + id + '&step=' + ajaxSendObj.step;
    ajaxSendObj.sendRequest(data);
    $('#argComplBtn').slideUp(400);
    e.preventDefault();
}

var readyDataCatArg = [];
function startCatArguments(objLi) {
    $(objLi).each(function() {
        var loadDataObj = new LoadData(
            $(this).attr('data-value'),
            $(this).attr('data-parent'),
            $(this).text()
        );
        readyDataCatArg.push(loadDataObj);
    });
    function LoadData(id, parent_id, name) {
        this.id = id;
        this.name = name;
        this.parent_id = parent_id;
    }
}

var argObjSend = {
    id: 0,
    cat_id: 0,
    complaint_text: '',
    choosenArgFunc: function(data) {
        this.id = data.attr('data-value');
        this.cat_id = data.attr('data-parent');
        this.complaint_text = data.text();
    }
};

var ajaxSendObj = {
    step: 2,
    stepsCacheArr: [],
    sendRequest: function(data) {
        $.ajax({
            type: "GET",
            url: "http://fas/complaint/ajaxStepsAddComplaint" + data,
            dataType: 'json',
            success: function (value) {
                if (ajaxSendObj.step != 6) {
                    ajaxSendObj.stepsCacheArr.push(value);
                    ajaxSendObj.showDopBlocks();
                    ajaxSendObj.withoutCatArg(value);
                    ajaxSendObj.writeSelectLi(value);
                    ajaxSendObj.writeListLi(value);
                    ajaxSendObj.changeLineSteps();
                    ajaxSendObj.stepIncrease();
                } else {
                    ajaxSendObj.searchWriteStep(value);
                }
            },
            error: function (xhr) {
                alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                + xhr.statusText + ' ResponseText:' + xhr.responseText);
            }
        });
    },
    writeListLi: function(data) {
        if (ajaxSendObj.step == 3 || ajaxSendObj.step == 4) {
            $('.argCompl-review li').remove();
            for (var i = 0; i < data.arguments.length; i++) {
                $('.argCompl-review ul').append(
                    '<li data-value="' + data.arguments[i].id +
                    '" data-parent="' + data.arguments[i].category_id +
                    '">' + data.arguments[i].name + '</li>'
                );
            }
        }
    },
    writeSelectLi: function(data) {
        $('#argComplSelect .custom-options li').remove();
        for (var i = 0; i < data.cat_arguments.length; i++) {
            $('#argComplSelect .custom-options div div:first').append(
                '<li class="argo"' +
                ' data-value="' + data.cat_arguments[i].id +
                '" data-parent="' + data.cat_arguments[i].parent_id +
                '">' + data.cat_arguments[i].name + '</li>'
            );
        }
    },
    withoutCatArg: function(data) {
        if (data.cat_arguments.length == 0) {
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
            $('.btn-div').fadeOut(400);
        } else if (ajaxSendObj.step == 3) {
            $('.last-argComplList').slideDown(400);
            $('#argComplSelect').slideDown(400);
            $('#argComplSelect .custom-options').on('click', 'li', function() {
                $('.btn-div').slideUp(400);
            });
        } else if ( ajaxSendObj.step == 4) {
            $('#argComplSelect').slideUp(400);
        }
    },
    showHideBtn: function(obj) {
        $('.argCompl-review li').css('color', '#000');
        obj.css('color', '#00aeef');
        $('.btn-div').slideDown(400);
        $('#argComplBtn').slideUp(400);
        $('#argComplSelect .custom-options').slideUp();
        $('#argComplSelect .current-option span').removeClass('rotate-icon');
        $('#argComplSelect .current-option div').removeClass('transDiv');
    },
    changeLineSteps: function() {
        if (ajaxSendObj.step == 2) {
            changeLine(1, 2);
            changeSteps(1);
            ajaxSendObj.behindParentSelect = $('.argo').attr('data-parent');
        } else if (ajaxSendObj.step == 3) {
            changeLine(2, 3);
            changeSteps(2);
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
    stepsRewriteData: function(obj) {
        if ($(obj).hasClass('back1')) {
            ajaxSendObj.stepsCacheArr = [];
            ajaxSendObj.step = 2;
            $('.last-argComplList').slideUp(400);
            $('#argComplSelect').slideDown(400);
            $('#argComplSelect .current-option span').text('Жалоба на положения документации');
            $('#argComplSelect .custom-options li').remove();
            for (var i = 0; i < readyDataCatArg.length; i++) {
                $('#argComplSelect .custom-options div div:first').append(
                    '<li class="argo"' +
                    ' data-value="' + readyDataCatArg[i].id +
                    '" data-parent="' + readyDataCatArg[i].parent_id +
                    '">' + readyDataCatArg[i].name + '</li>'
                );
            }
            reclassStepsLine(1);
            $('.steps-line:first span').removeClass('stepBack back1');
        }
        if ($(obj).hasClass('back2')) {
            fillingItems(1);
            lookingToFillSelect(0);
            reclassStepsLine(2);
        }
        if ($(obj).hasClass('back3')) {
            fillingItems(2);
            lookingToFillSelect(1);
            reclassStepsLine(3);
        }
        function reclassStepsLine(num) {
            $('.steps-line').removeClass('arg-dunStep arg-nextStep');
            $('.steps-line:nth-child(' + num + ')').addClass('arg-nextStep');
            if (num == 2) {
                $('.steps-line:nth-child(1)').addClass('arg-dunStep');
                $('.steps-line:nth-child(2) span, .steps-line:nth-child(3) span').attr('class', '');
            } else if (num == 3) {
                $('.steps-line:nth-child(1), .steps-line:nth-child(2)').addClass('arg-dunStep');
                $('.steps-line:nth-child(3) span').attr('class', '');
            }
        }
        function lookingToFillSelect(num) {
            for (var i = 0; i < ajaxSendObj.stepsCacheArr[num].cat_arguments.length; i++) {
                $('#argComplSelect .custom-options div div:first').append(
                    '<li class="argo"' +
                    ' data-value="' + ajaxSendObj.stepsCacheArr[num].cat_arguments[i].id +
                    '" data-parent="' + ajaxSendObj.stepsCacheArr[num].cat_arguments[i].parent_id +
                    '">' + ajaxSendObj.stepsCacheArr[num].cat_arguments[i].name + '</li>'
                );
            }
        }
        function fillingItems(num) {
            ajaxSendObj.stepsCacheArr.splice(num, ajaxSendObj.stepsCacheArr.length - num);
            ajaxSendObj.step = num + 2;
            if (num == 1) {
                $('.last-argComplList').slideUp(400);
            } else if (num == 2) {
                $('.last-argComplList').slideDown(400);
            }
            $('#argComplSelect').slideDown(400);
            $('#argComplSelect .current-option span').text(lookingParentId(num - 1));
            $('#argComplSelect .custom-options li').remove();
        }
        function lookingParentId(num) {
            var behindParId = ajaxSendObj.stepsCacheArr[num].cat_arguments[0].parent_id,
                behindName = '';
            if (num == 0) {
                for (var i = 0; i < readyDataCatArg.length; i++) {
                    var saveArr = readyDataCatArg[i].id;
                    if (saveArr == behindParId) {
                        behindName = readyDataCatArg[i].name;
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
    searchWriteStep: function(data) {
        $('#argComplSelect').slideUp(400);
        $('.last-argComplList').slideDown(400);
        $('.argCompl-review li').remove();
        for (var i = 0; i < data.arguments.length; i++) {
            $('.argCompl-review ul').append(
                '<li data-value="' + data.arguments[i].id +
                '" data-parent="' + data.arguments[i].category_id +
                '">' + data.arguments[i].name + '</li>'
            );
        }
        $('.steps-line:first').removeClass('arg-nextStep');
        $('.steps-line').addClass('arg-dunStep');
        $('.steps-line:first span').addClass('stepBack back1');
    }
};