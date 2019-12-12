<?php

require_once __DIR__ . '/vendor/autoload.php';

use Siga98\Helpers\CryptoHelper;
use Siga98\Helpers\StringHelper;

d(StringHelper::camelCase('hola_mundo'));
d(StringHelper::generateStrongPassword(20, false, 'luds'));
d(CryptoHelper::encryptStrong('thisismypassword'));
d(CryptoHelper::decryptStrong('dms5YjNwY0cxYWdsaHlYeUNOYkdGMUJPTUdubkQyalQzc2ZJeHh5VUdFTXVnVkpZcEZRdmdOOFQ3cWhPSGJrRGx2OHFEWFkvQThLVitweTRXMk9nblE9PQ=='));
dd(CryptoHelper::checkStrong('thisismypassword', 'dms5YjNwY0cxYWdsaHlYeUNOYkdGMUJPTUdubkQyalQzc2ZJeHh5VUdFTXVnVkpZcEZRdmdOOFQ3cWhPSGJrRGx2OHFEWFkvQThLVitweTRXMk9nblE9PQ=='));