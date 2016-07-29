$(document).ready(function() {
    $('.add-cat').click(function() {
        if ($('.saveCat').hasClass('subChild')) {
            $('.saveCat').removeClass('subChild');
            catNum = '';
        }
        $('.add-Arguments_category').fadeIn().css('display', 'flex');
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
});

var catNum = '',
    parentId,
    shell;
function categorySend() {
    var catName = $('.inputBox input').val(),
        data;
    if ($('.saveCat').hasClass('subChild')) {
        catNum++;
        data = 'id=' + parentId + '&name=' + catName;
    } else {
        data = 'id=' + 0 + '&name=' + catName;
    }
    createNewCategory.newCategorySend(data);
}
function createSubCat_Arg(obj) {
    $('.saveCat').addClass('subChild');
    catNum = obj.parent().attr('data-value');
    parentId = obj.parent().attr('data-id');
    $('.add-Arguments_category').fadeIn().css('display', 'flex');
}
function popupCancel() {
    $('.admin-popup-wrap').fadeOut();
    $('.inputBox input').val('');
}
function deleteCatBlock(obj) {
    var thisId = obj.parent().attr('data-id'),
        data = 'id=' + thisId;
    deleteCategory.deleteCategorySend(data, obj.parent().parent());
}
function ShellToFill(step, titleText, id, parent_id) {
    this.wrapp = '<li class="catArguments">';
    this.holder = '<ul class="subWrap_' + step + '">';
    this.box = '<div class="category" data-value="" data-id="' + id + '" data-parent_id="' + parent_id + '">';
    this.box2 = '<li class="category" data-value="' + step + '" data-id="' + id + '" data-parent_id="' + parent_id + '">';
    this.arrow = '<div class="category_arrow"></div>';
    this.title = '<h2>' + titleText + '</h2>';
    this.title2 = '<h3>' + titleText + '</h3>';
    this.catAdd = '<div class="category_add">Добавить категорию</div>';
    this.catAdd2 = '<div class="category_add withoutText"></div>';
    this.catAddCross = '<div class="category_add crossView">Подкатегорию</div>';
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
                console.log(value);
                shell = new ShellToFill(catNum, value.name, value.id, value.parent_id);
                popupCancel();
                switch(catNum) {
                    case 1:
                        createNewCategory.createSubCategory_1();
                        break;
                    case 2:
                        break;
                    case 3:
                        break;
                    case 4:
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
        $('.argCatTree').append(
            shell.wrapp +
            shell.box +
            shell.arrow +
            shell.title +
            shell.catAdd +
            shell.catEdit +
            shell.catDel +
            '</div></li>'
        );
    },
    createSubCategory_1: function() {
        $('.argCatTree').append(
            shell.wrapp +
            shell.box +
            shell.arrow +
            shell.title +
            shell.catAdd +
            '</div></li>'
        );
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
}


/* /ajaxRemove?edit[id]=6&edit[name]=jhbnji&edit[arg]=true&edit[text]=mkonkomko */