var arrCheck = new Array();
jQuery(document).ready(function($) {

    if (bowser.firefox || bowser.chrome || bowser.name=='Yandex Browser') {
        if(bowser.firefox && bowser.version < 51)
            alert("Для корректной работы системы обновите браузер 'Mozilla Firefox' до последней версии. <a href='https://download.mozilla.org/?product=firefox-stub&os=win&lang=ru'>Скачать</a>");
        else if(bowser.chrome && bowser.version < 56)
            alert("Для корректной работы системы обновите браузер 'Chrome' до последней версии. <a href='https://www.google.ru/chrome/browser/desktop/index.html#'>Скачать</a>");
        else if(bowser.name=='Yandex Browser' && bowser.version < 17)
            alert("Для корректной работы системы обновите браузер 'Yandex Browser' до последней версии. <a href='https://browser.yandex.ru/?from=wizard___one_|&banerid=0500000134'>Скачать</a>");
        
        if(!checkIfPluginEnabled())
        {
            alert('Для работы с электронной подписью необходимо установить плагин "Cades browser plug-in". http://www.cryptopro.ru/products/cades/plugin/get_2_0');
        }
    }
    else
    {
        alert('Для работы с системой необходимо использовать один из перечисленных браузеров:\r\n\
        - Mozilla FireFox\r\n\
        - Chrome\r\n\
        - Yandex браузер\r\n'
        );
    }
});

var checkIfPluginEnabled = function() { 
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