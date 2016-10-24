<?php


    $suite  = new PHPUnit_Framework_TestSuite("TestTest");
    
    
    class Foo {
        
        function Bar()
        {
            return null;
        }
    }
    
    class TestTest extends PHPUnit_Framework_TestCase 
    {
        private $test_max_prod;
        private $initial;
        
        public function setUp() {
            $this->_foo = $this->getMockBuilder('Foo')
                ->disableOriginalConstructor()
                ->getMock();
        
            $this->_foo->expects($this->any())
                ->method('bar')
                ->will($this->returnValue('bar'));
        
            var_dump($this->_foo->bar());
        }
        
        function tearDown() {
        
        }
        
        function testTest(){
            $this->assertTrue(true);
        }
       
        
        
    }


