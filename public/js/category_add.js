$(document).ready(function() {
    $('.add-cat').click(function() {
        addNewCat();
    });
    $('.cancel').click(function() {
        popupCancel();
    });
    $('.saveCat').click(function() {
        categorySend();
    });
    $('.argCatTree').on('click', '.category_delete', function() {
        deleteCatBlock($(this));
    });
    $('.argCatTree').on('click', '.category_add', function() {
        createSubCat_Arg($(this));
    });
    $('.argCatTree').on('click', '.category_arrow, .category h2, .category h3', function() {
        toggleClick($(this));
    });
    $('.argCatTree').on('click', '.category_argumentAdd', function() {
        addArgumentFunc($(this));
    });
    $('.argCatTree').on('click', '.category_edit', function() {
        editCatArg($(this).parent());
    });
    $('.addArgComment').click(function() {
        showArgComment($(this));
    });
});

var catNum = '', parentId, shell, catArgObj, requiredCat, argType, argComm;
function categorySend() {
    var catName = $('.inputBox input').val(),
        data;
    if ($('.saveCat').hasClass('subChild')) {
        catNum++;
        data = 'parent_id=' + parentId +
            '&name=' + catName +
            '&required=' + requiredCat;
        createNewCategory.newCategorySend(data);
    } else if ($('.saveCat').hasClass('createArgumentStart')) {
        $('.saveCat').text('Сохранить');
        $('.saveCat').removeClass('createArgumentStart').addClass('createArgument');
        $('.add-ArgumentsCategory').slideUp(400);
        $('.add-ArgumentsType').slideDown(400);
    } else if ($('.saveCat').hasClass('createArgument')) {
        var argumentName = $('.inputBox input').val(),
            argumentText = $('.argumentText textarea').val(),
            argumentComm = $('.argumentComment textarea').val(),
            argumentTypeVal = $('.add-ArgumentsType .current-option').attr('data-value');
        data = 'arguments[category_id]=' + parentId +
            '&arguments[name]=' + argumentName +
            '&arguments[text]=' + argumentText +
            '&arguments[type]=' + argumentTypeVal +
            '&arguments[comment]=' + argumentComm;
        addArgument.addData(data);
    } else if ($('.saveCat').hasClass('editArgCat')) {
        if (catArgObj.descr == 'argument') {
            catArgObj.name = $('.inputBox input').val();
            catArgObj.text = $('.argumentText textarea').val();
            data = 'edit[id]=' + catArgObj.id +
                '&edit[name]=' + catArgObj.name +
                '&edit[arg]=true&edit[text]=' + catArgObj.text +
                '&arguments[type]=' + argType +
                '&arguments[comment]=' + argComm;
            editCategoryArgument.editCatArg(data, catArgObj.descr);
        } else {
            catArgObj.name = $('.inputBox input').val();
            data = 'edit[id]=' + catArgObj.id +
                '&edit[name]=' + catArgObj.name;
            editCategoryArgument.editCatArg(data, catArgObj.descr);
        }
    } else {
        data = 'parent_id=' + 0 +
            '&name=' + catName +
            '&required=' + requiredCat;
        createNewCategory.newCategorySend(data);
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
    if ($('.saveCat').hasClass('subChild')) {
        $('.saveCat').removeClass('subChild');
    }
    catNum = '';
    $('.argumentText').hide();
    $('.add-Arguments_category h6').text('Добавление категории');
    $('.add-Arguments_category').fadeIn().css('display', 'flex');
}
function createSubCat_Arg(obj) {
    $('.argumentText').hide();
    $('.saveCat').addClass('subChild');
    catNum = obj.parent().attr('data-value');
    parentId = obj.parent().attr('data-id');
    $('.add-Arguments_category h6').text('Добавление категории');
    $('.add-Arguments_category').fadeIn().css('display', 'flex');
}
function popupCancel() {
    $('.admin-popup-wrap').fadeOut();
    $('.inputBox input').val('');
    $('.argumentText').hide();
}
function deleteCatBlock(obj) {
    var thisId = obj.parent().attr('data-id'), data;
    if (obj.parent().attr('id') == 'argument') {
        data = 'id=' + thisId + '&argument=true';
    } else {
        data = 'id=' + thisId;
    }
    deleteCategory.deleteCategorySend(data, obj.parent().parent());
}
function addArgumentFunc(obj) {
    parentId = obj.parent().attr('data-id');
    catNum = obj.parent().attr('data-value');
    $('.argumentText textarea').val('').text('');
    $('.argumentText').show();
    $('.add-Arguments_category h6').text('Добавление довода');
    $('.saveCat').text('Добавить тип');
    $('.add-Arguments_category').fadeIn().css('display', 'flex');
    $('.saveCat').removeClass('subChild').addClass('createArgumentStart');
}
function editCatArg(obj) {
    parentId = obj.attr('data-parent_id');
    if (obj.attr('id') == 'argument') {
        $('.argumentText').show();
        catArgObj = {
            descr: obj.attr('id'),
            name: obj.find('h3').text(),
            text: obj.find('p').text(),
            id: parseInt(obj.attr('data-id'))
        };
        $('.inputBox input').val(catArgObj.name);
        $('.argumentText textarea').text(catArgObj.text).val(catArgObj.text);
        $('.add-Arguments_category h6').text('Редактирование довода');
    } else {
        $('.argumentText').hide();
        catArgObj = {
            descr: obj.attr('id'),
            name: obj.find('h3, h2').text(),
            id: parseInt(obj.attr('data-id'))
        };
        $('.inputBox input').val(catArgObj.name);
        $('.add-Arguments_category h6').text('Редактирование категории');
    }
    $('.add-Arguments_category').fadeIn().css('display', 'flex');
    $('.saveCat').removeClass('subChild createArgument').addClass('editArgCat');
}
function ShellToFill(step, titleText, id, parent_id, dataRequired, text) {
    this.wrapp = '<li class="catArguments">';
    this.holder = '<ul class="subWrap_' + step + '">';
    this.box = '<div class="category" id="category" data-value="" data-id="' + id + '" data-parent_id="' + parent_id + '" data-required="' + dataRequired + '" data-toggle="true">';
    this.box2 = '<li class="category" id="category" data-value="' + step + '" data-id="' + id + '" data-parent_id="' + parent_id + '" data-required="' + dataRequired + '" data-toggle="true">';
    this.box3 = '<li class="category" id="argument" data-value="' + step + '" data-id="' + id + '" data-parent_id="' + parent_id + '" data-required="' + dataRequired + '" data-toggle="true">';
    this.arrow = '<div class="category_arrow"></div>';
    this.title = '<h2>' + titleText + '</h2>';
    this.title2 = '<h3>' + titleText + '</h3>';
    this.catAdd = '<div class="category_add">Добавить категорию</div>';
    this.catAdd2 = '<div class="category_add withoutText"></div>';
    this.argAdd = '<div class="category_argumentAdd"></div>';
    this.argAddCross = '<div class="category_argumentAdd crossView">Довод</div>';
    this.argText = '<p>' + text + '</p>';
    this.catEdit = '<div class="category_edit"></div>';
    this.catDel = '<div class="category_delete"></div>';
}

function showArgComment(obj) {
    if ($('#addArgComments').prop('checked') == false) {
        obj.addClass('toggleArgComment');
        $('.argumentComment').slideDown(400);
    } else {
        obj.removeClass('toggleArgComment');
        $('.argumentComment').slideUp(400);
        $('.argumentComment textarea').val('');
    }
}

var createNewCategory = {
    newCategorySend: function(data) {
            $.ajax({
            type: "GET",
            url: "http://fas/admin/arguments/ajaxAddCategory",
            data: data,
            dataType: 'json',
            success: function(value) {
                shell = new ShellToFill(catNum, value.name, value.id, value.parent_id, value.required);
                popupCancel();
                switch(catNum) {
                    case 1:
                        createNewCategory.createSubCategory_1();
                        break;
                    case 2:
                        createNewCategory.createSubCategory_2();
                        break;
                    case 3:
                        createNewCategory.createSubCategory_3();
                        break;
                    default:
                        createNewCategory.createCategory();
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
            type: "GET",
            url: "http://fas/admin/arguments/ajaxAddArguments",
            data: data,
            dataType: 'json',
            success: function(value) {
                var argNum = catNum;
                argNum++;
                shell = new ShellToFill(argNum, value.name, value.id, value.category_id, value.required, value.text);
                popupCancel();
                $('.subWrap_' + catNum + ' .category').each(function() {
                    if ($(this).attr('data-id') == parentId) {
                        $(this).parent().append(writeGetData.subCategory_3());
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

var receivingData = {
    getSomeData: function(data, obj, num) {
        $.ajax({
            type: "GET",
            url: "http://fas/admin/arguments/ajaxGetCatArguments",
            data: data,
            dataType: 'json',
            success: function(value) {
                console.log(value);
                num++;
                switch (num) {
                    case 1:
                        cycleDataCat(num, writeGetData.subCategory_1);
                        break;
                    case 2:
                        cycleDataCat(num, writeGetData.subCategory_2);
                        if (value.arguments.length != 0) {
                            cycleDataArg(num, writeGetData.subCategory_3);
                        }
                        break;
                    case 3:
                        cycleDataArg(num, writeGetData.subCategory_3);
                        break;
                }
                if (obj.next().length == 0) {
                    alert('неты данных!');
                } else {

                }
                function cycleDataCat(numb, func) {
                    for (var i = 0; i < value.cat_arguments.length; i++) {
                        shell = new ShellToFill(numb, value.cat_arguments[i].name, value.cat_arguments[i].id, value.cat_arguments[i].parent_id, value.cat_arguments[i].required);
                        obj.parent().append(func());
                    }
                }
                function cycleDataArg(numb, func) {
                    for (var i = 0; i < value.arguments.length; i++) {
                        shell = new ShellToFill(numb, value.arguments[i].name, value.arguments[i].id, value.arguments[i].category_id, value.arguments[i].required, value.arguments[i].text);
                        obj.parent().append(func());
                    }
                }
            },
            error: function(xhr) {
                alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                    + xhr.statusText + ' ResponseText:' + xhr.responseText);
            }
        });
    }
};

var editCategoryArgument = {
    editCatArg: function(data, name) {
        $.ajax({
            type: "GET",
            url: "http://fas/admin/arguments/ajaxEdit",
            data: data,
            dataType: 'json',
            success: function(value) {
                if (name == 'argument') {
                    editCategoryArgument.renameArg(value);
                } else {
                    editCategoryArgument.renameCat(value);
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
                $(this).find('p').text(val.text);
            }
        });
    },
    renameCat: function(val) {
        $('.argCatTree #category').each(function() {
            var thisId = $(this).attr('data-id');
            if (thisId == val.id) {
                $(this).find('h2, h3').text(val.name);
            }
        });
    }
};

var deleteCategory = {
    deleteCategorySend: function(data, obj) {
        $.ajax({
            type: "GET",
            url: "http://fas/admin/arguments/ajaxRemove",
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
        return '<li>' +
            shell.holder +
            shell.box2 +
            shell.arrow +
            shell.title2 +
            shell.catAdd2 +
            shell.argAdd +
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
            shell.catEdit +
            shell.catDel +
            '</ul></li>'
    }
};