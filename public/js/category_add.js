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

var createNewCategory = {
    step: 1,
    title: '',
    text: '',
    newCategorySend: function(data) {
        $.ajax({
            type: "GET",
            url: "http://fas/admin/arguments/ajaxAddCategory",
            data: data,
            dataType: 'json',
            success: function(value) {
                console.log(value);
                // createNewCategory.createParentCategory();
            },
            error: function(xhr) {
                alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                    + xhr.statusText + ' ResponseText:' + xhr.responseText);
            }
        });
    },
    // createParentCategory: function() {
    //
    // }
};

var shell = new ShellToFill(
    createNewCategory.step,
    createNewCategory.title,
    createNewCategory.text
);
function ShellToFill(step, titleText, text) {
    this.wrapp = '<li class="catArguments">';
    this.holder = '<ul class="subWrap_' + step + '">';
    this.box = '<div class="category">';
    this.arrow = '<div class="category_arrow"></div>';
    this.title1 = '<h2>' + titleText + '</h2>';
    this.title2 = '<h3>' + titleText + '</h3>';
    this.catAdd = '<div class="category_add">' + text + '</div>';
    this.catAddnt = '<div class="category_add withoutText"></div>';
    this.catAddCross = '<div class="category_add crossView">' + text + '</div>';
    this.argAdd = '<div class="category_argumentAdd"></div>';
    this.argAddCross = '<div class="category_argumentAdd crossView">' + text + '</div>';
    this.catEdit = '<div class="category_edit"></div>';
    this.catDel = '<div class="category_delete"></div>';
};


/* /ajaxRemove?edit[id]=6&edit[name]=jhbnji&edit[arg]=true&edit[text]=mkonkomko */