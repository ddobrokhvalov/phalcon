var arrCheck = new Array();
jQuery(document).ready(function($) {

    if (bowser.firefox || bowser.chrome || bowser.name=='Yandex Browser') {
        if(bowser.firefox && bowser.version < 51)
            showStyledPopupMessageWithButtons(
                "#pop-browser-alert",
                "Подтвердите действие",
                "Для корректной работы системы обновите браузер 'Mozilla Firefox' до последней версии. <a href='https://download.mozilla.org/?product=firefox-stub&os=win&lang=ru'>Скачать</a>",
                "alert('ok')"
            );
        else if(bowser.chrome && bowser.version < 56)
            showStyledPopupMessageWithButtons(
                "#pop-browser-alert",
                "Подтвердите действие",
                "Для корректной работы системы обновите браузер 'Chrome' до последней версии. <a href='https://www.google.ru/chrome/browser/desktop/index.html#'>Скачать</a>",
                "alert('ok')"
            );
        else if(bowser.name=='Yandex Browser' && bowser.version < 17)
            showStyledPopupMessageWithButtons(
                "#pop-browser-alert",
                "Подтвердите действие",
                "Для корректной работы системы обновите браузер 'Yandex Browser' до последней версии. <a href='https://browser.yandex.ru/?from=wizard___one_|&banerid=0500000134'>Скачать</a><br/>",
                "alert('ok')"
            );
        
        if(!checkIfPluginEnabled())
        {
            showStyledPopupMessageWithButtons(
                "#pop-browser-alert",
                "Подтвердите действие",
                'Для работы с электронной подписью необходимо установить плагин "Cades browser plug-in". <a href="http://www.cryptopro.ru/products/cades/plugin/get_2_0">Скачать</a>',
                "alert('ok')"
            );
        }
    }
    else
    {
        showStyledPopupMessageWithButtons(
            "#pop-browser-alert",
            "Подтвердите действие",
            '<form>Для работы с системой необходимо использовать один из перечисленных браузеров:<br/>\
        - Mozilla FireFox<br/>\
        - Chrome<br/>\
        - Yandex браузер<br/></form>',
            "alert('ok')"
        );
    }
});

var checkIfPluginEnabled = function() { 
 /* if(typeof window.cades != "undefined")
      return true;*/
  
  var isCryptoEnabled = false; 
  // Проверка для всех браузеров, кроме IE 
  if (typeof(navigator.plugins)!="undefined" && typeof(navigator.plugins["CryptoPro CAdES NPAPI Browser Plug-in"])=="object") isCryptoEnabled = true; 
  else if (typeof  window.ActiveXObject !="undefined") { 
    // Проверка для IE 
    try { 
      if (new ActiveXObject("CryptoPro CAdES NPAPI Browser Plug-in")) isCryptoEnabled = true; 
    } catch(e) {}; 
  }; 
  return isCryptoEnabled; 
};