DelegateClass
=============

PHP Method weaving! Mix in the same method with several different signatures.

## Understanding

Basically the DelegateClass allows you to proxy method calls to the contained
objects within. It verifies method signature so will match up the first method
that will work with the supplied arguments

## Usage (Basic)

### Example 1 (extending)

    class Model {
        public function getName(){
            return "name";
        }
    }
    class Test extends DelegateClass {}

    $test = new Test(new Model());

    echo $test->getName();


### Example 2 (multiple methods)

    class Model1 {
        public function say(){
            return "hello";
        }
    }

    class Model2 {
        public function say($what){
            return "hello " . $what;
        }
    }

    class Test extends DelegateClass {}

    $test = new Test(new Model1(), new Model2());
    echo $test->say();
    echo $test->say("world");



