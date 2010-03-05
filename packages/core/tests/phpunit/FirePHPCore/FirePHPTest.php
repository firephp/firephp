<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'TestHelper.php';
 

class FirePHPCore_FirePHPTest extends PHPUnit_Framework_TestCase
{
    /**
     * @issue http://code.google.com/p/firephp/issues/detail?id=117
     */
    public function testDumpArguments()
    {
        $firephp = new FirePHP_Test_Class();

        $firephp->dump("key", "value");
        $headers = $firephp->_getHeaders();
        $this->assertEquals('15|{"key":"value"}|', $headers['X-Wf-1-2-1-1']);
        $firephp->_clearHeaders();

        $caught = false;
        try {
            $firephp->dump(array(), "value");
        } catch(Exception $e) {
            // Key passed to dump() is not a string
            $caught = true;
        }
        if(!$caught) $this->fail('No exception thrown');

        $caught = false;
        try {
            $firephp->dump("key \n\r value", "value");
        } catch(Exception $e) {
            // Key passed to dump() contains invalid characters [a-zA-Z0-9-_\.:]
            $caught = true;
        }
        if(!$caught) $this->fail('No exception thrown');

        $caught = false;
        try {
            $firephp->dump("keykeykeykkeykeykeykkeykeykeykkeykeykeykkeykeykeykkeykeykeykkeykeykeykkeykeykeykkeykeykeykkeykeykeyk1", "value");
        } catch(Exception $e) {
            // Key passed to dump() is longer than 100 characters
            $caught = true;
        }
        if(!$caught) $this->fail('No exception thrown');
    }
    
    /**
     * @issue http://code.google.com/p/firephp/issues/detail?id=123
     */
    public function testRegisterErrorHandler()
    {
        $firephp = new FirePHP_Test_Class();
        $firephp->setOption("maxObjectDepth", 1);
        $firephp->setOption("maxArrayDepth", 1);

        $firephp->registerErrorHandler();
        trigger_error("Hello World");
        $headers = $firephp->_getHeaders();
        if(!isset($headers["X-Wf-1-1-1-1"])) {
            $this->fail("Error not in headers");
        }
    }

    /**
     * @issue http://code.google.com/p/firephp/issues/detail?id=122
     */
    public function testFirePHPClassInstanceLogging()
    {
        $firephp = new FirePHP_Test_Class();

        $firephp->log($firephp);
        $headers = $firephp->_getHeaders();
        if(!preg_match_all('/"protected:objectStack":"\\*\\* Excluded by Filter \\*\\*"/', $headers['X-Wf-1-1-1-1'], $m)) {
            $this->fail("objectStack member contains value");
        }
        if(!preg_match_all('/"protected:static:instance":"\\*\\* Excluded by Filter \\*\\*"/', $headers['X-Wf-1-1-1-1'], $m)) {
            $this->fail("instance member should not be logged");
        }
        if(!preg_match_all('/"undeclared:json_objectStack":"\\*\\* Excluded by Filter \\*\\*"/', $headers['X-Wf-1-1-1-1'], $m)) {
            $this->fail("json_objectStack member should not be logged");
        }
    }

    public function testOptions()
    {
        $firephp = new FirePHP_Test_Class();
        
        // defaults
        $this->assertEquals(10, $firephp->getOption("maxObjectDepth"));
        $this->assertEquals(20, $firephp->getOption("maxArrayDepth"));
        $this->assertEquals(true, $firephp->getOption("useNativeJsonEncode"));
        $this->assertEquals(true, $firephp->getOption("includeLineNumbers"));
        
        // modify
        $firephp->setOption("maxObjectDepth", 1);
        $this->assertEquals(1, $firephp->getOption("maxObjectDepth"));
        
        // invalid
        $caught = false;
        try {
            $firephp->setOption("invalidName", 1);
        } catch(Exception $e) {
            $caught = true;
        }
        if(!$caught) $this->fail('No exception thrown');

        $caught = false;
        try {
            $firephp->getOption("invalidName");
        } catch(Exception $e) {
            $caught = true;
        }
        if(!$caught) $this->fail('No exception thrown');
    }
    
    public function testDeprecatedMethods()
    {
        $firephp = new FirePHP_Test_Class();

        $caught = false;
        try {
            $firephp->setProcessorUrl('URL');
        } catch(Exception $e) {
            $caught = true;
            $this->assertEquals(E_USER_DEPRECATED, $e->getCode());
            $this->assertEquals('The FirePHP::setProcessorUrl() method is no longer supported', $e->getMessage());
        }
        if(!$caught) $this->fail('No deprecation error thrown');

        $caught = false;
        try {
            $firephp->setRendererUrl('URL');
        } catch(Exception $e) {
            $caught = true;
            $this->assertEquals(E_USER_DEPRECATED, $e->getCode());
            $this->assertEquals('The FirePHP::setRendererUrl() method is no longer supported', $e->getMessage());
        }
        if(!$caught) $this->fail('No deprecation error thrown');
    }
      
}
