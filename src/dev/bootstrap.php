<?php
/**
 * <REPLACE WITH FILE DESCRIPTION>
 *
 * PHP version 5
 *
 * @category   AppServer
 * @package    $package
 * @subpackage $subPackage
 * @author     Florian Sydekum <f.sydekum@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 */

$projectHome = __DIR__
    . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . 'WEB-INF'
    . DIRECTORY_SEPARATOR . 'classes';

// initialize the include path
$includePaths = array(
    get_include_path(),
    $projectHome
);
set_include_path(implode(PATH_SEPARATOR, $includePaths));
spl_autoload_register('autoloadForUnitTestSkeleton');

function autoloadForUnitTestSkeleton($class)
{
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
        $fileName = $path . DIRECTORY_SEPARATOR . $file;
        if (file_exists($fileName)) {
            include_once $file;
            if (class_exists($class, false)) {
                return true;
            }
        }

    }
    return false;
}