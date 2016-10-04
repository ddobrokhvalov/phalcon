
function replaceWordTags(text, ckeditor_id) {
    while(text.search("<br>") >= 0 || text.search("<p>") >= 0 || text.search("</p>") >= 0 || text.search("&nbsp;") >= 0){
        text = text.replace("<br>", '\r\n');
        text = text.replace("<p>", '');
        text = text.replace("</p>", '\r\n');
        text = text.replace("&nbsp;", ' ');
    }
    text = remove_marker(text);
    text = replace_hard_tags(text);
    text = replace_easy_tags(text);
    text = add_simple_tags_text(text);
    var dd = text.match(/<w:r>[\s\S]*?<\/w:r>/g);
    if (dd != null) {
        for (var _c = 0; _c < dd.length; _c++) {
            if (dd[_c].search('<w:r><w:rPr><w:b/></w:rPr>') >= 0) {
                var replaced_font = add_font_support(dd[_c], 'strong', true);
                text = text.replace(dd[_c], replaced_font);
            } else if (dd[_c].search('<w:r><w:rPr><w:i/></w:rPr>') >= 0) {
                var replaced_font = add_font_support(dd[_c], 'italian', true);
                text = text.replace(dd[_c], replaced_font);
            } else if (dd[_c].search('<w:r><w:rPr><w:u w:val="single"/></w:rPr>') >= 0) {
                var replaced_font = add_font_support(dd[_c], 'underline', true);
                text = text.replace(dd[_c], replaced_font);
            } else if (dd[_c].search('<w:r><w:rPr><w:i/><w:u w:val="single"/></w:rPr>') >= 0) {
                var replaced_font = add_font_support(dd[_c], 'underline_italian', true);
                text = text.replace(dd[_c], replaced_font);
            } else if (dd[_c].search('<w:rPr><w:b/><w:u w:val="single"/></w:rPr>') >= 0) {
                var replaced_font = add_font_support(dd[_c], 'underline_bold', true);
                text = text.replace(dd[_c], replaced_font);
            } else if (dd[_c].search('<w:r><w:rPr><w:b/><w:i/></w:rPr>') >= 0) {
                var replaced_font = add_font_support(dd[_c], 'italian_bold', true);
                text = text.replace(dd[_c], replaced_font);
            } else if (dd[_c].search('<w:r><w:rPr><w:b/><w:i/><w:u w:val="single"/></w:rPr>') >= 0) {
                var replaced_font = add_font_support(dd[_c], 'italian_bold_underline', true);
                text = text.replace(dd[_c], replaced_font);
            }
        }
    }
    //text = add_simple_tags_text(text);
    text = add_list(text, ckeditor_id);
    text = add_font_support(text, '', false);
    text = new_line_fix(text);
    var del_text = text.split('\r\n');
    
    var text_copy = '';
    for (var d_t = 0; d_t < del_text.length; d_t++) {
        if (del_text[d_t] != "" && del_text[d_t].search("<w:p") != 0) {
            var start_sign_o = del_text[d_t].search("<");
            var start_text_o = del_text[d_t].substr(0, start_sign_o);
            var curr_text = '';
            if (start_text_o.length > 0) {
                var text_to_end_o = del_text[d_t].substr(start_sign_o, del_text[d_t].length);
                curr_text = '<w:r><w:t xml:space="preserve">' + start_text_o + '</w:t></w:r>' + text_to_end_o;
                var position_o = (get_last_closing_sign_position(curr_text)) + 1;
                start_text_o = curr_text.substr(0, position_o);
                text_to_end_o = curr_text.substr(position_o, curr_text.length);
                curr_text = start_text_o + '<w:r><w:t xml:space="preserve">' + text_to_end_o + '</w:t></w:r>';
            }
            if (curr_text.length == 0) {
                curr_text = '<w:r><w:t xml:space="preserve">' + del_text[d_t] + '</w:t></w:r>';
            }
            text_copy += '<w:p>' + curr_text + '</w:p>';
        } else if (del_text[d_t] != "" && del_text[d_t].substr(del_text[d_t].length - 6, del_text[d_t].length) != '</w:p>') {
            var position_o = (get_last_closing_sign_position(del_text[d_t])) + 1;
            start_text_o = del_text[d_t].substr(0, position_o);
            var text_to_end_o = del_text[d_t].substr(position_o, del_text[d_t].length);
            text_copy += start_text_o + '<w:p><w:r><w:t xml:space="preserve">' + text_to_end_o + '</w:p></w:t></w:r>';
        } else {
            text_copy += del_text[d_t];
        }
    }
    text = text_copy;
    var start_sign = text.search("<");
    if (start_sign >= 0) {
        var start_text = text.substr(0, start_sign);
        if (start_text.length > 0) {
            var text_to_end = text.substr(start_sign, text.length);
            text = '<w:p><w:r><w:t xml:space="preserve">' + start_text + '</w:t></w:r></w:p>' + text_to_end;
            var position = (get_last_closing_sign_position(text)) + 1;
            start_text = text.substr(0, position);
            text_to_end = text.substr(position, text.length);
            text = start_text + '<w:p><w:r><w:t xml:space="preserve">' + text_to_end + '</w:t></w:r></w:p>';
        }
        
    } else {
        text = '<w:r><w:t xml:space="preserve">' + text + '</w:t></w:r>';
    }
    if (text.search("<w:p") != 0) {
        text = "<w:p>" + text + "</w:p>";
    }
    text = quick_fix(text);
    return text;
}

function replace_easy_tags(text) {
    /* 3 style selected */
    while (text.search("<strong><em><u>") >= 0) {
        text = text.replace("<strong><em><u>", '<w:r><w:rPr><w:b/><w:i/><w:u w:val="single"/></w:rPr><w:t xml:space="preserve">');
        text = text.replace("</u></em></strong>", '</w:t></w:r>');
    }
    while (text.search("<strong><u><em>") >= 0) {
        text = text.replace("<strong><u><em>", '<w:r><w:rPr><w:b/><w:i/><w:u w:val="single"/></w:rPr><w:t xml:space="preserve">');
        text = text.replace("</em></u></strong>", '</w:t></w:r>');
    }
    while (text.search("<u><strong><em>") >= 0) {
        text = text.replace("<u><strong><em>", '<w:r><w:rPr><w:b/><w:i/><w:u w:val="single"/></w:rPr><w:t xml:space="preserve">');
        text = text.replace("</em></strong></u>", '</w:t></w:r>');
    }
    while (text.search("<u><em><strong>") >= 0) {
        text = text.replace("<u><em><strong>", '<w:r><w:rPr><w:b/><w:i/><w:u w:val="single"/></w:rPr><w:t xml:space="preserve">');
        text = text.replace("</strong></em></u>", '</w:t></w:r>');
    }
    while (text.search("<em><u><strong>") >= 0) {
        text = text.replace("<em><u><strong>", '<w:r><w:rPr><w:b/><w:i/><w:u w:val="single"/></w:rPr><w:t xml:space="preserve">');
        text = text.replace("</strong></u></em>", '</w:t></w:r>');
    }
    while (text.search("<em><strong><u>") >= 0) {
        text = text.replace("<em><strong><u>", '<w:r><w:rPr><w:b/><w:i/><w:u w:val="single"/></w:rPr><w:t xml:space="preserve">');
        text = text.replace("</u></strong></em>", '</w:t></w:r>');
    }

    /* 2 style selected */
    while (text.search("<strong><em>") >= 0) {
        text = text.replace("<strong><em>", '<w:r><w:rPr><w:b/><w:i/></w:rPr><w:t xml:space="preserve">');
        text = text.replace("</em></strong>", '</w:t></w:r>');
    }
    while (text.search("<em><strong>") >= 0) {
        text = text.replace("<em><strong>", '<w:r><w:rPr><w:b/><w:i/></w:rPr><w:t xml:space="preserve">');
        text = text.replace("</strong></em>", '</w:t></w:r>');
    }
    while (text.search("<u><strong>") >= 0) {
        text = text.replace("<u><strong>", '<w:r><w:rPr><w:b/><w:u w:val="single"/></w:rPr><w:t xml:space="preserve">');
        text = text.replace("</strong></u>", '</w:t></w:r>');
    }
    while (text.search("<strong><u>") >= 0) {
        text = text.replace("<strong><u>", '<w:r><w:rPr><w:b/><w:u w:val="single"/></w:rPr><w:t xml:space="preserve">');
        text = text.replace("</u></strong>", '</w:t></w:r>');
    }
    while (text.search("<em><u>") >= 0) {
        text = text.replace("<em><u>", '<w:r><w:rPr><w:i/><w:u w:val="single"/></w:rPr><w:t xml:space="preserve">');
        text = text.replace("</u></em>", '</w:t></w:r>');
    }
    while (text.search("<u><em>") >= 0) {
        text = text.replace("<u><em>", '<w:r><w:rPr><w:i/><w:u w:val="single"/></w:rPr><w:t xml:space="preserve">');
        text = text.replace("</em></u>", '</w:t></w:r>');
    }
    
    /* One style selected */
    while (text.search("<u>") >= 0) {
        text = text.replace("<u>", '<w:r><w:rPr><w:u w:val="single"/></w:rPr><w:t xml:space="preserve">');
        text = text.replace("</u>", '</w:t></w:r>');
    }
    while (text.search("<em>") >= 0) {
        text = text.replace("<em>", '<w:r><w:rPr><w:i/></w:rPr><w:t xml:space="preserve">');
        text = text.replace("</em>", '</w:t></w:r>');
    }
    
    while (text.search("<strong>") >= 0) {
        text = text.replace("<strong>", '<w:r><w:rPr><w:b/></w:rPr><w:t xml:space="preserve">');
        text = text.replace("</strong>", '</w:t></w:r>');
    }
    return text;
}

function add_list(text, ckeditor_id) {
    while(true) {
        if (text.search("<ol>") >= 0) {
            $("#" + ckeditor_id + " ol").each(function(ul_index, ul_item) {
                var list = "<ol>" + $(ul_item).html() + "</ol>";
                while (list.search('<br>') >= 0/* || list.search("\r\n") >= 0*/) {
                    list = list.replace("<br>", '\r\n');
                    //list = list.replace("\r\n", '');
                }
                var list_li = [];
                $(ul_item).find("li").each(function(li_index, li_item){
                    list_li.push($(li_item).text());
                });
                var li_i_html = '';
                for (var li_i = 0; li_i < list_li.length; li_i++) {
                    li_i_html += '<w:p><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr></w:pPr><w:r><w:t>' + list_li[li_i] + '</w:t></w:r></w:p>';
                }
                text = text.replace(list, li_i_html + '\r\n');
            });
        } else {
            return text;
        }
    }
    return text;
}

function add_simple_tags_text(text) {
    var no_styles_text = text.match(/<\/w:r>[\s\S]*?<w:r>/g);
    if (no_styles_text == null) {
        return text;
    }
    for (var i = 0; i < no_styles_text.length; i++) {
        if(no_styles_text[i] != "</w:r><w:r>" && no_styles_text[i] != ""){
            var clone_text = no_styles_text[i];
            clone_text = clone_text.substr(6, clone_text.length - 11);
            text = text.replace(no_styles_text[i], '</w:r><w:r><w:t xml:space="preserve">' + clone_text + "</w:t></w:r><w:r>");
        }
        
    }
    /*var no_styles_text = text.match(/\<\/w:r>(.*?)\<w:r>/);
    if (no_styles_text.length > 1) {
        if (no_styles_text[1].length > 0) {
            text = text.replace(no_styles_text[1], '<w:r><w:t xml:space="preserve">' + no_styles_text[1] + '</w:t></w:r>');
        }
    }
    no_styles_text = text.match(/\<\/w:r>(.*?)\<w:r>/);
    if (no_styles_text.length > 1) {
        if (no_styles_text[1].length > 0) {
            text = add_simple_tags_text(text);
        }
    }*/
    return text;
}

function add_font_support(text, f_style, tag_split) {
    var short_open_tag = '', short_close_tag = '';

    var _tag = '';

    if (tag_split) {
        short_open_tag = '</w:t></w:r>';
        switch (f_style) {
            case 'strong':
                short_close_tag = '<w:r><w:rPr><w:b/></w:rPr><w:t xml:space="preserve">';
                _tag = '<w:b/>';
                break;
            case 'italian':
                short_close_tag = '<w:r><w:rPr><w:i/></w:rPr><w:t xml:space="preserve">';
                _tag = '<w:i/>';
                break;
            case 'underline':
                short_close_tag = '<w:r><w:rPr><w:u w:val="single"/></w:rPr><w:t xml:space="preserve">';
                _tag = '<w:u w:val="single"/>';
                break;
            case 'underline_italian':
                short_close_tag = '<w:r><w:rPr><w:i/><w:u w:val="single"/></w:rPr><w:t xml:space="preserve">';
                _tag = '<w:i/><w:u w:val="single"/>';
                break;
            case 'underline_bold':
                short_close_tag = '<w:r><w:rPr><w:b/><w:u w:val="single"/></w:rPr><w:t xml:space="preserve">';
                _tag = '<w:b/><w:u w:val="single"/>';
                break;
            case 'italian_bold':
                short_close_tag = '<w:r><w:rPr><w:b/><w:i/></w:rPr><w:t xml:space="preserve">';
                _tag = '<w:b/><w:i/>';
                break;
            case 'italian_bold_underline':
                short_close_tag = '<w:r><w:rPr><w:b/><w:i/><w:u w:val="single"/></w:rPr><w:t xml:space="preserve">';
                _tag = '<w:b/><w:i/><w:u w:val="single"/>';
                break;
            default:
                short_close_tag = '<w:r><w:t xml:space="preserve">';
                break;
        }
        
    }
    while (true) {
    var font_size_style = text.match(/font-size:[^;]+px/);
        if (font_size_style == null) {
            return text;
        }
        var font_size = get_font_size(font_size_style[0]);
        var h1 = '<span style="' + font_size_style[0] + ';">[\\s\\S]*?</span>';
        var re = new RegExp(h1);
        var text_to_replace = text.match(re);
        var only_text = text_to_replace[0].match(/\>(.*)\</);
        only_text = only_text[1];
        var substring_to_replace = '<span style="' + font_size_style[0] + ';">' + only_text + '</span>';
        text = text.replace(substring_to_replace, short_open_tag +
                                                '<w:r><w:rPr>' +
                                                _tag +
                                                '<w:sz w:val="' + font_size + '"/><w:szCs w:val="' + font_size + '"/></w:rPr><w:t xml:space="preserve">' +
                                                only_text +
                                                '</w:t></w:r>' +
                                                short_close_tag);
    }
    return text;
}

function get_font_size(style) {
    style = style.match(/\d+/);
    return (+style[0]) * 2;
}

function get_last_closing_sign_position(text) {
    var position = 0;
    for (var u = text.length; u >= 0; u--) {
        if (text[u] == ">") {
            position = u;
            break;
        }
    }
    return position;
}

function remove_marker(text){
    return text.replace(/<font class="marker_yellow">/g, '').replace(/<font class="marker_white">/g, '').replace(/<font class="marker_red">/g, '').replace(/<\/font>/g, '');
}

function replace_hard_tags(text) {
    var text_parts = [];
    var text_to_end = '';
    var open_close_tag = {
        '<strong>':'</strong>',
        '<em>':'</em>',
        '<u>':'</u>',
        '<u><em>': '</em></u>',
        '<em><u>': '</u></em>',
        '<strong><u>': '</u></strong>',
        '<u><strong>': '</strong></u>',
        '<em><strong>': '</strong></em>',
        '<strong><em>': '</em></strong>',
        '<em><strong><u>': '</u></strong></em>',
        '<em><u><strong>': '</strong></u></em>',
        '<u><em><strong>': '</strong></em></u>',
        '<u><strong><em>': '</em></strong></u>',
        '<strong><u><em>': '</em></u></strong>',
        '<strong><em><u>': '</u></em></strong>'
    };
    var tags_1_level = ["<strong>", "<em>", "<u>"];
    var tags_2_level = ["<u><em>", "<em><u>", "<strong><u>", "<u><strong>", "<em><strong>", "<strong><em>"];
    var tags_3_level = ["<strong><em><u>", "<strong><u><em>", "<u><strong><em>", "<u><em><strong>", "<em><u><strong>", "<em><strong><u>"];
    var start_sign = text.search('<');
    while (start_sign >= 0) {
        if (text_parts.length > 0) {
            var first_part = second_part.substr(0, start_sign);
            var last_closing_position = get_last_closing_sign_position(second_part);
            var second_part = second_part.substr(start_sign, ((last_closing_position + 1) - start_sign));
            if (text_to_end.length == 0) {
                text_to_end = second_part.substr(last_closing_position + 1, second_part.length);
            }
        } else {
            var first_part = text.substr(0, start_sign);
            var last_closing_position = get_last_closing_sign_position(text);
            var second_part = text.substr(start_sign, ((last_closing_position + 1) - start_sign));
            if (text_to_end.length == 0) {
                text_to_end = text.substr(last_closing_position + 1, text.length);
            }
        }
        if (first_part != '\r\n') {
            text_parts.push(first_part);
        }
        
        for (var s = 0; s < tags_1_level.length; s++) {
            if (second_part.length > 0 && second_part.startsWith(tags_1_level[s])) {
                if (second_part[tags_1_level[s].length] == '<') {
                    break;
                } else {
                    var close_position = second_part.search(open_close_tag[tags_1_level[s]]);
                    var simple_text = second_part.substring(tags_1_level[s].length, close_position).replace(/(<([^>]+)>)/ig, "");
                    // TODO check if inside of this part exists other tags
                    text_parts.push(tags_1_level[s] + simple_text + open_close_tag[tags_1_level[s]]);
                    second_part = second_part.substr(close_position + tags_1_level[s].length + 1, second_part.length);
                }
            }
        }
        for (var s = 0; s < tags_2_level.length; s++) {
            if (second_part.length > 0 && second_part.startsWith(tags_2_level[s])) {
                if (second_part[tags_2_level[s].length] == '<') {
                    break;
                } else {
                    var cut_first = tags_2_level[s].substr(0, (tags_2_level[s].search('>') + 1));
                    var close_position = second_part.search(open_close_tag[cut_first]);
                    var simple_text = second_part.substring(tags_2_level[s].length, close_position).replace(/(<([^>]+)>)/ig, "");
                    // TODO check if inside of this part exists other tags
                    text_parts.push(tags_2_level[s] + simple_text + open_close_tag[tags_2_level[s]]);
                    second_part = second_part.substr(close_position + cut_first.length + 1, second_part.length);
                }
            }
        }
        for (var s = 0; s < tags_3_level.length; s++) {
            if (second_part.length > 0 && second_part.startsWith(tags_3_level[s])) {
                var cut_first = tags_3_level[s].substr(0, (tags_3_level[s].search('>') + 1));
                var close_position = second_part.search(open_close_tag[cut_first]);
                var simple_text = second_part.substring(tags_3_level[s].length, close_position).replace(/(<([^>]+)>)/ig, "");
                text_parts.push(tags_3_level[s] + simple_text + open_close_tag[tags_3_level[s]]);
                second_part = second_part.substr(close_position + cut_first.length + 1, second_part.length);
            }
        }
        if (second_part.startsWith('<ol>')) {
            var list_html = second_part.substr(0, second_part.search('</ol>') + '</ol>'.length);
            text_parts.push(list_html);
            second_part = second_part.replace(list_html, '');
        }
        if (second_part.startsWith('<span')) {
            var list_html = second_part.substr(0, second_part.search('</span>') + '</span>'.length);
            text_parts.push(list_html);
            second_part = second_part.replace(list_html, '');
        }
        start_sign = second_part.search('<');
    }
    if (text_parts.length > 0) {
        return text_parts.join('') + text_to_end;
    }
    return text;
}

function quick_fix(text) {
    if (text.search('<w:r><w:t xml:space="preserve"><w:r><w:t xml:space="preserve">') >= 0) {
        text = text.replace(/<w:r><w:t xml:space="preserve"><w:r><w:t xml:space="preserve">/g, '<w:r><w:t xml:space="preserve">');
    }
    if (text.search('</w:t></w:r></w:t></w:r>') >= 0) {
        text = text.replace(/<\/w:t><\/w:r><\/w:t><\/w:r>/g, '<\/w:t><\/w:r>');
    }
    return text;
}

function new_line_fix(text) {
    if (text.search('\r\n</w:t></w:r>') >= 0) {
        text = text.replace(/\r\n<\/w:t><\/w:r>/g, '</w:t></w:r>\r\n');
    }
    return text;
}