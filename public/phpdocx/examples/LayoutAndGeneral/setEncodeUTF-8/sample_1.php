<?php

require_once '../../../classes/CreateDocx.inc';

$docx = new CreateDocx();

$docx->setEncodeUTF8();

$docx->createDocx('example_setEncodeUTF8_1');