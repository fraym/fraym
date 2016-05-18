<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Validation;

/**
 * Class Validation
 * @package Fraym\Validation
 * @Injectable(lazy=true)
 */
class Validation
{
    const DEFAULT_ERROR_TRANSLATIONS = array(
        'NOTEMPTY' => 'Field must not be empty.',
        'MINLENGTH' => 'Max length of {maxLength} chars.',
        'MAXLENGTH' => 'Max length of {maxLength} chars.',
        'DATE' => 'Enter a date.',
        'EMAIL' => 'Enter a valid email.',
        'EMAILRFC' => 'Enter a valid email.',
        'URL' => 'Enter a valid URL.',
        'IP' => 'Enter a valid IP address.',
        'ALPHANUMERIC' => 'Only alphanumeric chars allowed.',
        'NUMERIC' => 'Only numbers are allowed.',
        'DECIMAL' => 'Only decimal numbers are allowed.',
        'COLOR' => 'Enter a hex color code.',
    );

    /**
     * @var array
     */
    private $rules = [];

    /**
     * @var array
     */
    private $errorMessages = [];

    /**
     * @var array
     */
    private $fieldsArray = [];

    /**
     * @Inject
     * @var \Fraym\Locale\Locale
     */
    protected $locale;

    /**
     * @Inject
     * @var \Fraym\Entity\FormField
     */
    protected $formField;

    /**
     * @param $fieldsToCheck
     * @return $this
     */
    public function setData($fieldsToCheck)
    {
        if (is_object($fieldsToCheck)) {
            $fieldsToCheck = get_object_vars($fieldsToCheck);
        } else {
            if (!is_array($fieldsToCheck)) {
                $fieldsToCheck = [$fieldsToCheck];
            }
        }

        $this->fieldsArray = $fieldsToCheck;
        return $this;
    }

    /**
     * Build errormessages and add rules
     *
     * @param $modelName
     * @return $this
     */
    public function getFormFieldValidation($modelName)
    {
        $formField = $this->formField->setClassName($modelName);
        $errorMessages = [];
        $defaultLocale = $this->locale->getDefaultLocale();
        foreach ($formField->getFields() as $field => $annotation) {
            if (isset($annotation['validation'])) {
                foreach ($annotation['validation'] as $validationRule) {
                    $value = isset($this->fieldsArray[$field]) ? $this->fieldsArray[$field] : '';
                    $formFieldAnnotationTranslation = $formField->getAnnotation(
                        $annotation['annotations'],
                        'Gedmo\Mapping\Annotation\Translatable'
                    );

                    if (false !== $formFieldAnnotationTranslation && is_array($value)) {
                        $translation = isset($value[$defaultLocale->locale]) ? $value[$defaultLocale->locale] : '';
                        $this->fieldsArray[$field] = $translation; // set default translation
                        $errorMessages[$field][$validationRule] = $formField->getErrorMessage(
                            $field,
                            $translation,
                            $modelName,
                            $validationRule
                        );
                    } elseif (is_array($value)) {
                        $errorMessages[$field][$validationRule] = $formField->getErrorMessage(
                            $field,
                            implode(',', $value),
                            $modelName,
                            $validationRule
                        );
                    } elseif (is_string($value)) {
                        $errorMessages[$field][$validationRule] = $formField->getErrorMessage(
                            $field,
                            $value,
                            $modelName,
                            $validationRule
                        );
                    }

                    if ($validationRule === 'unique' && !isset($this->fieldsArray['id'])) {
                        $this->addRule($field, [$this->formField, 'uniqueEntityCheck'], [$modelName, $field]);
                    } elseif ($validationRule !== 'unique') {
                        $this->addRule($field, $validationRule);
                    }
                }
            }
        }

        $this->setErrorMessages($errorMessages);
        return $this;
    }

    /**
     * Adds a rule to the rule set
     *
     * @param  $field
     * @param  $rule
     * @param mixed $params
     * @return $this
     */
    public function addRule($field, $rule, $params = null)
    {
        $length = count($this->rules);
        $this->rules[$length][$field] = [
            'rule' => $rule,
            'params' => $params
        ];
        return $this;
    }

    /**
     * Get all rules added by addRule
     *
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param  $rule
     * @param  $params
     * @param  $value
     * @param array $foundErrors
     * @return array|bool
     */
    private function checkRule($rule, $params, $value, $foundErrors = [])
    {
        $errorRule = false;

        // convert string to array
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        switch ($rule) {

            case 'callback':
                $obj = $params[0];
                $method = $params[1];
                $methodParams = null;
                if (isset($params[2])) {
                    $methodParams = $params[2];
                }
                if (is_object($obj)) {
                    if ($obj->$method($value, $methodParams) === false) {
                        $errorRule[][$rule] = $value;
                    }
                }
                break;

            case 'notEmpty':
                if (strlen($value) === 0) {
                    $errorRule[][$rule] = $value;
                }
                break;

            case 'minLength':
                if (strlen($value) < intval($params)) {
                    $errorRule[][$rule] = intval($params);
                }
                break;

            case 'maxLength':
                if (strlen($value) > intval($params)) {
                    $errorRule[][$rule] = intval($params);
                }
                break;

            case 'matches':
                if (isset($this->fieldsArray[$params]) && $this->fieldsArray[$params] !== $value) {
                    $errorRule[][$rule] = $params;
                } else {
                    $errorRule[][$rule] = $params;
                }
                break;

            case 'email':
                if ($this->email($value) === false) {
                    $errorRule[][$rule] = $value;
                }
                break;

            case 'emailRfc':
                if ($this->email($value, true) === false) {
                    $errorRule[][$rule] = $value;
                }
                break;

            case 'url':
                if ($this->url($value) === false) {
                    $errorRule[][$rule] = $value;
                }
                break;

            case 'ip':
                if ($this->ip($value) === false) {
                    $errorRule[][$rule] = $value;
                }
                break;

            case 'alphaNumeric':
                if ($this->alphaNumeric($value) === false) {
                    $errorRule[][$rule] = $value;
                }
                break;

            case 'numeric':
                if (is_numeric($value) === false) {
                    $errorRule[][$rule] = $value;
                }
                break;

            case 'decimal':
                if ($this->decimal($value) === false) {
                    $errorRule[][$rule] = $value;
                }
                break;

            case 'range':
                if (isset($params['min']) && isset($params['max'])) {
                    if ($this->range($value, $params['min'], $params['max']) === false) {
                        $errorRule[][$rule] = $params;
                    }
                }
                break;

            case 'regex':
                if ($this->regex($value, $params) === false) {
                    $errorRule[][$rule] = $value;
                }
                break;

            case 'date':
                if ($this->date($value) === false) {
                    $errorRule[][$rule] = $value;
                }
                break;

            case 'color':
                if ($this->color($value) === false) {
                    $errorRule[][$rule] = $value;
                }
                break;

            case 'digit':
                if ($this->digit($value) === false) {
                    $errorRule[][$rule] = $value;
                }
                break;

            case 'alphaDash':
                if ($this->alphaDash($value) === false) {
                    $errorRule[][$rule] = $value;
                }
                break;
        }

        if ($errorRule !== false) {
            return array_merge($foundErrors, $errorRule);
        }

        return false;
    }

    /**
     * @param $messages
     * @return $this
     */
    public function setErrorMessages($messages)
    {
        $this->errorMessages = $messages;
        return $this;
    }

    /**
     * Validates the user input
     *
     * @return array|bool
     */
    public function check()
    {
        $errors = [];

        foreach ($this->rules as $rules) {
            foreach ($rules as $field => $ruleConfig) {
                $rule = $ruleConfig['rule'];
                $ruleParams = $ruleConfig['params'];

                if (!isset($errors[$field])) {
                    $foundErrors = [];
                } else {
                    $foundErrors = $errors[$field];
                }

                if ($field == '*') {
                    foreach ($this->fieldsArray as $value) {
                        $errorRule = $this->checkRule($rule, $ruleParams, $value, $foundErrors);

                        if ($errorRule !== false) {
                            $errors[$field] = $errorRule;
                        }
                    }
                } else {
                    $value = isset($this->fieldsArray[$field]) ? $this->fieldsArray[$field] : '';
                    $errorRule = $this->checkRule($rule, $ruleParams, $value, $foundErrors);

                    if ($errorRule !== false) {
                        $errors[$field] = $errorRule;
                    }
                }
            }
        }

        $this->rules = [];
        $this->fieldsArray = [];

        if (count($errors) > 0) {
            $errors = $this->assignErrorMessagesToErrors($errors);
            return $errors;
        } else {
            return true;
        }
    }

    /**
     * @param   string  value
     * @param   string  regular expression to match (including delimiters)
     * @return  boolean
     */
    public function regex($value, $expression)
    {
        return (bool)preg_match($expression, (string)$value);
    }

    /**
     * @param  $errors
     * @return array
     */
    private function assignErrorMessagesToErrors($errors)
    {
        if (count($this->errorMessages) > 0) {
            foreach ($errors as $field => $fields) {
                foreach ($fields as $ruleType => $ruleValue) {
                    $rule = key($ruleValue);
                    $ruleValue = current($ruleValue);

                    if (!isset($this->errorMessages[$field][$rule])) {
                        continue;
                    } else {
                        $msg = $this->errorMessages[$field][$rule];
                    }

                    if (is_array($ruleValue)) {
                        foreach ($ruleValue as $param => $paramValue) {
                            $msg = preg_replace('/{' . $param . '}/im', $paramValue, $msg);
                        }
                    } else {
                        $msg = preg_replace('/{' . $rule . '}/im', $ruleValue, $msg);
                    }

                    if (isset($this->errorMessages[$field][$rule])) {
                        $errors[$field][$ruleType]['message'] = $msg;
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * @param   string   email address
     * @param   boolean  strict RFC compatibility
     * @return  boolean
     */
    public function email($email, $strict = false)
    {
        if ($strict === true) {
            $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
            $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
            $atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
            $pair = '\\x5c[\\x00-\\x7f]';

            $domain_literal = "\\x5b($dtext|$pair)*\\x5d";
            $quoted_string = "\\x22($qtext|$pair)*\\x22";
            $sub_domain = "($atom|$domain_literal)";
            $word = "($atom|$quoted_string)";
            $domain = "$sub_domain(\\x2e$sub_domain)*";
            $local_part = "$word(\\x2e$word)*";

            $expression = "/^$local_part\\x40$domain$/D";
        } else {
            $expression = '/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD';
        }

        return (bool)preg_match($expression, (string)$email);
    }

    /**
     * @param   string   email address
     * @return  boolean
     */
    public function emailDomain($email)
    {
        // Check if the email domain has a valid MX record
        return (bool)checkdnsrr(preg_replace('/^[^@]++@/', '', $email), 'MX');
    }


    /**
     * @param   string   URL
     * @return  boolean
     */
    public function url($url)
    {
        // Based on http://www.apps.ietf.org/rfc/rfc1738.html#sec-5
        if (!preg_match(
            '~^

                    # scheme
                    [-a-z0-9+.]++://

                    # username:password (optional)
                    (?:
                                [-a-z0-9$_.+!*\'(),;?&=%]++   # username
                            (?::[-a-z0-9$_.+!*\'(),;?&=%]++)? # password (optional)
                            @
                    )?

                    (?:
                            # ip address
                            \d{1,3}+(?:\.\d{1,3}+){3}+

                            | # or

                            # hostname (captured)
                            (
                                         (?!-)[-a-z0-9]{1,63}+(?<!-)
                                    (?:\.(?!-)[-a-z0-9]{1,63}+(?<!-)){0,126}+
                            )
                    )

                    # port (optional)
                    (?::\d{1,5}+)?

                    # path (optional)
                    (?:/.*)?

                    $~iDx',
            $url,
            $matches
        )
        ) {
            return false;
        }

        // We matched an IP address
        if (!isset($matches[1])) {
            return true;
        }

        // Check maximum length of the whole hostname
        // http://en.wikipedia.org/wiki/Domain_name#cite_note-0
        if (strlen($matches[1]) > 253) {
            return false;
        }

        // An extra check for the top level domain
        // It must start with a letter
        $tld = ltrim(substr($matches[1], (int)strrpos($matches[1], '.')), '.');
        return ctype_alpha($tld[0]);
    }

    /**
     * @param   string   IP address
     * @param   boolean  allow private IP networks
     * @return  boolean
     */
    public function ip($ip, $allowPrivate = true)
    {
        // Do not allow reserved addresses
        $flags = FILTER_FLAG_NO_RES_RANGE;

        if ($allowPrivate === false) {
            // Do not allow private or reserved addresses
            $flags = $flags | FILTER_FLAG_NO_PRIV_RANGE;
        }

        return (bool)filter_var($ip, FILTER_VALIDATE_IP, $flags);
    }

    /**
     * @param   string   date to check
     * @return  boolean
     */
    public function date($str)
    {
        return (strtotime($str) !== false);
    }

    /**
     * @param   string   input string
     * @param   boolean  trigger UTF-8 compatibility
     * @return  boolean
     */
    public function alpha($str, $utf8 = false)
    {
        $str = (string)$str;

        if ($utf8 === true) {
            return (bool)preg_match('/^\pL++$/uD', $str);
        } else {
            return ctype_alpha($str);
        }
    }

    /**
     * @param   string   input string
     * @param   boolean  trigger UTF-8 compatibility
     * @return  boolean
     */
    public function alphaNumeric($str, $utf8 = false)
    {
        if ($utf8 === true) {
            return (bool)preg_match('/^[\pL\pN]++$/uD', $str);
        } else {
            return ctype_alnum($str);
        }
    }

    /**
     * @param   string   input string
     * @param   boolean  trigger UTF-8 compatibility
     * @return  boolean
     */
    public function alphaDash($str, $utf8 = false)
    {
        if ($utf8 === true) {
            $regex = '/^[-\pL\pN_]++$/uD';
        } else {
            $regex = '/^[-a-z0-9_]++$/iD';
        }

        return (bool)preg_match($regex, $str);
    }


    /**
     * @param   string   input string
     * @param   boolean  trigger UTF-8 compatibility
     * @return  boolean
     */
    public function digit($str, $utf8 = false)
    {
        if ($utf8 === true) {
            return (bool)preg_match('/^\pN++$/uD', $str);
        } else {
            return (is_int($str) && $str >= 0) || ctype_digit($str);
        }
    }

    /**
     * @param   string   input string
     * @return  boolean
     */
    public function numeric($str)
    {
        list($decimal) = array_values(localeconv());
        return (bool)preg_match('/^-?+(?=.*[0-9])[0-9]*+' . preg_quote($decimal) . '?+[0-9]*+$/D', (string)$str);
    }

    /**
     * @param   string   number to check
     * @param   integer  minimum value
     * @param   integer  maximum value
     * @return  boolean
     */
    public function range($number, $min, $max)
    {
        return ($number >= $min && $number <= $max);
    }

    /**
     * @param   string   number to check
     * @param   integer  number of decimal places
     * @param   integer  number of digits
     * @return  boolean
     */
    public function decimal($str, $places = 2, $digits = null)
    {
        if ($digits > 0) {
            // Specific number of digits
            $digits = '{' . (int)$digits . '}';
        } else {
            // Any number of digits
            $digits = '+';
        }

        // Get the decimal point for the current locale
        list($decimal) = array_values(localeconv());

        return (bool)preg_match('/^[0-9]' . $digits . preg_quote($decimal) . '[0-9]{' . (int)$places . '}$/D', $str);
    }

    /**
     * @param   string   input string
     * @return  boolean
     */
    public function color($str)
    {
        return (bool)preg_match('/^#?+[0-9a-f]{3}(?:[0-9a-f]{3})?$/iD', $str);
    }
}
