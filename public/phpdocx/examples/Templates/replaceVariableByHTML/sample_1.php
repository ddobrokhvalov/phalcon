<?php

require_once '../../../classes/CreateDocx.inc';

//$docx = new CreateDocxFromTemplate('../../files/TemplateHTML.docx');
$docx = new CreateDocxFromTemplate('documentation_phpword.docx');

$docx->replaceVariableByHTML('ADDRESS', 'inline', '<p style="font-family: verdana; font-size: 11px">Привет траляля <b>Spain</b></p>', array('isFile' => false, 'parseDivsAsPs' => true, 'downloadImages' => false));
$docx->replaceVariableByHTML('dovod', 'block', 'http://www.2mdc.com/PHPDOCX/example.html', array('isFile' => true, 'parseDivsAsPs' => true,  'filter' => '#capa_bg_bottom', 'downloadImages' => true));
$docx->replaceVariableByHTML('CHUNK_2', 'block', 'http://www.2mdc.com/PHPDOCX/example.html', array('isFile' => true, 'parseDivsAsPs' => false,  'filter' => '#lateral', 'downloadImages' => true));

$docx->createDocx('example_replaceTemplateVariableByHTML_1');