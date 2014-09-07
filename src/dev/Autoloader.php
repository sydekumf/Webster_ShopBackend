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
class AutoLoader {

    static private $classNames = array();

    /**
     * Store the filename (sans extension) & full path of all ".php" files found
     */
    public static function registerDirectory($dirName) {

        $di = new DirectoryIterator($dirName);
        foreach ($di as $file) {

            if ($file->isDir() && !$file->isLink() && !$file->isDot()) {
                // recurse into directories other than a few special ones
                self::registerDirectory($file->getPathname());
            } elseif (substr($file->getFilename(), -4) === '.php') {
                // save the class name / path of a .php file found
                $classes = AutoLoader::file_get_php_classes($file->getPathname());
                foreach($classes as $class){
                    AutoLoader::registerClass($class, $file->getPathname());
                }
            }
        }
    }

    public static function registerClass($className, $fileName) {
        AutoLoader::$classNames[$className] = $fileName;
    }

    public static function loadClass($className) {
//        error_log(var_export($className, true));
        error_log(var_export(AutoLoader::$classNames, true));
        if (isset(AutoLoader::$classNames[$className])) {
//            error_log('setted: ' . $className, true);
            require_once(AutoLoader::$classNames[$className]);
        }
    }

    function file_get_php_classes($filepath) {
        $php_code = file_get_contents($filepath);
        $classes = AutoLoader::get_php_classes($php_code);
        return $classes;
    }

    function get_php_classes($php_code) {
        $classes = array();
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if (   $tokens[$i - 2][0] == T_CLASS
                && $tokens[$i - 1][0] == T_WHITESPACE
                && $tokens[$i][0] == T_STRING) {

                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }
        return $classes;
    }
}

spl_autoload_register(array('AutoLoader', 'loadClass'));