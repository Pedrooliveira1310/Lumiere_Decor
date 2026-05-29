<?php
define('BASE_PATH', __DIR__);
define('BASE_URL', '');

require_once BASE_PATH . '/autoload.php';

use Lumiere\Models\Usuario;
use Lumiere\Models\Item;
use Lumiere\Models\Pacote;
use Lumiere\Models\Categoria;
use Lumiere\Models\Historico;
use Lumiere\Auth\Session;

Usuario::init(BASE_PATH);
Item::init(BASE_PATH);
Pacote::init(BASE_PATH);
Categoria::init(BASE_PATH);
Historico::init(BASE_PATH);
Session::start();
