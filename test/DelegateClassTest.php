<?php

use Delegate\DelegateClass;

/**
 * Simple test models
 */
class TestModel1 {
    public function hello(){
        return "hello";
    }

    public function chomp($required, $optional = true){

    }
}

class TestModel2 {
    public function hello($name){
        return "hello " . $name;
    }

    public function chomp(TestModel1 $model = null){
        if(null === $model) return null;
        return $model->chomp("required");
    }
}


/**
 * Test this mother...
 */
class DelegateClassTest extends PHPUnit_Framework_TestCase {

    public function setUp(){
        $this->mock = $this->getMock('stdClass', array('getName'));
    }

    /**
     * @test
     */
    public function methodCallInvokesDelegateMethod(){

        $mock = $this->getMock('stdClass', array('getName'));

        $mock->expects($this->once())
             ->method('getName')
             ->will($this->returnValue('hello world'));

        $test = new DelegateClass($mock);

        $this->assertEquals('hello world', $test->getName());

    }

    /**
     * @test
     */
    public function delegateClassPicksTheCorrectDelegateBasedOnMethodSignature(){
        $test = new DelegateClass(new TestModel1(), new TestModel2());

        $this->assertEquals("hello world", $test->hello("world"));
        $this->assertEquals("hello", $test->hello());

        // object binding makes no difference to method invocation so long as signatures differ.
        $test = new DelegateClass(new TestModel2(), new TestModel1());

        $this->assertEquals("hello world", $test->hello("world"));
        $this->assertEquals("hello", $test->hello());


    }

    /**
     * @test
     */
    public function badMethodCallsThrowException(){

        $this->setExpectedException('BadMethodCallException');

        $test = new DelegateClass();

        $test->hello();

    }

    /**
     * @test
     */
    public function bindingAndUnbindingWorkAccordingly(){

        $test = new DelegateClass();

        $model = new TestModel1();
        $test->bind($model);

        $this->assertEquals("hello", $test->hello());

        $test->unbind($model);

        try {
            $test->hello();
        } catch(Exception $e){
            $this->assertThat($e, $this->isInstanceOf('BadMethodCallException'));
        }

    }

    /**
     * @test
     */
    public function methodsWithOptionalParametersAreInvokedCorrectly(){

        $mock1 = $this->getMock('TestModel1');
        $mock1->expects($this->at(0))
             ->method('chomp')
             ->with("required");
        $mock1->expects($this->at(1))
             ->method('chomp')
             ->with("required", false);

        // Execute
        $test = new DelegateClass($mock1);

        $test->chomp("required");

        $test->chomp("required", false);

    }

    /**
     * @test
     */
    public function objectMethodsWithIdentialSignaturesAreChosenByFirstComeFirstServed(){

        $mock1 = $this->getMock('TestModel1');
        $mock1->expects($this->at(0))
             ->method('chomp')
             ->with("required");

        $mock2 = $this->getMock('TestModel1');
        $mock2->expects($this->never())
              ->method('chomp');


        $test = new DelegateClass($mock1, $mock2);

        $test->chomp("required");

    }

    /**
     * @test
     */
    public function methodDelegationWorksWhenOptionalParameterIsObject(){

        $mock1 = $this->getMock('TestModel2');
        $mock1->expects($this->at(0))
             ->method('chomp');

        $test = new DelegateClass($mock1);

        $test->chomp();

    }

}