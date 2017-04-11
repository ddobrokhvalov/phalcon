<?php

require_once '../../../classes/CreateDocx.inc';

$docx = new CreateDocxFromTemplate('../../files/TemplateVariables.docx');

print_r($docx->getTemplateVariables());


