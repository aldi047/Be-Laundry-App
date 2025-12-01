<?php
//Routing Publik
defined('API_DIR') or define('API_DIR', 'Api');
defined('API_KEY') or define('API_KEY', 'api');

//Routing Admin
defined('ADMIN_DIR') or define('ADMIN_DIR', 'Admin');
defined('ADMIN_KEY') or define('ADMIN_KEY', 'admin');

//Routing Mobile
defined('MOBILE_DIR') or define('MOBILE_DIR', 'Mobile');
defined('MOBILE_KEY') or define('MOBILE_KEY', 'mobile');

//Modul Main
defined('MAIN_DIR') or define('MAIN_DIR', 'Main');
defined('MAIN_KEY') or define('MAIN_KEY', 'main');

// Separator
defined('TDS') or define('TDS', DIRECTORY_SEPARATOR);

if (!function_exists('xstrlen')) {
    function xstrlen($variable): int
    {
        return strlen(strval($variable));
    }
}

function controller_path($controller, $modul = '')
{
    if (strlen($modul) == 0) {
        $path = path_generator($controller);
    } else {
        $modulDir = get_modul_directory($modul);
        $path = path_generator($modulDir . TDS . $controller);
    }

    return $path;
}

function path_generator($path)
{
    switch (get_active_os()) {
        case 'darwin':
        case 'mac':
        case 'unix':
        case 'linux':
            $path = str_replace("/", "\\", $path);
            break;
        default:
            break;
    }

    return $path;
}

function get_modul_directory($modul = '')
{
    if (empty($modul)) {
        return '';
    }

    $modul = strtolower($modul);
    $dirName = implode('', array_map('ucfirst', \App\Helpers\UtilityHelper::cleanExplode($modul, '-')));
    return $dirName;
}

function get_active_os()
{
    return strtolower(php_uname('s'));
}