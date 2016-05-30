<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Template;

/**
 * Class Template
 * @package Fraym\Template
 * @Injectable(lazy=true)
 */
class Template
{
    const DISABLED_FUNCTIONS = array(
        'shell_exec',
        'apache_child_terminate',
        'apache_setenv',
        'define_syslog_variables',
        'escapeshellarg',
        'escapeshellcmd',
        'eval',
        'exec',
        'mail',
        'bzopen',
        'gzopen',
        'Java',
        'COM',
        'assert',
        'str_repeat',
        'unserialize',
        'curl_exec',
        'curl_init',
        'mysql_connect',
        'register_tick_function',
        'register_shutdown_function',
        'set_error_handler',
        'set_exception_handler',
        'session_set_save_handler',
        'sqlite_create_aggregate',
        'sqlite_create_function',
        'preg_replace_callback',
        'spl_autoload_register',
        'iterator_apply',
        'ReflectionFunction',
        'extract',
        'parse_str',
        'putenv',
        'file_exists',
        'ftp_nb_get',
        'fp',
        'fput',
        'header',
        'ini_set',
        'ftp_connect',
        'ftp_exec',
        'ftp_get',
        'ftp_login',
        'ftp_nb_fput',
        'ftp_put',
        'ftp_raw',
        'ftp_rawlist',
        'highlight_file',
        'include_once',
        'require_once',
        'require',
        'ini_alter',
        'ini_get_all',
        'ini_get',
        'ini_restore',
        'inject_code',
        'mysql_pconnect',
        'openlog',
        'passthru',
        'php_uname',
        'phpAds_remoteInfo',
        'phpAds_XmlRpc',
        'phpAds_xmlrpcDecode',
        'phpAds_xmlrpcEncode',
        'popen',
        'getenv',
        'lchgrp',
        'lchown',
        'phpinfo',
        'rmdir',
        'create_function',
        'posix_getpwuid',
        'pcntl_exec',
        'posix_kill',
        'posix_mkfifo',
        'posix_setpgid',
        'posix_setsid',
        'posix_setuid',
        'posix_uname',
        'proc_close',
        'proc_get_status',
        'proc_nice',
        'proc_terminate',
        'proc_open',
        'get_cfg_var',
        'get_current_user',
        'getcwd',
        'pfsockopen',
        'fsockopen',
        'syslog',
        'system',
        'xmlrpc_entity_decode',
        'fopen',
        'chmod',
        'chgrp',
        'chown',
        'delete',
        'copy',
        'rename',
        'fgetc',
        'fgetcsv',
        'file_get_contents',
        'file_put_contents',
        'fread',
        'fscanf',
        'fstat',
        'link',
        'symlink',
        'tmpfile',
        'readfile',
        'file',
        'readlink',
        'tempnam',
        'popen',
        'parse_ini_file',
        'parse_ini_string',
        'touch',
        'mkdir',
        'unlink',
        'move_uploaded_file',
        'glob',
        'imagepng',
        'imagewbmp',
        'image2wbmp',
        'imagejpeg',
        'imagexbm',
        'imagegif',
        'imagegd',
        'imagegd2',
        'imagecreatefromgif',
        'imagecreatefromjpeg',
        'imagecreatefrompng',
        'imagecreatefromwbmp',
        'imagecreatefromxbm',
        'imagecreatefromxpm',
        'exif_read_data',
        'read_exif_data',
        'exif_thumbnail',
        'exif_imagetype',
        'exif_imagetype',
        'exif_imagetype',
        'highlight_file',
        'show_source',
    );

    /**
     * @var null
     */
    private $currentTemplateContent = null;

    /**
     * @var string
     */
    private $mainTemplate = '';

    /**
     * @var string
     */
    private $defaultDir = 'Default';

    /**
     * @var string
     */
    private $templateDir = 'Template';

    /**
     * @var string
     */
    private $pageTitle = '';

    /**
     * @var string
     */
    private $pageDescription = '';

    /**
     * @var array
     */
    private $keywords = [];

    /**
     * @var array
     */
    private $headData = [];

    /**
     * @var array
     */
    private $footData = [];

    /**
     * @var null
     */
    private $template = null;

    /**
     * @var array
     */
    private $parserLog = [];

    /**
     * Holds the current module name
     *
     * @var string
     */
    public $moduleName = '';

    /**
     * Current site folder
     *
     * @var null
     */
    private $siteTemplateDir = null;

    /**
     * The javascript stack for rendering the javascript files to the output source
     *
     * @var array
     */
    private $jsFiles = [];

    /**
     * The css stack for rendering the css files to the output source
     *
     * @var array
     */
    private $cssFiles = [];

    /**
     * @var bool
     */
    private $outputFilterEnabled = true;

    /**
     * @var array
     */
    private $outputFilters = [];

    /**
     * holds all template vars
     *
     * @var array
     */
    private $templateVars = [];

    /**
     * holds all template vars from the last template
     *
     * @var array
     */
    private $lastTemplateVars = [];

    /**
     * holds all template vars
     *
     * @var array
     */
    private $globalTemplateVars = [];

    /**
     * All user pseudo template functions
     *
     * @var array
     */
    private $templateFunctions = [];

    /**
     * @Inject
     * @var \Fraym\Block\BlockParser
     */
    protected $blockParser;

    /**
     * @Inject
     * @var \Fraym\Translation\Translation
     */
    protected $translation;

    /**
     * @Inject
     * @var \Fraym\EntityManager\EntityManager
     */
    protected $entityManager;

    /**
     * @Inject
     * @var \Fraym\ServiceLocator\ServiceLocator
     */
    protected $serviceLocator;

    /**
     * @Inject
     * @var \Fraym\Core
     */
    protected $core;

    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @Inject
     * @var \Fraym\Registry\Config
     */
    protected $config;

    /**
     * @Inject
     * @var \Fraym\Locale\Locale
     */
    protected $locale;

    /**
     * @Inject
     * @var \Fraym\Cache\Cache
     */
    protected $cache;

    /**
     * @Inject
     * @var \Fraym\Route\Route
     */
    protected $route;

    /**
     * @var bool
     */
    private $cachingDisabled = false;

    /**
     * @return boolean
     */
    public function isCachingDisabled()
    {
        return $this->cachingDisabled;
    }

    /**
     * @param boolean $cachingDisabled
     */
    public function setCachingDisabled($cachingDisabled)
    {
        $this->cachingDisabled = $cachingDisabled;
    }

    public function disabledFunctions()
    {
        error_log('Dangerous function call in template!');
    }

    /**
     * Add the default custom template functions
     */
    public function __construct()
    {
        // Disabled PHP functions in templates
        foreach (self::DISABLED_FUNCTIONS as $function) {
            $this->addFunction($function, [&$this, 'disabledFunctions']);
        }

        $this->addFunction('menuItem', [&$this, 'getMenuItem']);
        $this->addFunction('i', [&$this, 'getInstance']);
        $this->addFunction('css', [&$this, 'addCssFile']);
        $this->addFunction('js', [&$this, 'addJsFile']);
        $this->addFunction('include', [&$this, 'includeTemplate']);
        $this->addFunction('shorten', [&$this, 'shorten']);
        $this->addFunction('age', [&$this, 'age']);
        $this->addFunction('isLast', [&$this, 'isLast']);
        $this->addFunction('isFirst', [&$this, 'isFirst']);
        $this->addFunction('formatCurrency', [&$this, 'formatCurrency']);
        $this->addFunction('formatDate', [&$this->locale, 'formatDate']);
        $this->addFunction('formatDateTime', [&$this->locale, 'formatDateTime']);
        $this->addFunction('_', [&$this->translation, 'getTranslation']);
        $this->addFunction('et', [&$this->entityManager, 'getEntityTranslation']);
    }

    /**
     * @param $number
     * @param string $symbol
     * @return mixed
     */
    public function formatCurrency($number, $symbol = '')
    {
        $fmt = new NumberFormatter($this->locale->getLocale()->locale, NumberFormatter::CURRENCY);
        return $fmt->formatCurrency($number, $symbol);
    }

    /**
     * Template function to check if a array item is the last
     *
     * @param $array
     * @param $propKey
     * @return bool
     */
    public function isLast($array, $propKey)
    {
        $array = (array)$array;
        end($array);
        $key = key($array);
        return $key === $propKey;
    }

    /**
     * Template function to check if a array item is the first
     *
     * @param $array
     * @param $propKey
     * @return bool
     */
    public function isFirst($array, $propKey)
    {
        $array = (array)$array;
        reset($array);
        $key = key($array);
        return $key === $propKey;
    }

    /**
     * @param $birthday
     * @return string
     */
    public function age($birthday)
    {
        return $birthday ? $birthday->diff(new \DateTime('now'))->y : '';
    }

    /**
     * @param $text
     * @param int $length
     * @param string $append
     * @return string
     */
    public function shorten($text, $length = 200, $append = '...')
    {
        $text = strip_tags($text);
        $text = trim(preg_replace('#[\s\n\r\t]{2,}#', ' ', $text));
        $textTemp = $text;
        while (substr($text, $length, 1) != " ") {
            $length++;
            if ($length > strlen($text)) {
                break;
            }
        }
        $text = substr($text, 0, $length);
        return $text . ((!empty($append) && $text != '' && strlen($textTemp) > $length) ? $append : '');
    }

    /**
     * Template function to get a menuItem
     *
     * @param $menuItemId
     * @return mixed
     */
    public function getMenuItem($menuItemId)
    {
        return $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById($menuItemId);
    }

    /**
     * Adds a user template funktion
     *
     * @param  $pseudoFunctionName
     * @param  $realFunction
     * @return void
     */
    public function addFunction($pseudoFunctionName, $realFunction)
    {
        $this->templateFunctions[$pseudoFunctionName] = $realFunction;
        return $this;
    }

    /**
     * Sets the view module name to get the correct module template from the module folder
     *
     * @param  $name
     * @return void
     */
    public function setView($name)
    {
        $this->moduleName = $name;
        return $this;
    }

    /**
     * @param $dir
     */
    public function setSiteTemplateDir($dir)
    {
        $this->siteTemplateDir = $dir;
    }

    /**
     * @return string
     */
    public function getTemplateDir()
    {
        return $this->templateDir;
    }

    /**
     * @return string
     */
    public function getDefaultDir()
    {
        return $this->defaultDir;
    }

    /**
     * Assign a var to the template.
     *
     * @param  $templateVar
     * @param  $value
     * @param  $toObject
     * @return Tpl
     */
    public function assign($templateVar, $value, $toObject = true)
    {
        $this->templateVars[$templateVar] = $toObject ? $this->arrayToObject($value) : $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getTemplateVars()
    {
        return $this->templateVars;
    }

    /**
     * @param $templateVar
     * @return null
     */
    public function getTemplateVar($templateVar)
    {
        return isset($this->templateVars[$templateVar]) ? $this->templateVars[$templateVar] : null;
    }

    /**
     * Assign a var to the template.
     *
     * @param  $templateVar
     * @param  $value
     * @return Tpl
     */
    public function assignGlobalVar($templateVar, $value)
    {
        $this->globalTemplateVars[$templateVar] = $this->arrayToObject($value);
        return $this;
    }

    /**
     * @param $templateVar
     * @return null
     */
    public function getGlobalVar($templateVar)
    {
        return isset($this->globalTemplateVars[$templateVar]) ? $this->globalTemplateVars[$templateVar] : null;
    }

    /**
     * Converts all arrays to an object for the template
     *
     * @param  $var
     * @return stdClass
     */
    public function arrayToObject($var)
    {
        if (is_string($var) ||
            is_bool($var) ||
            is_object($var) ||
            is_numeric($var) ||
            is_null($var) ||
            is_callable($var)
        ) {
            return $var;
        }

        $object = new \stdClass();
        foreach ($var as $name => $value) {
            $name = (string)$name;
            if (is_object($value) || is_callable($value)) {
                $object->{$name} = $value;
            } elseif ($name !== null && $name !== '') {
                $object->{$name} = $this->arrayToObject($value);
            }
        }
        return $object;
    }

    /**
     * Load the template file content.
     *
     * @return bool|string
     * @throws \Exception
     */
    public function getTemplateContent()
    {
        $tpl = $this->template;
        $content = '';
        if (substr($tpl, 0, 7) === 'string:') {
            $content = substr($tpl, 7);
        } elseif (!empty($tpl)) {
            // add file extension if is not set
            if (strpos(strtolower($tpl), '.tpl') === false) {
                $tpl .= '.tpl';
            }
            $templateFile = $this->getTemplateFilePath($tpl);
            if ($templateFile === false || !is_file($templateFile)) {
                trigger_error('Template file not found: ' . $tpl, E_USER_NOTICE);
                return false;
            }
            $content = file_get_contents($templateFile);
        }
        return $content;
    }

    /**
     * Template helper function to include another template file.
     *
     * @param $file
     * @param array $vars
     * @param null $cacheKey
     * @param bool $showError
     * @return bool|mixed|string
     */
    public function includeTemplate($file, $vars = [], $cacheKey = null, $showError = true)
    {
        $filename = $this->getTemplateFilePath($file);

        if (is_file($filename)) {
            if (count($vars)) {
                foreach ($vars as $templateVarName => $val) {
                    $this->assign($templateVarName, $val);
                }
            } else {
                $this->templateVars = $this->lastTemplateVars;
            }

            $content = $this->cache->getDataCache($cacheKey);

            if ($content === false) {
                $content = $this->prepareTemplate(file_get_contents($filename));

                if ($cacheKey !== null) {
                    $this->cache->setDataCache($cacheKey, $content);
                }
            }

            return $content;
        }
        return $showError ? "Template: {$file} not found." : "";
    }

    /**
     * Get all assigned template vars.
     *
     * @param bool $clearTemplateVars
     * @return string
     */
    private function getTemplateVarString($clearTemplateVars = true)
    {
        $templateVarString = '';
        foreach ($this->templateVars as $var => $value) {
            $globKey = microtime() . '_' . $var;
            $GLOBALS[$globKey] = $value;
            $templateVarString .= "$$var = " . '$GLOBALS["' . $globKey . '"]; ';
        }

        if ($clearTemplateVars) {
            $this->resetTemplateVars();
        }
        return "<?php $templateVarString ?>";
    }

    /**
     * Clear all assigned template vars
     *
     * @return $this
     */
    private function resetTemplateVars()
    {
        $this->lastTemplateVars = $this->templateVars;
        $this->templateVars = [];
        return $this;
    }

    /**
     * Replace the custom template functions
     *
     * @param $content
     * @return mixed
     */
    private function replaceCustomTemplateFunctions($content)
    {
        // replace the custom template function
        foreach ($this->templateFunctions as $pseudoFunction => &$realFunction) {

            $GLOBALS['PSEUDOFUNC_TEMP_' . $pseudoFunction] = $realFunction;

            $func = function ($match) use (&$realFunction, $pseudoFunction) {
                if (isset($match[0]) && isset($match[3])) {
                    $pseudoFunctionName = 'PSEUDOFUNC_TEMP_' . $pseudoFunction;
                    $this->assign(
                        $pseudoFunctionName,
                        function () use (&$realFunction) {
                            return call_user_func_array($realFunction, func_get_args());
                        }
                    );

                    return preg_replace(
                        '/\b' . $match[3] . '\b/',
                        "$$pseudoFunctionName",
                        '{' . $match[0] . '}',
                        1
                    );
                }
            };

            $content = preg_replace_callback(
                '/{(((' . preg_quote($pseudoFunction) . ')(\((.*?)\))[^\}]*))\}/is',
                $func,
                $content
            );


            $func = function ($match) use (&$realFunction, $pseudoFunction) {
                if (isset($match[0]) && isset($match[4])) {
                    $pseudoFunctionName = 'PSEUDOFUNC_TEMP_' . $pseudoFunction;
                    $this->assign(
                        $pseudoFunctionName,
                        function () use (&$realFunction) {
                            return call_user_func_array($realFunction, func_get_args());
                        }
                    );
                    return preg_replace('/\b' . $match[3] . '\b/', "$$pseudoFunctionName", $match[0], 1);
                }
            };

            $content = preg_replace_callback(
                '/\{[^\}]*(((\b' . preg_quote($pseudoFunction) . '\b)(\((.*?)\))[^\}]*))\}/',
                $func,
                $content
            );
        }
        return $content;
    }

    private function replaceTemplateTags($content)
    {
        // clean default php tags from template
        $content = preg_replace_callback(
            '/(\<\?.*\?\>)/is',
            function ($found) {
                return htmlspecialchars($found[0], ENT_QUOTES, 'utf-8');
            },
            $content
        );

        // remove comments
        $content = preg_replace('#\{\*.*\*\}#Uis', '', $content);

        // replace close tags
        $content = str_ireplace(
            ['{/if}', '{/foreach}', '{/while}', '{/for}', '{/switch}', '{/function}'],
            ['{endif}', '{endforeach}', '{endwhile}', '{endfor}', '{endswitch}', '{endfunction}'],
            $content
        );

        $pseudoFunctionName = '';
        foreach ($this->templateFunctions as $pseudoFunction => $realFunction) {
            $pseudoFunctionName .= '$PSEUDOFUNC_TEMP_' . $pseudoFunction . ' = $GLOBALS["PSEUDOFUNC_TEMP_' . $pseudoFunction . '"];';
        }

        $content = preg_replace_callback('/\{((function)\s+([^\}]*)\(([^\}]*)\)([^\}]*))\}/is', function($found) use ($pseudoFunctionName) {
            return '<?php if(function_exists(\'' . $found[3] . '\') === false) { function ' . $found[3] . '(' . $found[4] . ') { ' . $pseudoFunctionName . ' ?>';
        }, $content);

        // replace function close tag
        $content = preg_replace('/{endfunction}/is', '<?php } } ?>', $content);

        // check for valid objects
        $content = preg_replace_callback(
            '/\{(if|elseif|foreach|while|for|switch)[^\}]*\}/is',
            [$this, 'regexObjectVarCheck'],
            $content
        );

        // check string and echo the string
        $content = preg_replace(
            '/\{\{\$([^\s][^\}]*[^\}]*)\}\}/is',
            '<?php echo (string)$$1; ?>',
            $content
        );

        // escape strings
        $content = preg_replace(
            '/\{\$([^\s][^\}]*)\}/is',
            '<?php $__TPL_DATA__ = $$1; echo htmlspecialchars(isset($__TPL_DATA__) ? (string)$__TPL_DATA__ : "", ENT_QUOTES, \'utf-8\'); unset($__TPL_DATA__); ?>',
            $content
        );

        // no output vars
        $content = preg_replace('/\{@([^\s][^\}]*)\}/is', '<?php $1; ?>', $content);

        // echo none objects and booleans
        $content = preg_replace(
            '/\{((?!if|elseif|\$|@|foreach|while|for|switch|endif|endforeach|endwhile|endfor|endswitch|else)[^\s][^\}]*[^\(][^\)])\}/is',
            '<?php echo (!is_bool($1) && !is_object($1) ? (string)$1 :""); ?>',
            $content
        );

        // echo strings
        $content = preg_replace(
            '/\{((?!if|elseif|\$|@|foreach|while|for|switch|endif|endforeach|endwhile|endfor|endswitch|else)[^\s][^\}]*)\}/is',
            '<?php echo (string)$1; ?>',
            $content
        );

        // replace php tags
        $content = preg_replace(
            '/\{((foreach)(\s?\(?\s?([^\}]*)\s+as\s+[^\}]*))\}/is',
            '<?php if(is_array($4) || is_object($4)) { $2($3): ?>',
            $content
        );

        // replace php close tags
        $content = preg_replace(
            '/\{(endforeach[^\s]*)\}/is',
            '<?php $1; } ?>',
            $content
        );

        // replace php tags
        $content = preg_replace(
            '/\{((if|elseif|while|for|switch)([^\}]*))\}/is',
            '<?php $2($3): ?>',
            $content
        );

        // replace else tag
        $content = preg_replace('/\{((else)[^\}]*)\}/is', '<?php $1: ?>', $content);

        // replace php close tags
        $content = preg_replace(
            '/\{(endif|endwhile|endfor|endswitch[^\s][^\}]*)\}/is',
            '<?php $1; ?>',
            $content
        );

        // replace object properties and methods
        $content = preg_replace_callback('/<\?php.*?\s*\?>/is', [$this, 'regexReplacePointer'], $content);

        return $content;
    }

    /**
     * @param $code
     * @param $text
     * @param $file
     * @param $line
     * @return bool
     */
    public function errorHandler($code, $text, $file, $line)
    {
        if ($code !== E_NOTICE && $code !== E_USER_NOTICE) {
            ob_clean();
            error_log("$text $file $line");
            $lines = explode("\n", $this->currentTemplateContent);
            $linePhp = explode("\n", $this->core->getScriptCode());
            echo "{$text} \n\n" . $lines[$line-1];
            echo "\n\nPhp Code: \n\n" . $linePhp[$line-1];
            exit(0);
        }
        return true;
    }

    /**
     * @param string $tpl
     */
    public function setMainTemplate($tpl)
    {
        $this->mainTemplate = $tpl;
    }

    /**
     * @return string
     */
    public function getMainTemplate()
    {
        if (empty($this->mainTemplate)) {
            return $this->route->getCurrentMenuItemTranslation()->menuItem->template ?
                $this->route->getCurrentMenuItemTranslation()->menuItem->template->html :
                $this->getDefaultMenuItemTemplate();
        }
        return $this->mainTemplate;
    }

    /**
     * @return string
     */
    private function getDefaultMenuItemTemplate() {
        $this->setView(self::class);
        $templateFile = $this->getTemplateFilePath('NoTemplate');
        return file_get_contents($templateFile);
    }

    /**
     * @param null $content
     * @return string
     */
    public function prepareTemplate($content = null)
    {
        if ($content === null) {
            // get the template content
            $content = $this->getTemplateContent();
        }

        $this->currentTemplateContent = $content;

        $content = $this->replaceCustomTemplateFunctions($content);
        $content = $this->replaceTemplateTags($content);

        $this->template = null;
        $vars = $this->getTemplateVarString();

        return $this->core->includeScript($vars . $content, [&$this, 'errorHandler']);
    }

    /**
     * @return array
     */
    public function getParserLog()
    {
        return $this->parserLog;
    }

    /**
     * @throws Exception
     * @param  $tpl
     * @return bool|mixed|string
     */
    public function fetch($tpl = null)
    {
        $this->setTemplate($tpl);
        $content = $this->prepareTemplate();
        $content = $this->blockParser->parse($content);
        return $content;
    }

    /**
     * @throws Exception
     * @param  $string
     * @return bool|mixed|string
     */
    public function fetchString($string = null)
    {
        return $this->fetch('string:' . $string);
    }

    /**
     * @param null $tpl
     * @return $this
     */
    public function setTemplate($tpl = null)
    {
        if ($tpl === null && $this->template === null) {
            $this->template = $this->moduleName;
        } elseif ($tpl !== null) {
            $this->template = (string)$tpl;
        }
        return $this;
    }

    /**
     * @return null
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param $found
     * @return mixed
     */
    public function regexObjectVarCheck($found)
    {
        $content = preg_replace('/((\$[a-zA-Z0-9_]*)(\.+[a-zA-Z0-9_]*)+)/is', '$1', $found[0]);
        return $content;
    }

    /**
     * @param $found
     * @return mixed
     */
    public function regexReplacePointer($found)
    {
        $result = preg_replace("/\.(\w+)(?=(?:[^']*'[^']*')*[^']*$)/is", "->{'$1'}", current($found));
        return $result;
    }

    /**
     * @param $templateFile
     * @return bool|string
     */
    public function getTemplateFilePath($templateFile)
    {
        if (strpos(strtolower($templateFile), '.tpl') === false) {
            $templateFile .= '.tpl';
        }

        $folders = $this->getTemplateFolders();

        foreach ($folders as $folder) {
            if (is_file($file = $folder . DIRECTORY_SEPARATOR . $templateFile)) {
                return $file;
            } elseif (is_file(
                $file = $folder . DIRECTORY_SEPARATOR .
                    $this->core->createDirNameFromClassName(
                        $this->moduleName
                    ) . DIRECTORY_SEPARATOR .
                    basename(
                        str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $templateFile)
                    )
            )
            ) {
                return $file;
            } elseif (is_file($templateFile)) {
                return realpath($templateFile);
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getTemplateFolders()
    {

        // Site template dirs
        if ($this->siteTemplateDir) {
            $folders[] = realpath($this->siteTemplateDir);
        }

        // Default extension dir
        if ($this->defaultDir) {
            $folders[] = $this->templateDir . DIRECTORY_SEPARATOR . $this->defaultDir;
            // Template base dir
            $folders[] = $this->templateDir;
        }

        foreach ($folders as $key => $folder) {
            if (!is_dir($folder)) {
                unset($folders[$key]);
            }
        }

        return $folders;
    }

    /**
     * @param null $tpl
     * @return void
     */
    public function render($tpl = null)
    {
        echo $this->fetch($tpl);
        return $this;
    }

    /**
     * @param null $string
     * @return void
     */
    public function renderString($string = null)
    {
        return $this->render('string:' . $string);
    }

    /**
     * Render the main template
     */
    public function renderMainTemplate()
    {
        $this->renderString($this->getMainTemplate());
    }

    /**
     * @param string $title
     * @return void
     */
    public function setPageTitle($title)
    {
        $this->pageTitle = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * @param string $description
     * @return void
     */
    public function setPageDescription($description)
    {
        $this->pageDescription = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageDescription()
    {
        return $this->pageDescription;
    }

    /**
     * @return array
     */
    public function getKeyword()
    {
        return $this->keywords;
    }

    /**
     * @param string $word
     * @param bool $truncate
     * @return void
     */
    public function setKeyword($word, $truncate = false)
    {
        if ($truncate) {
            $this->keywords = [];
        }

        $this->keywords[] = strtolower($word);
        return $this;
    }

    /**
     * @param array $words
     * @param bool $truncate
     * @return $this
     */
    public function setKeywords($words = [], $truncate = false)
    {
        if ($truncate) {
            $this->keywords = [];
        }

        $this->keywords = array_unique(array_merge($this->keywords, $words));
        return $this;
    }

    /**
     * @param $file
     * @param string $group
     * @param null $key
     */
    public function addJsFile($file, $group = 'default', $key = null)
    {
        $hash = $key ? : md5($file);
        $this->jsFiles[$group][$hash] = $file;
    }

    /**
     * @param string $group
     * @return array
     */
    public function getJsFiles($group = 'default')
    {
        return isset($this->jsFiles[$group]) ? $this->jsFiles[$group] : [];
    }

    /**
     * @param $file
     * @param string $group
     * @param null $key
     */
    public function addCssFile($file, $group = 'default', $key = null)
    {
        $hash = $key ? : md5($file);
        $this->cssFiles[$group][$hash] = $file;
    }

    /**
     * @param string $group
     * @return array
     */
    public function getCssFiles($group = 'default')
    {
        return isset($this->cssFiles[$group]) ? $this->cssFiles[$group] : [];
    }

    /**
     * For client outputs if we dont want do replace 'Block' elements
     *
     * @param bool $enableStatus
     * @return void
     */
    public function setOutputFilterEnable($enableStatus = true)
    {
        $this->outputFilterEnabled = $enableStatus;
        return $this;
    }

    /**
     * preOutputFiler
     *
     * @param string $source
     * @return string
     */
    public function outputFilter($source)
    {
        // if cached return the source only
        if ($this->outputFilterEnabled === false) {
            return $source;
        }

        $source = $this->blockParser->parse($source, __FUNCTION__);

        $source = $this->addCmsInfo()->prependHeadDataToDocument($source);
        $source = $this->appendFootDataToDocument($source);

        $this->headData = [];
        $this->footData = [];
        // replace title
        $title = htmlspecialchars(trim($this->pageTitle), ENT_QUOTES, 'utf-8');
        $search = '/<title>(.*)<\/title>/im';
        $replaceWith = empty($title) ? '' : "<title>$title</title>";
        $source = preg_replace($search, $replaceWith, $source);

        // replace description
        $description = htmlspecialchars(trim($this->pageDescription), ENT_QUOTES, 'utf-8');
        $search = '/<meta\s*name=\"description\"\s*content=\"(.*)\"\s*\/>/im';
        $replaceWith = empty($description) ? '' : '<meta name="description" content="' . $description . '" />';
        $source = preg_replace($search, $replaceWith, $source);

        if (count($this->keywords)) {
            // replace keywords
            $keywords = htmlspecialchars(trim(implode(',', $this->keywords), ','), ENT_QUOTES, 'utf-8');
            $search = '/<meta\s*name=\"keywords\"\s*content=\"(.*)\"\s*\/>/im';
            $replaceWith = empty($keywords) ? '' : '<meta name="keywords" content="' . $keywords . '" />';
            $source = preg_replace($search, $replaceWith, $source);
        }

        return $this->execOutputFilters($source);
    }

    /**
     * Execute custom output filters
     *
     * @param string $source
     * @return string
     */
    private function execOutputFilters($source)
    {
        foreach ($this->outputFilters as $callback) {
            if (is_array($callback) && count($callback) == 2 && isset($callback[0]) && isset($callback[1])) {
                $function = $callback[1];
                if (is_object($callback[0])) {
                    $source = $callback[0]->$function($source);
                }
            }
        }
        return $source;
    }

    /**
     * @param array $callback array(&this, 'functionName')
     */
    public function addOutputFilter($callback)
    {
        $this->outputFilters[] = $callback;
    }

    /**
     * @return array
     */
    public function getOutputFilters()
    {
        return $this->outputFilters;
    }

    /**
     * @param $string
     * @param null $key
     * @return $this
     */
    public function addHeadData($string, $key = null)
    {
        if ($key) {
            $this->headData[$key] = $string;
        } else {
            $this->headData[] = $string;
        }
        return $this;
    }

    /**
     * @param $string
     * @param null $key
     * @return $this
     */
    public function addFootData($string, $key = null)
    {
        if ($key) {
            $this->footData[$key] = $string;
        } else {
            $this->footData[] = $string;
        }
        return $this;
    }

    /**
     * @param $name
     * @return null
     */
    public function getInstance($name)
    {
        return $this->serviceLocator->get($name);
    }

    /**
     * Adds license / copyright information to the html head element
     *
     * @return Template
     */
    private function addCmsInfo()
    {
        $name = \Fraym\Core::NAME;
        $author = \Fraym\Core::AUTHOR;
        $website = \Fraym\Core::WEBSITE;
        $year = \Fraym\Core::PUBLISHED < intval(date('Y')) ? \Fraym\Core::PUBLISHED . ' - ' .
            date('Y') :
            \Fraym\Core::PUBLISHED;

        $poweredByText = "\n\t<!-- \n\t\tThis website is powered by {$name} - power of simplicity!\n\t\t{$name} is a free open source content management system initially created by {$author} and licensed under GNU/GPL version 2 or later.\n\t\t{$name} is copyright {$year} of {$author}. Extensions are copyright of their respective owners.\n\t\tInformation and contribution at {$website}\n\t-->\n";
        $metaTagGenerator = '<meta name="generator" content="' . $name . ' (' . $website . ')' . '">';
        $this->addHeadData($poweredByText, 'powered-by');
        $this->addHeadData($metaTagGenerator, 'generator');

        return $this;
    }

    /**
     * Prepend a string to the html head element
     *
     * @param $source
     * @return mixed
     */
    private function prependHeadDataToDocument($source)
    {
        $siteTpl = $source;
        foreach (array_reverse($this->headData) as $data) {
            $siteTpl = str_replace($data, '', $siteTpl); // replace for caching output
            $siteTpl = preg_replace('#(<head\b[^>]*>)#is', '$1' . $data, $siteTpl);
        }
        return $siteTpl;
    }

    /**
     * Prepend a string to the html head element
     *
     * @param $source
     * @return mixed
     */
    private function appendFootDataToDocument($source)
    {
        $siteTpl = $source;
        foreach (array_reverse($this->footData) as $data) {
            $siteTpl = str_replace($data, '', $siteTpl); // replace for caching output
            $siteTpl = preg_replace('#(<\/body>)#is', $data . '$1', $siteTpl);
        }
        return $siteTpl;
    }
}
