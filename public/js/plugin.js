var arrCheck = new Array();
jQuery(document).ready(function ($) {

    if (bowser.firefox || bowser.chrome || bowser.name == 'Yandex Browser') {
        if (bowser.firefox && bowser.version < 51)
            showStyledPopupMessageWithButtons(
                "#pop-browser-alert",
                "Обратите внимание",
                "Для корректной работы системы обновите браузер 'Mozilla Firefox' до последней версии. <a href='https://download.mozilla.org/?product=firefox-stub&os=win&lang=ru'>Скачать</a>",
                "alert('ok')"
            );
        else if (bowser.chrome && bowser.version < 56)
            showStyledPopupMessageWithButtons(
                "#pop-browser-alert",
                "Обратите внимание",
                "Для корректной работы системы обновите браузер 'Chrome' до последней версии. <a href='https://www.google.ru/chrome/browser/desktop/index.html#'>Скачать</a>",
                "alert('ok')"
            );
        else if (bowser.name == 'Yandex Browser' && bowser.version < 17)
            showStyledPopupMessageWithButtons(
                "#pop-browser-alert",
                "Обратите внимание",
                "Для корректной работы системы обновите браузер 'Yandex Browser' до последней версии. <a href='https://browser.yandex.ru/?from=wizard___one_|&banerid=0500000134'>Скачать</a><br/>",
                "alert('ok')"
            );

        if (!checkIfPluginEnabled()) {
            if ((bowser.chrome || bowser.name == 'Yandex Browser')) {
                var canPromise = !!window.Promise;
                if (canPromise) {
                    cadesplugin.then(function () {
                            //alert("cadesplugin_loaded");
                        },
                        function (error) {
                            showStyledPopupMessageWithButtons(
                                "#pop-browser-alert",
                                "Обратите внимание",
                                'Для корректной работы с электронной подписью необходимо установить плагин "Cades browser plug-in" (<a href="http://www.cryptopro.ru/products/cades/plugin/get_2_0">Скачать</a>) и убедиться, что данный плагин находится в статусе "Включен".',
                                "alert('ok')"
                            );
                        }
                    );
                } else {
                    window.addEventListener("message", function (event) {
                            if (event.data == "cadesplugin_loaded") {
                                alert("cadesplugin_loaded1");
                            } else if (event.data == "cadesplugin_load_error") {
                                alert("cadesplugin_load_error1");
                            }
                        },
                        false);
                    window.postMessage("cadesplugin_echo_request", "*");
                }

            }
            else
                showStyledPopupMessageWithButtons(
                    "#pop-browser-alert",
                    "Обратите внимание",
                    'Для корректной работы с электронной подписью необходимо установить плагин "Cades browser plug-in" (<a href="http://www.cryptopro.ru/products/cades/plugin/get_2_0">Скачать</a>) и убедиться, что данный плагин находится в статусе "Включен".',
                    "alert('ok')"
                );
        }
    }
    else {
        showStyledPopupMessageWithButtons(
            "#pop-browser-alert",
            "Обратите внимание",
            '<form>Для работы с системой необходимо использовать один из перечисленных браузеров:<br/>\
        - Mozilla FireFox<br/>\
        - Chrome<br/>\
        - Yandex браузер<br/></form>',
            "alert('ok')"
        );
    }
});


var checkIfPluginEnabled = function () {
    var isCryptoEnabled = false;
    // Проверка для всех браузеров, кроме IE
    if (typeof(navigator.plugins) != "undefined" && typeof(navigator.plugins["CryptoPro CAdES NPAPI Browser Plug-in"]) == "object") isCryptoEnabled = true;
    else if (typeof  window.ActiveXObject != "undefined") {
        // Проверка для IE
        try {
            if (new ActiveXObject("CryptoPro CAdES NPAPI Browser Plug-in")) isCryptoEnabled = true;
        } catch (e) {
        }
        ;
    }
    ;
    return isCryptoEnabled;
};