$(document).ready(function() {
    $('.add-cat').click(function() {
        addNewCat();
    });
    $('.cancel').click(function() {
        popupCancel($(this));
    });
    $('.saveCat').click(function(e) {
        categorySend(e);
    });
    $('.argCatTree').on('click', '.category_delete', function() {
        prevDelCatBlock($(this));
    });
    $('.delChoosen').click(function() {
        deleteCatBlock();
    });
    $('.cancelDel').click(function() {
        $('.deleteChoosenItem').fadeOut();
    });
    $('.argCatTree').on('click', '.category_add', function() {
        if($(this).parent().find('.category_argumentAdd').length > 0) {
            $(this).parent().addClass('delete_category_argumentAdd');
        }
        createSubCat_Arg($(this));
    });
    $('.argCatTree').on('click', '.category_arrow, .category h2, #category h3', function() {
        toggleClick($(this));
    });
    $('.argCatTree').on('click', '.subWrap_3 #argument .itemTitle, .subWrap_2 #argument .itemTitle', function() {
        $(this).parent().find('.category_edit').click();
    });
    $('.argCatTree').on('click', '.category_argumentAdd', function() {
        if($(this).parent().find('.category_add').length > 0) {
            $(this).parent().addClass('delete_category_add');
        }
        addArgumentFunc($(this));
    });
    $('.argCatTree').on('click', '.category_edit', function() {
        editCatArg($(this).parent());
    });
    $('.addArgComment').click(function() {
        showArgComment($(this));
    });
    $('.requiredOrNot label').click(function() {
        $('.requiredOrNot').attr('data-value', $(this).attr('data-required'));
    });
    $('.argumentsComment textarea').keyup(function() {
        maxStrLength($(this), 1000, 'comment');
    });
    $('.warningMessageNew .popupBtn').click(function() {
        $('.warningMessageNew').fadeOut();
    });
    $('body').on('keyup', '.cke_textarea_inline', function() {
        maxStrLength($(this), 6000, 'tags');
    });
    $('.add-ArgumentsCategory input[type="text"]').keyup(function() {
        if ($(this).parent().parent().attr('data-obj') == 'category') {
            maxStrLength($(this), 50, 'category');
        } else {
            maxStrLength($(this), 160, 'argument');
        }
    });
    $('body').on('keyup', '.inputBox input[type="text"], .cke_textarea_inline', function() {
        if ($(this).val() != '' || $(this).text() != '') {
            $(this).css('border-color', '#10b8f7');
        }
    });
    $('.add-ArgumentsType .current-option').click(function() {
        if ($(this).attr('data-value') != '') {
            $('.add-ArgumentsType .current-option span').css('border-color', '#d3d3d3');
        }
    });
    
    requiredStartSearch();
});

function removeSecondActionButton() {
    if($('.delete_category_argumentAdd').length) {
        $('.delete_category_argumentAdd').find('.category_argumentAdd').remove();
    } else if($('.delete_category_add').length) {
        $('.delete_category_add').find('.category_add').remove();
    }
    deleteOldClasses();
}

var addCat = true, addArg = true;
var base_url = window.location.origin;
var catNum = '', parentId, shell, catArgObj, requiredCat, argId, thisIdDel, thisObj;
function categorySend() {
    var catName = $('.inputBox input').val(),
        data;
    if ($('.saveCat').hasClass('subChild')) {
        if ($('.inputBox input[type="text"]').val() == '') {
            $('.inputBox input[type="text"]').css('border-color', '#f26d7d');
        } else {
            catNum++;
            requiredCat = $('.requiredOrNot').attr('data-value');
            data = 'parent_id=' + parentId +
                '&name=' + catName +
                '&required=' + requiredCat;
            removeSecondActionButton();
            createNewCategory.newCategorySend(data);
            $('.add-Arguments_category .admin-popup-content').addClass('hiddenSaveBtn');
        }
    } else if ($('.saveCat').hasClass('createArgumentStart')) {
        if ($('.inputBox input[type="text"]').val() == '' && $('.cke_textarea_inline').text() == '') {
            $('.inputBox input[type="text"], .cke_textarea_inline').css('border-color', '#f26d7d');
        } else if ($('.inputBox input[type="text"]').val() == '') {
            $('.inputBox input[type="text"]').css('border-color', '#f26d7d');
        } else if ($('.cke_textarea_inline').text() == '') {
            $('.cke_textarea_inline').css('border-color', '#f26d7d');
        } else {
            $('.inputBox input[type="text"], .cke_textarea_inline').css('border-color', '#d3d3d3');
            $('.saveCat').text('Сохранить');
            $('.popupBtn.cancel').addClass('backPopupLevel').text('Назад');
            $('.saveCat').removeClass('createArgumentStart');
            if ($('.saveCat').hasClass('editAlso')) {
                $('.saveCat').addClass('editArgCat');
            } else {
                $('.saveCat').addClass('createArgument');
            }
            $('.argumentComments textarea').val('');
            if (catArgObj != undefined && catArgObj.type != undefined) {
                $('.add-ArgumentsType .current-option span').text('');
                $('.add-ArgumentsType .current-option').attr('data-value', catArgObj.type)
                var catArgObjType = catArgObj.type.split(',');
                for (var i = 0; i < catArgObjType.length; i++) {
                    switch (catArgObjType[i]) {
                        case 'electr_auction':
                            catArgObjType[i] = 'Электронный аукцион';
                            break;
                        case 'concurs':
                            catArgObjType[i] = 'Конкурс';
                            break;
                        case 'kotirovok':
                            catArgObjType[i] = 'Запрос котировок';
                            break;
                        case 'offer':
                            catArgObjType[i] = 'Запрос предложений';
                            break;
                    }
                    if (i == 0) {
                        $('.add-ArgumentsType .current-option span').append(catArgObjType[i]);
                    } else {
                        $('.add-ArgumentsType .current-option span').append(', ' + catArgObjType[i]);
                    }
                    $('.selectArgType_item').each(function () {
                        if ($(this).text() == catArgObjType[i]) {
                            $(this).addClass('choosenArgType');
                        }
                    });
                }
                selectArgType.name = catArgObjType;
            }
            $('.add-ArgumentsCategory').slideUp(400);
            $('.add-ArgumentsType').slideDown(400);
        }
    } else if ($('.saveCat').hasClass('createArgument')) {
        if ($('.selectArgType .current-option').attr('data-value') == '') {
            $('.add-ArgumentsType .current-option span').css('border-color', '#f26d7d');
        } else {
            var arrParam = {'arguments':{}};
            arrParam.arguments.category_id = parentId;
            arrParam.arguments.name = $('.inputBox input').val();
            arrParam.arguments.text = $('.argumentText textarea').val();
            arrParam.arguments.comment = $('.argumentsComment textarea').val();
            arrParam.arguments.type = [];
            argumentTypeVal = $('.add-ArgumentsType .current-option').attr('data-value'),
            argTypeValArray = argumentTypeVal.split(',');
            for (var i = 0; i < argTypeValArray.length; i++) {
                arrParam.arguments.type.push(argTypeValArray[i]);
            }
            removeSecondActionButton();
            addArgument.addData(arrParam);
            $('.add-Arguments_category .admin-popup-content').addClass('hiddenSaveBtn');
        }
    } else if ($('.saveCat').hasClass('editArgCat')) {
        if (catArgObj.descr == 'argument') {
            if ($('.selectArgType .current-option').attr('data-value') == '') {
                $('.add-ArgumentsType .current-option span').css('border-color', '#f26d7d');
            } else {
                catArgObj.name = $('.add-ArgumentsCategory .inputBox input').val();
                catArgObj.text = $('.add-ArgumentsCategory .argumentText textarea').val();
                catArgObj.type = $('.add-ArgumentsType .current-option').attr('data-value');
                catArgObj.comment = $('.argumentsComment textarea').val(),
                    argTypeValArray = catArgObj.type.split(',');

                var arrParam = {'edit':{}};
                arrParam.edit.id = argId;
                arrParam.edit.name = catArgObj.name;
                arrParam.edit.arg = true;
                arrParam.edit.text = catArgObj.text;
                arrParam.edit.comment = catArgObj.comment;
                arrParam.edit.type = [];
                for (var i = 0; i < argTypeValArray.length; i++) {
                    arrParam.edit.type.push(argTypeValArray[i]);
                }


                //data = 'edit[id]=' + argId +
                //    '&edit[name]=' + catArgObj.name +
                //    '&edit[arg]=true&edit[text]=' + catArgObj.text +
                //    '&edit[comment]=' + catArgObj.comment;
                //
                //for (var i = 0; i < argTypeValArray.length; i++) {
                //    data += ('&edit[type][]=' + argTypeValArray[i]);
                //}
                editCategoryArgument.editCatArg(arrParam, catArgObj.descr);
                $('.add-Arguments_category .admin-popup-content').addClass('hiddenSaveBtn');
            }
        } else {
            if ($('.inputBox input[type="text"]').val() == '') {
                $('.inputBox input[type="text"]').css('border-color', '#f26d7d');
            } else {
                catArgObj.name = $('.inputBox input').val();
                catArgObj.required = $('.requiredOrNot').attr('data-value');
                data = 'edit[id]=' + catArgObj.id +
                    '&edit[name]=' + catArgObj.name +
                    '&edit[required]=' + catArgObj.required;
                editCategoryArgument.editCatArg(data, catArgObj.descr, catArgObj.required, catArgObj.thisObj);
                $('.add-Arguments_category .admin-popup-content').addClass('hiddenSaveBtn');
            }
        }
    } else {
        if ($('.inputBox input[type="text"]').val() == '') {
            $('.inputBox input[type="text"]').css('border-color', '#f26d7d');
        } else {
            requiredCat = $('.requiredOrNot').attr('data-value');
            data = 'parent_id=' + 0 +
                '&name=' + catName +
                '&required=' + requiredCat;
            createNewCategory.newCategorySend(data);
        }
    }
}
function toggleClick(objClick) {
    if (objClick.parent().attr('data-toggle') == 'true') {
        objClick.parent().nextAll().remove();
        var data = 'id=' + objClick.parent().attr('data-id');
        receivingData.getSomeData(data, objClick.parent(), objClick.parent().attr('data-value'));
        objClick.parent().attr('data-toggle', 'false');
    } else {
        objClick.parent().nextAll().slideUp(400);
        objClick.parent().attr('data-toggle', 'true');
    }
}
function addNewCat() {
    $('.add-Arguments_category .admin-popup-content').removeClass('hiddenSaveBtn');
    $('.saveCat').attr('class', 'popupBtn saveCat');
    $('.add-Arguments_category input').text('').val('');
    $('.add-Arguments_category textarea').val('');
    $('.saveCat').text('Сохранить');
    if ($('.saveCat').hasClass('subChild')) {
        $('.saveCat').removeClass('subChild');
    }
    catNum = '';
    $('.add-ArgumentsCategory').attr('data-obj', 'category').show();
    $('.add-ArgumentsType, .argumentText').hide();
    $('.add-Arguments_category h6').text('Добавление категории');
    $('.add-ArgumentsCategory .inputBox:first h4').text('Название категории');
    $('.requiredOrNot').show();
    $('.add-Arguments_category').fadeIn().css('display', 'flex');
}
function createSubCat_Arg(obj) {
    $('.add-Arguments_category .admin-popup-content').removeClass('hiddenSaveBtn');
    if (obj.parent().parent().hasClass('subWrap_1')) {
        $('.requiredOrNot').hide();
    } else {
        $('.requiredOrNot').show();
    }
    $('.saveCat').attr('class', 'popupBtn saveCat');
    $('.add-Arguments_category input').text('').val('');
    $('.add-Arguments_category textarea').val('');
    $('.add-ArgumentsCategory').attr('data-obj', 'category').show();
    $('.add-ArgumentsType, .argumentText').hide();
    $('.saveCat').removeClass('editArgCat createArgumentStart').addClass('subChild').text('Сохранить');
    $('.popupBtn.cancel').removeClass('backPopupLevel').text('Отмена');
    catNum = obj.parent().attr('data-value');
    parentId = obj.parent().attr('data-id');
    $('.add-Arguments_category h6').text('Добавление категории');
    $('.add-ArgumentsCategory .inputBox:first h4').text('Название категории');
    $('.add-Arguments_category').fadeIn().css('display', 'flex');
}
function popupCancel(obj) {
    deleteOldClasses();
    $('.inputBox input[type="text"], .cke_textarea_inline').css('border-color', '#d3d3d3');
    if ($(obj).hasClass('backPopupLevel')) {
        $('.add-ArgumentsCategory').slideDown(400);
        $('.add-ArgumentsType').slideUp(400);
        $('.saveCat').removeClass('editArgCat').addClass('createArgumentStart');
        $('.saveCat').text('Добавить тип');
        $('.popupBtn.cancel').removeClass('backPopupLevel').text('Отмена');
    } else {
        $('.admin-popup-wrap').fadeOut();
        $('.inputBox input').val('');
        $('.argumentText').hide();
    }
}
function prevDelCatBlock(obj) {
    thisObj = obj.parent();
    thisIdDel = obj.parent().attr('data-id');
    var data = 'id=' + thisIdDel;
    deleteCatArgPreview.deleteCatArgSend(data, obj);
}
function deleteCatBlock() {
    var data;
    if (thisObj.attr('id') == 'argument') {
        data = 'id=' + thisIdDel + '&argument=true';
    } else {
        data = 'id=' + thisIdDel;
    }
    deleteCategory.deleteCategorySend(data, thisObj.parent());
}
function addArgumentFunc(obj) {
    $('.add-Arguments_category .admin-popup-content').removeClass('hiddenSaveBtn');
    $('.saveCat').attr('class', 'popupBtn saveCat');
    $('.add-Arguments_category input').text('').val('');
    destroyEditor("argument-text");
    $('.add-Arguments_category textarea, .argumentText textarea').val('');
    $('.addArgComment').removeClass('toggleArgComment');
    $('#addArgComments').prop('checked', false);
    $('.argumentsComment, .add-ArgumentsType, .requiredOrNot').hide();
    $('.add-ArgumentsCategory, .argumentText').show();
    $('.saveCat').removeClass('editArgCat subChild').addClass('createArgumentStart').text('Добавить тип');
    $('.popupBtn.cancel').removeClass('backPopupLevel').text('Отмена');
    parentId = obj.parent().attr('data-id');
    catNum = obj.parent().attr('data-value');
    $('.add-Arguments_category h6').text('Добавление довода');
    $('.add-ArgumentsCategory .inputBox:first h4').text('Название довода');
    $('.add-Arguments_category').fadeIn().css('display', 'flex');
    initEditor("argument-text");
    $('.add-ArgumentsCategory').attr('data-obj', 'argument');
}
function editCatArg(obj) {
    $('.add-Arguments_category .admin-popup-content').removeClass('hiddenSaveBtn');
    $('.saveCat').attr('class', 'popupBtn saveCat');
    $('.add-Arguments_category input').text('').val('');
    $('.add-Arguments_category textarea').val('');
    parentId = obj.attr('data-parent_id');
    if (obj.attr('id') == 'argument') {
        destroyEditor("argument-text");
        $('.requiredOrNot').hide();
        $('.saveCat').text('Добавить тип');
        $('.saveCat').addClass('createArgumentStart editAlso');
        $('.add-ArgumentsCategory, .argumentText').show();
        $('.add-ArgumentsCategory').attr('data-obj', 'argument');
        $('.add-ArgumentsType').hide();
        catArgObj = {
            descr: obj.attr('id'),
            name: obj.find('h3').text(),
            text: obj.find('.argumText').html(),
            type: obj.find('.argumentType').text(),
            comment: obj.find('.argumentComment').text()
        };
        if (catArgObj.text.search("&lt;") >= 0) {
            catArgObj.text = catArgObj.text.replace(/&lt;/g,'<').replace(/&gt;/g,'>');
        }
        argId = parseInt(obj.attr('data-id'))
        $('.inputBox input').val(catArgObj.name);
        $('.argumentText #argument-text')/*.text(catArgObj.text)*/.val(catArgObj.text);
        $('.add-Arguments_category h6').text('Редактирование довода');
        $('.add-ArgumentsCategory .inputBox:first h4').text('Название довода');
        $('.selectArgType_item').each(function() {
            if ($(this).attr('data-value') == catArgObj.type) {
                var thisType = $(this).text(),
                    thisTypeVal = $(this).attr('data-value');
                $('.add-ArgumentsType .current-option span').text(thisType);
                $('.add-ArgumentsType .current-option').attr('data-value', thisTypeVal);
            }
        });
        if (catArgObj.comment.length != 0) {
            $('.argumentsComment textarea').val(catArgObj.comment);
            $('.addArgComment').addClass('toggleArgComment');
            $('#addArgComments').prop('checked', true);
            $('.argumentsComment').show();
        }
        initEditor("argument-text");
    } else {
        if (obj.attr('data-required') == 0) {
            $('#requiredOrNot_item1').prop('checked', true);
        } else {
            $('#requiredOrNot_item2').prop('checked', true);
        }
        $('.requiredOrNot').attr('data-value', obj.attr('data-required'));
        $('.argumentText').hide();
        catArgObj = {
            descr: obj.attr('id'),
            name: obj.find('h3, h2').text(),
            id: parseInt(obj.attr('data-id')),
            thisObj: obj
        };
        $('.inputBox input').val(catArgObj.name);
        $('.saveCat').text('Сохранить');
        $('.saveCat').addClass('editArgCat');
        $('.add-Arguments_category h6').text('Редактирование категории');
        $('.add-ArgumentsCategory .inputBox:first h4').text('Название категории');
        $('.add-ArgumentsCategory').attr('data-obj', 'category');
        if (obj.parent().hasClass('subWrap_2')) {
            $('.requiredOrNot').hide();
        } else {
            $('.requiredOrNot').show();
        }
    }
    $('.add-Arguments_category').fadeIn().css('display', 'flex');
}
function showArgComment(obj) {
    if ($('#addArgComments').prop('checked') == false) {
        obj.addClass('toggleArgComment');
        $('.argumentsComment').slideDown(400);
    } else {
        obj.removeClass('toggleArgComment');
        $('.argumentsComment').slideUp(400);
        $('.argumentsComment textarea').val('');
    }
}
function requiredStartSearch() {
    $('.argCatTree > .catArguments .category').each(function() {
        if ($(this).attr('data-required') == 1) {
            $(this).addClass('dataRequired');
        } else {
            $(this).removeClass('dataRequired');
        }
    });
}
function maxStrLength(obj, num, descr) {
    if (descr == 'tags') {
        if (obj.text().length > num) {
            var temp = obj.text().substr(0, num);
            obj.text(temp);
            showMeWarningPopup('Описание не должно превышать ' + num + ' символов!');
        }
    } else if (descr == 'category') {
        rezSymbol(obj, num, 'Название каталога не должно превышать ' + num + ' символов!');
    } else if (descr == 'argument') {
        rezSymbol(obj, num, 'Название довода не должно превышать ' + num + ' символов!');
    } else if (descr == 'comment') {
        rezSymbol(obj, num, 'Комментарий не должен превышать ' + num + ' символов!');
    }
    function rezSymbol(oBj, nUm, text) {
        if (oBj.val().length > nUm) {
            oBj.val(oBj.val().substr(0, nUm));
            showMeWarningPopup(text);
        }
    }
}
function showMeWarningPopup(descr) {
    $('.warningMessageNew').fadeIn().css('display', 'flex');
    $('.warningMessageNew p').text(descr);
}
function ShellToFill(step, titleText, id, parent_id, dataRequired, text, comment, argumentType) {
    this.wrapp = '<li class="catArguments">';
    this.holder = '<ul class="subWrap_' + step + '">';
    this.box = '<div class="category" id="category" data-value="" data-id="' + id +
        '" data-parent_id="' + parent_id +
        '" data-required="' + dataRequired +
        '" data-toggle="true">';
    this.box2 = '<li class="category" id="category" data-value="' + step +
        '" data-id="' + id +
        '" data-parent_id="' + parent_id +
        '" data-required="' + dataRequired +
        '" data-toggle="true">';
    this.box3 = '<li class="category" id="argument" data-value="' + step +
        '" data-id="' + id +
        '" data-parent_id="' + parent_id +
        '" data-required="' + dataRequired +
        '" data-toggle="true">';
    this.arrow = '<div class="category_arrow"></div>';
    this.title = '<h2 class="itemTitle">' + titleText + '</h2>';
    this.title2 = '<h3 class="itemTitle">' + titleText + '</h3>';
    this.catAdd = '<div class="category_add">Добавить категорию</div>';
    if (addCat == true) this.catAdd2 = '<div class="category_add withoutText"></div>';
    if (addArg == true) this.argAdd = '<div class="category_argumentAdd"></div>';
    this.argAddCross = '<div class="category_argumentAdd crossView">Довод</div>';
    this.argText = '<div class="argumText">' + text + '</div>';
    this.argComment = '<p class="argumentComment">' + comment + '</p>';
    this.argType = '<p class="argumentType">' + argumentType + '</p>';
    this.catEdit = '<div class="category_edit"></div>';
    this.catDel = '<div class="category_delete"></div>';
}

var createNewCategory = {
    newCategorySend: function(data) {
            $.ajax({
            type: "POST",
            url: base_url + "/admin/arguments/ajaxAddCategory",
            data: data,
            dataType: 'json',
            success: function(value) {
                shell = new ShellToFill(
                    catNum,
                    value.name,
                    value.id,
                    value.parent_id,
                    value.required
                );
                popupCancel();
                switch(catNum) {
                    case 1:
                        createNewCategory.createSubCategory_1();
                        requiredStartSearch();
                        break;
                    case 2:
                        createNewCategory.createSubCategory_2();
                        requiredStartSearch();
                        break;
                    case 3:
                        createNewCategory.createSubCategory_3();
                        requiredStartSearch();
                        break;
                    default:
                        createNewCategory.createCategory();
                        requiredStartSearch();
                }
            },
            error: function(xhr) {
                alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                    + xhr.statusText + ' ResponseText:' + xhr.responseText);
            }
        });
    },
    createCategory: function() {
        $('.argCatTree').append(writeGetData.writeParentCategory());
    },
    createSubCategory_1: function() {
        $('.argCatTree > li .category').each(function() {
            if ($(this).attr('data-id') == parentId) {
                $(this).parent().append(writeGetData.subCategory_1());
            }
        });
    },
    createSubCategory_2: function() {
        createNewCategory.boxCreateSub(writeGetData.subCategory_2(), catNum);
    },
    createSubCategory_3: function() {
        createNewCategory.boxCreateSub(writeGetData.subCategory_3(), catNum);
    },
    boxCreateSub: function(valData, num) {
        num -= 1;
        $('.subWrap_' + num + ' .category').each(function() {
            if ($(this).attr('data-id') == parentId) {
                $(this).parent().append(valData);
            }
        });
    }
};

var addArgument = {
    addData: function(data) {
        $.ajax({
            type: "POST",
            url: base_url + "/admin/arguments/ajaxAddArguments",
            data: data,
            dataType: 'json',
            success: function(value) {
                var argNum = catNum;
                argNum++;
                shell = new ShellToFill(
                    argNum,
                    value.name,
                    value.id,
                    value.category_id,
                    value.required,
                    value.text,
                    value.comment,
                    value.type
                );
                popupCancel();
                $('.subWrap_' + catNum + ' .category').each(function() {
                    if ($(this).attr('data-id') == parentId) {
                        $(this).parent().append(writeGetData.subCategory_3());
                    }
                });
                requiredStartSearch();
            },
            error: function(xhr) {
                alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                    + xhr.statusText + ' ResponseText:' + xhr.responseText);
            }
        });
    }
};

var receivingData = {
    getSomeData: function(data, obj, num) {
        $.ajax({
            type: "GET",
            url: base_url + "/admin/arguments/ajaxGetCatArguments",
            data: data,
            dataType: 'json',
            context: obj.parent(),
            success: function(value) {
                if (value.cat_arguments.length == 0 && value.arguments.length == 0) {
                    showMeWarningPopup('Данные отсутствуют!');
                }
                num++;
                switch (num) {
                    case 1:
                        cycleDataCat(num, writeGetData.subCategory_1);
                        requiredStartSearch();
                        break;
                    case 2:
                        cycleDataCat(num, writeGetData.subCategory_2);
                        requiredStartSearch();
                        if (value.arguments.length != 0) cycleDataArg(num, writeGetData.subCategory_3);
                        break;
                    case 3:
                        cycleDataArg(num, writeGetData.subCategory_3);
                        requiredStartSearch();
                        break;
                }
                function cycleDataCat(numb, func) {
                    for (var i = 0; i < value.cat_arguments.length; i++) {
                        if (value.cat_arguments[i].count_arg > 0 ) {
                            addCat = false;
                            addArg = true;
                        } else if (value.cat_arguments[i].count_cat > 0) {
                            addCat = true;
                            addArg = false;
                        }
                        shell = new ShellToFill(
                            numb,
                            value.cat_arguments[i].name,
                            value.cat_arguments[i].id,
                            value.cat_arguments[i].parent_id,
                            value.cat_arguments[i].required
                        );
                        obj.parent().append(func());
                    }
                }
                function cycleDataArg(numb, func) {
                    for (var i = 0; i < value.arguments.length; i++) {
                        shell = new ShellToFill(
                            numb,
                            value.arguments[i].name,
                            value.arguments[i].id,
                            value.arguments[i].category_id,
                            value.arguments[i].required,
                            value.arguments[i].text,
                            value.arguments[i].comment,
                            value.arguments[i].type
                        );
                        obj.parent().append(func());
                    }
                }
                $(this).children().each(function () {
                    if ($(this).find('.category').attr('data-required') == 1) {
                        $(this).find('.category').addClass('dataRequired');
                    }
                });
            },
            error: function(xhr) {
                alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                    + xhr.statusText + ' ResponseText:' + xhr.responseText);
            }
        });
    }
};

var editCategoryArgument = {
    editCatArg: function(data, name, req, obj) {
        $.ajax({
            type: "POST",
            url: base_url + "/admin/arguments/ajaxEdit",
            data: data,
            context: obj,
            dataType: 'json',
            success: function(value) {
                if (name == 'argument') {
                    editCategoryArgument.renameArg(value);
                } else {
                    editCategoryArgument.renameCat(value, req, obj);
                }
                popupCancel();
            },
            error: function(xhr) {
                alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                    + xhr.statusText + ' ResponseText:' + xhr.responseText);
            }
        });
    },
    renameArg: function(val) {
        $('.argCatTree #argument').each(function() {
            var thisId = $(this).attr('data-id');
            if (thisId == val.id) {
                $(this).find('h3').text(val.name);
                $(this).find('.argumText').html(val.text);
                $(this).find('.argumentComment').text(val.comment);
                $(this).find('.argumentType').text(val.type);
            }
        });
    },
    renameCat: function(val, requi, obj) {
        $('.argCatTree #category').each(function() {
            var thisId = $(this).attr('data-id');
            if (thisId == val.id) $(this).find('h2, h3').text(val.name);
            if (thisId == val.id && requi === '1') {
                $(this).attr('data-required', requi);
            } else if (thisId == val.id && requi === '0') {
                $(this).attr('data-required', requi);
            }
        });
        if (val.required == 1) {
            obj.addClass('dataRequired');
            obj.nextAll().find('.category').each(function() {
                $(this).addClass('dataRequired');
            });
        } else {
            obj.removeClass('dataRequired');
            obj.nextAll().find('.category').each(function() {
                $(this).removeClass('dataRequired');
            });
        }
    }
};

var deleteCatArgPreview = {
    deleteCatArgSend: function(data, obj) {
        $.ajax({
            type: "GET",
            url: base_url + "/admin/arguments/ajaxGetCountCatArg",
            data: data,
            dataType: 'json',
            context: obj,
            success: function(value) {
                console.log(value);
                deleteCatArgPreview.createPopupContent(value, obj);
            },
            error: function(xhr) {
                alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                    + xhr.statusText + ' ResponseText:' + xhr.responseText);
            }
        });
    },
    createPopupContent: function(val, objData) {
        var objDatas = objData.parent().attr('id'),
            objName = objData.parent().find('.itemTitle')[0].innerText,
            catItemsArr = [];
        $('.delChoosenCatArg__name').html(function() {
            if (objDatas == 'category') {
                return 'Вы уверены, что хотите удалить категорию <span>' + objName + '</span> ?';
            } else {
                return 'Вы уверены, что хотите удалить довод <span>' + objName + '</span> ?';
            }
        });
        if (val.cat_arguments.length != 0) {
            for (var i = 0; i < val.cat_arguments.length; i++) {
                catItemsArr.push(' ' + val.cat_arguments[i]);
            }
            $('.delChoosenCatArg__catNames').html(function() {
                if (catItemsArr.length > 1) {
                    return 'В нее также входят подкатегории:<span>' + catItemsArr + '</span>';
                } else {
                    return 'В нее также входит подкатегория<span>' + catItemsArr + '</span>';
                }
            });
            $('.delChoosenCatArg__catNames').show();
        } else {
            $('.delChoosenCatArg__catNames').hide();
        }
        if (val.cat_count != 0) {
            $('.delChoosenCatArg__argNumber').html(
                'C общим количеством доводов: <span>' + val.cat_count + '</span>'
            ).show();
        } else {
            $('.delChoosenCatArg__argNumber').hide();
        }
        $('.deleteChoosenItem').fadeIn().css('display', 'flex');
    }
};

var deleteCategory = {
    deleteCategorySend: function(data, obj) {
        $.ajax({
            type: "POST",
            url: base_url + "/admin/arguments/ajaxRemove",
            data: data,
            dataType: 'json',
            context: obj,
            success: function() {
                deleteCategory.clearDOM($(this));
            },
            error: function(xhr) {
                alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                    + xhr.statusText + ' ResponseText:' + xhr.responseText);
            }
        });
    },
    clearDOM: function(obj) {
        $('.deleteChoosenItem').fadeOut();
        obj.slideUp(400);
        setTimeout(function() {
            obj.remove();
        }, 400);
    }
};

var writeGetData = {
    writeParentCategory: function() {
        return shell.wrapp +
            shell.box +
            shell.arrow +
            shell.title +
            shell.catAdd +
            shell.catEdit +
            shell.catDel +
            '</div></li>'
    },
    subCategory_1: function() {
        var _addCat = addCat;
        var _addArg = addArg;
        addCat = true;
        addArg = true;
        return '<li>' +
            shell.holder +
            shell.box2 +
            shell.arrow +
            shell.title2 +
            ((_addCat == true) ? shell.catAdd2 : '') +
            ((_addArg == true) ? shell.argAdd : '') +
            shell.catEdit +
            shell.catDel +
            '</ul></li>'
    },
    subCategory_2: function() {
        return '<li>' +
            shell.holder +
            shell.box2 +
            shell.arrow +
            shell.title2 +
            shell.argAddCross +
            shell.catEdit +
            shell.catDel +
            '</ul></li>'
    },
    subCategory_3: function() {
        return '<li>' +
            shell.holder +
            shell.box3 +
            shell.title2 +
            shell.argText +
            shell.argComment +
            shell.argType +
            shell.catEdit +
            shell.catDel +
            '</ul></li>'
    }
};

function destroyEditor(id) {
    if (CKEDITOR.instances[id]) {
        CKEDITOR.instances[id].destroy();
    }
}

function initEditor(id) {
    if ( CKEDITOR.instances[id] ) {
        CKEDITOR.instances[id].destroy();
        //CKEDITOR.remove(CKEDITOR.instances[id]);
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
        removeButtons: 'Blockquote,Indent,Outdent,About,RemoveFormat,Format,Strike,Subscript,Superscript,Cut,Copy,Paste,PasteText,PasteFromWord,Undo,Redo,Scayt,Link,Unlink,Anchor,Image,Table,HorizontalRule,SpecialChar,Maximize,Source,NumberedList,BulletedList',
        removePlugins: 'Styles,Format',

        sharedSpaces: {
            top: 'itselem',
            left: 'itselem'
        }
    });
    // };
    editor.disableAutoInline = true;
    editor.config.extraPlugins = 'sharedspace';
    editor.config.stylesSet = [
        { name: 'Обычный текст',        element: 'span',    attributes: { 'class': 'marker_white' } },
        { name: 'Выделенный текст',     element: 'span',    attributes: { 'class': 'marker_yellow' } }
    ];

    CKEDITOR.instances[id].on('blur', function() {
        $("#argument-text").val($(".cke_textarea_inline.cke_editable").html());
    });
    //CKEDITOR.stylesSet.add([
    //    { name: 'Обычный текст',        element: 'span',    styles: { 'background-color': 'white' } },
    //    { name: 'Выделенный текст',     element: 'span',    styles: { 'background-color': 'yellow' } }
    //]);
}