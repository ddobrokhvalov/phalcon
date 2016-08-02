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
        var data = 'id=' + $(this).parent().attr('data-id');
        receivingData.getSomeData(data, $(this).parent(), $(this).parent().attr('data-value'));
    });
    $('.argCatTree').on('click', '.category_argumentAdd', function() {
        addArgumentFunc($(this));
    });
    $('.argCatTree').on('click', '.category_edit', function() {
        editCatArg($(this).parent());
    });
});

var catNum = '', parentId, argName, argText, shell;
function categorySend() {
    var catName = $('.inputBox input').val(),
        data;
    if ($('.saveCat').hasClass('subChild')) {
        catNum++;
        data = 'parent_id=' + parentId + '&name=' + catName;
        createNewCategory.newCategorySend(data);
    } else if ($('.saveCat').hasClass('createArgument')) {
        var argumentName = $('.inputBox input').val(),
            argumentText = $('.inputBox textarea').val();
        data = 'arguments[category_id]=' + parentId + '&arguments[name]=' + argumentName + '&arguments[text]=' + argumentText;
        addArgument.addData(data);
    } else {
        data = 'parent_id=' + 0 + '&name=' + catName;
        createNewCategory.newCategorySend(data);
    }
}
function addNewCat() {
    if ($('.saveCat').hasClass('subChild')) {
        $('.saveCat').removeClass('subChild');
    }
    catNum = '';
    $('.argumentText').hide();
    $('.add-Arguments_category').fadeIn().css('display', 'flex');
}
function createSubCat_Arg(obj) {
    $('.argumentText').hide();
    $('.saveCat').addClass('subChild');
    catNum = obj.parent().attr('data-value');
    parentId = obj.parent().attr('data-id');
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
    $('.add-Arguments_category').fadeIn().css('display', 'flex');
    $('.saveCat').removeClass('subChild').addClass('createArgument');
}
function editCatArg(obj) {
    parentId = obj.attr('data-parent_id');
    $('.argumentText textarea').val('').text('');
    if (obj.attr('id') == 'argument') {
        $('.argumentText').show();
        argName = obj.find('h3').text();
    } else {
        $('.argumentText').hide();
    }
    $('.add-Arguments_category').fadeIn().css('display', 'flex');
    $('.saveCat').removeClass('subChild createArgument').addClass('editArgCat');
}
function ShellToFill(step, titleText, id, parent_id) {
    this.wrapp = '<li class="catArguments">';
    this.holder = '<ul class="subWrap_' + step + '">';
    this.box = '<div class="category" data-value="" data-id="' + id + '" data-parent_id="' + parent_id + '">';
    this.box2 = '<li class="category" data-value="' + step + '" data-id="' + id + '" data-parent_id="' + parent_id + '">';
    this.box3 = '<li class="category" id="argument" data-value="' + step + '" data-id="' + id + '" data-parent_id="' + parent_id + '">';
    this.arrow = '<div class="category_arrow"></div>';
    this.title = '<h2>' + titleText + '</h2>';
    this.title2 = '<h3>' + titleText + '</h3>';
    this.catAdd = '<div class="category_add">Добавить категорию</div>';
    this.catAdd2 = '<div class="category_add withoutText"></div>';
    this.argAdd = '<div class="category_argumentAdd"></div>';
    this.argAddCross = '<div class="category_argumentAdd crossView">Довод</div>';
    this.catEdit = '<div class="category_edit"></div>';
    this.catDel = '<div class="category_delete"></div>';
}

var createNewCategory = {
    newCategorySend: function(data) {
            $.ajax({
            type: "GET",
            url: "http://fas/admin/arguments/ajaxAddCategory",
            data: data,
            dataType: 'json',
            success: function(value) {
                shell = new ShellToFill(catNum, value.name, value.id, value.parent_id);
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
                    case 4:
                        createNewCategory.createSubCategory_4();
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
    createSubCategory_4: function() {
        createNewCategory.boxCreateSub(writeGetData.subCategory_4(), catNum);
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
                shell = new ShellToFill(argNum, value.name, value.id, value.category_id);
                popupCancel();
                $('.subWrap_' + catNum + ' .category').each(function() {
                    if ($(this).attr('data-id') == parentId) {
                        $(this).parent().append(writeGetData.subCategory_4());
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
                console.log(obj.next().length);
                // if (obj.next().length != 0) {
                    if (num == 0) {
                        cycleData(1, writeGetData.subCategory_1);
                    } else if (num == 1) {
                        cycleData(2, writeGetData.subCategory_2);
                    } else if (num == 2) {
                        cycleData(3, writeGetData.subCategory_3);
                    } else if (num == 3) {
                        cycleData(4, writeGetData.subCategory_4);
                    }
                // }
                function cycleData(numb, func) {
                    for (var i = 0; i < value.cat_arguments.length; i++) {
                        shell = new ShellToFill(numb, value.cat_arguments[i].name, value.cat_arguments[i].id, value.cat_arguments[i].parent_id);
                        obj.parent().append(func());
                    }
                    for (var i = 0; i < value.arguments.length; i++) {
                        shell = new ShellToFill(numb, value.arguments[i].name, value.arguments[i].id, value.arguments[i].category_id);
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
    edit: function(data, obj) {
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
            shell.catAdd +
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
            shell.catAdd2 +
            shell.argAdd +
            shell.catEdit +
            shell.catDel +
            '</ul></li>'
    },
    subCategory_3: function() {
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
    subCategory_4: function() {
        return '<li>' +
            shell.holder +
            shell.box3 +
            shell.title2 +
            shell.catEdit +
            shell.catDel +
            '</ul></li>'
    }
};