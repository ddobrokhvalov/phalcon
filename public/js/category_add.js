$(document).ready(function() {
    $('.add-cat').click(function() {
        $('.add-Arguments_category').fadeIn().css('display', 'flex');
    });
    $('.cancel').click(function() {
        popupCancel();
    });
    $('.saveCat').click(function() {
        categorySend();
    });
});

function categorySend() {
    var catName = $('.inputBox input').val(),
        data = '?id=' + 0 + '&name=' + catName;
    createNewCategory.newCategorySend(data);
}

function popupCancel() {
    $('.admin-popup-wrap').fadeOut();
    $('.inputBox input').val('');
}

var shell = new ShellToFill();
function ShellToFill(step, titleText, id, parent_id) {
    this.wrapp = '<li class="catArguments">';
    this.holder = '<ul class="subWrap_' + step + '">';
    this.box = '<div class="category" data-id="' + id + '" data-parent_id="' + parent_id + '">';
    this.arrow = '<div class="category_arrow"></div>';
    this.title1 = '<h2>' + titleText + '</h2>';
    this.title2 = '<h3>' + titleText + '</h3>';
    this.catAdd = '<div class="category_add">Добавить категорию</div>';
    this.catAddnt = '<div class="category_add withoutText"></div>';
    this.catAddCross = '<div class="category_add crossView">Подкатегорию</div>';
    this.argAdd = '<div class="category_argumentAdd"></div>';
    this.argAddCross = '<div class="category_argumentAdd crossView">Довод</div>';
    this.catEdit = '<div class="category_edit"></div>';
    this.catDel = '<div class="category_delete"></div>';
};

var createNewCategory = {
    newCategorySend: function(data) {
        $.ajax({
            type: "GET",
            url: "http://fas/admin/arguments/ajaxAddCategory",
            data: data,
            dataType: 'json',
            success: function(value) {
                console.log(value);
                ShellToFill(1, value.name, value.id, value.parent_id);
                createNewCategory.createParentCategory();
            },
            error: function(xhr) {
                alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                    + xhr.statusText + ' ResponseText:' + xhr.responseText);
            }
        });
    },
    createParentCategory: function() {
        $('.admin-popup-wrap').fadeOut();
        $('.argCatTree').append(
            shell.wrapp +
            '<ul>' +
            shell.box +
            shell.arrow +
            shell.title1 +
            shell.catAdd +
            shell.catEdit +
            shell.catDel +
            '</ul>' +
            '</li>'
        );
    }
};


/* /ajaxRemove?edit[id]=6&edit[name]=jhbnji&edit[arg]=true&edit[text]=mkonkomko */