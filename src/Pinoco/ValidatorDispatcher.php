<?php
class Pinoco_ValidatorDispatcher
{
    private $_tests;

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
        $this->_tests[$testName] = array(
            'callback' => $callback,
            'message' => $message,
            'complex' => $complex,
        );
    }

    public function definedValidityTest($testName)
    {
        return isset($this->_tests[$testName]);
    }

    public function getValidityTestMessage($testName)
    {
        return $this->definedValidityTest($testName) ? $this->_tests[$testName]['message'] : null;
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
    public function execValidityTest($exists, $value, $testName, $param)
    {
        list($callback, $complex, $params) = $this->prepareCallback($testName, $param);
        if ($callback == null) {
            return array(false, $value);
        }
        else {
            return array(
                $this->callMethod($callback, $complex, $params, $exists, $value),
                $value
            );
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
    
    private function prepareCallback($methodName, $param)
    {
        $methods = $this->_tests;
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
    public function execValidityTestAll($exists, $value, $testName, $param)
    {
        list($callback, $complex, $params) = $this->prepareCallback($testName, $param);
        if ($callback == null || !(is_array($value) || $value instanceof Traversable)) {
            return array(false, $value);
        }
        else {
            foreach ($value as $v) {
                $result = $this->callMethod($callback, $complex, $params, $exists, $v);
                if (!$result) {
                    return array(false, $value);
                }
            }
            return array(true, $value);
        }
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
    public function execValidityTestAny($exists, $value, $testName, $param)
    {
        list($callback, $complex, $params) = $this->prepareCallback($testName, $param);
        if ($callback == null || !(is_array($value) || $value instanceof Traversable)) {
            return array(false, $value);
        }
        else {
            foreach ($value as $v) {
                $result = $this->callMethod($callback, $complex, $params, $exists, $v);
                if ($result) {
                    return array(true, $value);
                }
            }
            return array(false, $value);
        }
    }

    public function setupBuiltinTests()
    {
        // builtin testers
        $this->defineValidityTest('pass', array($this, '_testPassComplex'),
            "Valid.", true);
        $this->defineValidityTest('fail', array($this, '_testFailComplex'),
            "Invalid.", true);
        $this->defineValidityTest('empty', array($this, '_testEmptyComplex'),
            "Leave as empty.", true);
        $this->defineValidityTest('not-empty', array($this, '_testNotEmptyComplex'),
            "Required.", true);
        $this->defineValidityTest('max-length', array($this, '_testMaxLength'),
            "In {param} letters.");
        $this->defineValidityTest('min-length', array($this, '_testMinLength'),
            "At least {param} letters.");
        $this->defineValidityTest('in', array($this, '_testIn'),
            "Choose in {param}.");
        $this->defineValidityTest('not-in', array($this, '_testNotIn'),
            "Choose else of {param}.");
        $this->defineValidityTest('numeric', array($this, '_testNumeric'),
            "By number.");
        $this->defineValidityTest('integer', array($this, '_testInteger'),
            "By integer number.");
        $this->defineValidityTest('alpha', array($this, '_testAlpha'),
            "Alphabet only.");
        $this->defineValidityTest('alpha-numeric', array($this, '_testAlphaNumeric'),
            "Alphabet or number.");
        $this->defineValidityTest('array', array($this, '_testArray'),
            "By Array.");
        $this->defineValidityTest('==', array($this, '_testEqual'),
            "Shuld equal to {param}.");
        $this->defineValidityTest('!=', array($this, '_testNotEqual'),
            "Should not equal to {param}.");
        $this->defineValidityTest('>', array($this, '_testGreaterThan'),
            "Greater than {param}.");
        $this->defineValidityTest('>=', array($this, '_testGreaterThanOrEqual'),
            "Greater than or equals to {param}.");
        $this->defineValidityTest('<', array($this, '_testLessorThan'),
            "Lessor than {param}.");
        $this->defineValidityTest('<=', array($this, '_testLessorThanOrEqual'),
            "Lessor than or equals to {param}.");
        $this->defineValidityTest('match', array($this, '_testMatch'),
            "Invalid pattern.");
        $this->defineValidityTest('not-match', array($this, '_testNotMatch'),
            "Not allowed pattern.");
        $this->defineValidityTest('email', array($this, '_testEmail'),
            "Email only.");
        $this->defineValidityTest('url', array($this, '_testUrl'),
            "URL only.");
    }

    /////////////////////////////////////////////////////////////////////
    // builtin tests
    private function _testPassComplex($exists, $value)
    {
        return true;
    }
    private function _testFailComplex($exists, $value)
    {
        return false;
    }
    private function _testEmptyComplex($exists, $value)
    {
        if (!$exists || $value === null) { return true; }
        if ($value === "0" || $value === 0 || $value === false || $value === array()) {
            return false;
        }
        return empty($value);
    }
    private function _testNotEmptyComplex($exists, $value)
    {
        return !$this->_testEmptyComplex($exists, $value);
    }

    private function _testMaxLength($value, $param=0)
    {
        return strlen(strval($value)) <= $param;
    }
    private function _testMinLength($value, $param=0)
    {
        return strlen(strval($value)) >= $param;
    }
    private function _testIn($value, $param='')
    {
        $as = explode(',', $param);
        foreach ($as as $a) {
            if ($value == trim($a)) { return true; }
        }
        return false;
    }
    private function _testNotIn($value, $param='')
    {
        return !$this->_testIn($value, $param);
    }
    private function _testNumeric($value)
    {
        return is_numeric($value);
    }
    private function _testInteger($value)
    {
        return is_integer($value);
    }
    private function _testAlpha($value)
    {
        return ctype_alpha($value);
    }
    private function _testAlphaNumeric($value)
    {
        return ctype_alnum($value);
    }
    private function _testArray($value)
    {
        return is_array($value) || $value instanceof Traversable;
    }
    private function _testEqual($value, $param=null)
    {
        return $value == $param;
    }
    private function _testNotEqual($value, $param=null)
    {
        return !$this->_testEqual($value, $param);
    }
    private function _testGreaterThan($value, $param=0)
    {
        return $value > $param;
    }
    private function _testGreaterThanOrEqual($value, $param=0)
    {
        return $value >= $param;
    }
    private function _testLessorThan($value, $param=0)
    {
        return $value < $param;
    }
    private function _testLessorThanOrEqual($value, $param=0)
    {
        return $value <= $param;
    }
    private function _testMatch($value, $param='/^$/')
    {
        return preg_match($param, $value);
    }
    private function _testNotMatch($value, $param='/^$/')
    {
        return !$this->_testMatch($value, $param);
    }
    private function _testEmail($value)
    {
        return preg_match('/@[A-Z0-9][A-Z0-9_-]*(\.[A-Z0-9][A-Z0-9_-]*)*$/i', $value);
    }
    private function _testUrl($value)
    {
        return preg_match('/^[A-Z]+:\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)*):?(\d+)?\/?/i', $value);
    }
}

