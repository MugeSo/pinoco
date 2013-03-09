<?php
/**
 * Pinoco: makes existing static web site dynamic transparently.
 * Copyright 2010-2012, Hisateru Tanaka <tanakahisateru@gmail.com>
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP Version 5
 *
 * @author     Hisateru Tanaka <tanakahisateru@gmail.com>
 * @copyright  Copyright 2010-2012, Hisateru Tanaka <tanakahisateru@gmail.com>
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @package    Pinoco
 */

/**
 * Procedural validation utility.
 *
 * <code>
 * $validator = new Pinoco_Validator($data);
 * $validator->check('name')->is('not-empty')->is('max-length 255');
 * $validator->check('age')->is('not-empty')->is('integer')
 *                         ->is('>= 21', 'Adult only.');
 * if ($validator->valid) {
 *     echo "OK";
 * }
 * else {
 *     foreach ($validator->errors as $field=>$context) {
 *         echo $field . ":" . $context->message . "\n";
 *     }
 * }
 * </code>
 *
 * Builtin tests:
 *   pass, fail, empty, not-empty, max-length, min-length, in a,b,c, not-in a,b,c,
 *   numeric, integer, alpha, alpha-numeric, == n, != n, > n, >= n, < n,  <= n,
 *   match /regexp/, not-match /regexp/, email, url
 *
 * @package Pinoco
 * @property-read Pinoco_Vars $result All context objects.
 * @property-read Pinoco_Vars $errors Invalid context objects only.
 * @property-read Pinoco_Vars $values Validated values unwrapped.
 * @property-read boolean $valid   Totally valid or not.
 * @property-read boolean $invalid Totally invalid or not.
 */
class Pinoco_Validator extends Pinoco_DynamicVars
{
    protected $_tests;
    protected $_filters;
    protected $_messages;

    private $_target;
    private $_result;
    private $_errors;
    private $_values;

    private $_dispatcher;

    /**
     * Constructor
     *
     * @param string $target
     * @param array $messages
     */
    public function __construct($target, $messages=array())
    {
        parent::__construct();

        $this->_dispatcher = new Pinoco_ValidatorDispatcher();
        $this->_setupBuiltinTests();

        $this->_filters = array();
        $this->_setupBuiltinFilters();

        $this->_messages = array();
        $this->overrideErrorMessages($messages);

        $this->_target = $target;
        $this->_result = new Pinoco_Vars();
        $this->_errors = null;
        $this->_values = null;
    }

    private function _setupBuiltinTests()
    {
        $this->_dispatcher->setupBuiltinTests();
    }

    private function _setupBuiltinFilters()
    {
        // builtin filters
        $this->defineFilter('trim', array($this, '_filterTrim'));
        $this->defineFilter('ltrim', array($this, '_filterLtrim'));
        $this->defineFilter('rtrim', array($this, '_filterRtrim'));
    }

    /**
     * Defines custom test.
     *
     * @param string $testName
     * @param callable $callback
     * @param string $message
     * @param boolean $complex
     * @return void
     */
    public function defineValidityTest($testName, $callback, $message, $complex=false)
    {
        $this->_dispatcher->defineValidityTest($testName, $callback, $message, $complex);
    }

    /**
     * Defines custom filter.
     *
     * @param string $filterName
     * @param callable $callback
     * @param boolean $complex
     * @return void
     */
    public function defineFilter($filterName, $callback, $complex=false)
    {
        $this->_filters[$filterName] = array(
            'callback' => $callback,
            'complex' => $complex,
        );
    }

    /**
     * Overrides error messages.
     *
     * @param array $messages
     * @return void
     */
    public function overrideErrorMessages($messages)
    {
        foreach ($messages as $test=>$msg) {
            $this->_messages[$test] = $msg;
        }
    }

    /**
     * Resolve error message by test name.
     *
     * @param string $testName
     * @return string
     */
    public function getMessageFor($testName)
    {
        if (isset($this->_messages[$testName])) {
            return $this->_messages[$testName];
        }
        elseif ($this->_dispatcher->definedValidityTest($testName)) {
            return $this->_dispatcher->getValidityTestMessage($testName);
        }
        else {
            return 'not registered';
        }
    }

    /**
     * Check existence and fetch value at the same time.
     * (called by self and validation context)
     *
     * @param string $name
     * @return array
     */
    public function fetchExistenceAndValue($name)
    {
        //type check
        if ($this->_target instanceof Pinoco_Vars) {
            $exists = $this->_target->has($name);
            $value = $this->_target->get($name);
        }
        elseif ($this->_target instanceof Pinoco_List) {
            $exists = intval($name) < $this->_target->count();
            $value = $exists ? $this->_target[$name] : null;
        }
        elseif (is_array($this->_target)) {
            $exists = isset($this->_target[$name]);
            $value = $exists ? $this->_target[$name] : null;
        }
        elseif (is_object($this->_target)) {
            $exists = isset($this->_target->$name);
            $value = $exists ? $this->_target->$name : null;
        }
        else {
            $exists = false;
            $value = null;
        }
        return array($exists, $value);
    }

    private function prepareCallback($methods, $methodName, $param)
    {
        $this->_errors = null;
        $this->_values = null;
        if (isset($methods[$methodName])) {
            return array(
                $methods[$methodName]['callback'],
                $methods[$methodName]['complex'],
                array($param)
            );
        }
        elseif (is_callable($methodName)) {
            return array(
                $methodName,
                false,
                $param ? explode(' ', $param) : array()
            );
        }
        else {
            // test method not registered
            return array(
                null,
                false,
                array()
            );
        }
    }

    private function prepareValue($field, $filtered, $filteredValue)
    {
        // fetch
        if ($filtered) {
            return array(
                true,
                $filteredValue
            );
        }
        else {
            return $this->fetchExistenceAndValue($field);
        }
    }

    private function callMethod($callback, $complex, $params, $exists, $value)
    {
        if ($complex) {
            // complex test: full information presented
            //               and should be checked if empty or not
            $args = array($exists, $value);
        }
        else {
            // simple test: empty always success
            if (!$exists || empty($value) && !($value === "0" || $value === 0 || $value === false || $value === array())) {
                // validation must be passed and value is as is.
                return array(true, $value);
            }
            $args = array($value);
        }
        foreach ($params as $p) {
            $args[] = $p;
        }
        return call_user_func_array($callback, $args);
    }

    /**
     * Executes validation test.
     * (called by validation context)
     *
     * @param string $field
     * @param boolean $filtered
     * @param mixed $filteredValue
     * @param string $testName
     * @param string $param
     * @return array
     */
    public function execValidityTest($field, $filtered, $filteredValue, $testName, $param)
    {
        $this->_errors = null;
        $this->_values = null;
        list($exists, $value) = $this->prepareValue($field, $filtered, $filteredValue);
        return $this->_dispatcher->execValidityTest($exists, $value, $testName, $param);
    }

    /**
     * Executes validation test to array with logical AND.
     * (called by validation context)
     *
     * @param string $field
     * @param boolean $filtered
     * @param mixed $filteredValue
     * @param string $testName
     * @param string $param
     * @return array
     */
    public function execValidityTestAll($field, $filtered, $filteredValue, $testName, $param)
    {
        $this->_errors = null;
        $this->_values = null;
        list($exists, $value) = $this->prepareValue($field, $filtered, $filteredValue);
        return $this->_dispatcher->execValidityTestAll($exists, $value, $testName, $param);
    }

    /**
     * Executes validation test to array with logical OR.
     * (called by validation context)
     *
     * @param string $field
     * @param boolean $filtered
     * @param mixed $filteredValue
     * @param string $testName
     * @param string $param
     * @return array
     */
    public function execValidityTestAny($field, $filtered, $filteredValue, $testName, $param)
    {
        $this->_errors = null;
        $this->_values = null;
        list($exists, $value) = $this->prepareValue($field, $filtered, $filteredValue);
        return $this->_dispatcher->execValidityTestAny($exists, $value, $testName, $param);
    }

    /**
     * Executes filter.
     * (called by validation context)
     *
     * @param string $field
     * @param boolean $filtered
     * @param mixed $filteredValue
     * @param mixed $filterName
     * @param string $param
     * @return array
     */
    public function execFilter($field, $filtered, $filteredValue, $filterName, $param)
    {
        list($callback, $complex, $params) = $this->prepareCallback($this->_filters, $filterName, $param);
        list($exists, $value) = $this->prepareValue($field, $filtered, $filteredValue);
        if ($callback == null) {
            return array(true, null);
        }
        else {
            return array(
                true,
                $this->callMethod($callback, $complex, $params, $exists, $value)
            );
        }
    }

    /**
     * Executes filter for each elements.
     * (called by validation context)
     *
     * @param string $field
     * @param boolean $filtered
     * @param mixed $filteredValue
     * @param mixed $filterName
     * @param string $param
     * @return array
     */
    public function execFilterMap($field, $filtered, $filteredValue, $filterName, $param)
    {
        list($callback, $complex, $params) = $this->prepareCallback($this->_filters, $filterName, $param);
        list($exists, $value) = $this->prepareValue($field, $filtered, $filteredValue);
        if ($callback == null || !(is_array($value) || $value instanceof Traversable)) {
            return array(true, null);
        }
        else {
            if ($value instanceof Pinoco_List) {
                $result = new Pinoco_List();
                foreach ($value as $v) {
                    $result->push($this->callMethod($callback, $complex, $params, $exists, $v));
                }
            }
            else {
                $result = array();
                foreach ($value as $v) {
                    $result[] = $this->callMethod($callback, $complex, $params, $exists, $v);
                }
            }
            return array(true, $result);
        }
    }

    /**
     * Returns independent validation context.
     *
     * @param string $name
     * @param string|bool $label
     * @return Pinoco_ValidatorContext
     */
    public function contextFor($name, $label=false)
    {
        return new Pinoco_ValidatorContext($this, $name, $label);
    }

    /**
     * Starts named property check.
     *
     * @param string $name
     * @param string|bool $label
     * @return Pinoco_ValidatorContext
     */
    public function check($name, $label=false)
    {
        $this->_errors = null;
        $this->_values = null;
        if (!$this->_result->has($name)) {
            $this->_result->set($name, $this->contextFor($name, $label));
        }
        return $this->_result->get($name);
    }

    /**
     * Clears previous result and restarts named property check.
     *
     * @param string $name
     * @param string|bool $label
     * @return Pinoco_ValidatorContext
     */
    public function recheck($name, $label=false)
    {
        $this->_errors = null;
        $this->_values = null;
        $this->_result->set($name, $this->contextFor($name, $label));
        return $this->_result->get($name);
    }

    /**
     * Clears previous result.
     *
     * @param string $name
     * @return void
     */
    public function uncheck($name)
    {
        $this->_errors = null;
        $this->_values = null;
        if ($this->_result->has($name)) {
            $this->_result->remove($name);
        }
    }

    /**
     * Exports test all results.
     *
     * @return Pinoco_Vars
     */
    public function get_result()
    {
        return $this->_result;
    }

    /**
     * Exports test results only failed.
     *
     * @return Pinoco_Vars
     */
    public function get_errors()
    {
        if ($this->_errors === null) {
            $this->_errors = new Pinoco_Vars();
            foreach ($this->_result->keys() as $field) {
                $result = $this->_result->get($field);
                if ($result->invalid) {
                    $this->_errors->set($field, $result);
                }
            }
        }
        return $this->_errors;
    }

    /**
     * Exports test results only failed.
     *
     * @return Pinoco_Vars
     */
    public function get_values()
    {
        if ($this->_values === null) {
            $this->_values = new Pinoco_Vars();
            foreach ($this->_result->keys() as $field) {
                $result = $this->_result->get($field);
                $this->_values->set($field, $result->value);
            }
        }
        return $this->_values;
    }

    /**
     * Returns which all tests succeeded or not.
     *
     * @return boolean
     */
    public function get_valid()
    {
        return ($this->get_errors()->count() == 0);
    }

    /**
     * Returns which validator has one or more failed tests.
     *
     * @return boolean
     */
    public function get_invalid()
    {
        return !$this->get_valid();
    }

    /**
     * Returns all succeeded checking results to be used in form's initial state.
     * If you fetch a field not given by $values, you will get a passed checking
     * context instead.
     *
     * @param array $values
     * @return Pinoco_Vars
     */
    public static function emptyResult($values=array())
    {
        $validator = new self($values);
        foreach ($values as $name=>$value) {
            $validator->check($name)->is('pass');
        }
        $result = $validator->result;
        $result->setDefault($validator->contextFor('any')->is('pass'));
        $result->setLoose(true);
        return $result;
    }

    /////////////////////////////////////////////////////////////////////
    // builtin filters
    private function _filterTrim($value)
    {
        return trim($value);
    }
    private function _filterLtrim($value)
    {
        return ltrim($value);
    }
    private function _filterRtrim($value)
    {
        return rtrim($value);
    }
}

