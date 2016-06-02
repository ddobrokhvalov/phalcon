$(document).ready(function () {
    $("span.sortable").click(function(){
        if ($(this).hasClass("revert-bg")) {
            $(this).removeClass("revert-bg");
        } else {
            $(this).addClass("revert-bg");
        }
        var sort_order = ["asc", "desc"];
        var current_sort = 0;
        var parentObj = $(this).parent();
        var sort_by = parentObj.attr("sort-field");
        if (parentObj.attr("sort-order") == undefined) {
            parentObj.attr("sort-order", current_sort);
        } else {
            current_sort = !(+parentObj.attr("sort-order"));
            current_sort = current_sort ? 1 : 0;
            parentObj.attr("sort-order", current_sort);
        }
        tinysort('#sort-table ul.lt-content-main', {attr: sort_by, order: sort_order[current_sort]});
    });
    $("span.sortable-2").click(function(){
        if ($(this).hasClass("revert-bg")) {
            $(this).removeClass("revert-bg");
        } else {
            $(this).addClass("revert-bg");
        }
        var sort_order = ["asc", "desc"];
        var current_sort = 0;
        var parentObj = $(this).parent();
        var sort_by = parentObj.attr("sort-field");
        if (parentObj.attr("sort-order") == undefined) {
            parentObj.attr("sort-order", current_sort);
        } else {
            current_sort = !(+parentObj.attr("sort-order"));
            current_sort = current_sort ? 1 : 0;
            parentObj.attr("sort-order", current_sort);
        }
        tinysort('#sort-table-2 ul.lt-content-main', {attr: sort_by, order: sort_order[current_sort]});
    });
});