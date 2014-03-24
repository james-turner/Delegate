DelegateClass
=============

PHP Method weaving! Mix in the same method with several different signatures.

## Understanding

Basically the DelegateClass allows you to proxy method calls to the contained
objects within. It verifies method signature so will match up the first method
that will work with the supplied arguments.

## Usage (Basic)

### Example 1 (extending)
```php
<?php
class Model {
    public function getName(){
        return "name";
    }
}
class Test extends DelegateClass {}

$test = new Test(new Model());

echo $test->getName();
```

### Example 2 (multiple methods)
```php
<?php
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
```
## Usage (Advanced)

### Example 1 (runtime binding)
```php
<?php
class Model {
    public function getName(){
        return "name";
    }
}
class Test extends DelegateClass {}

$model = new Model();
$test = new Test();

// Runtime binding
$test->bind($model);

echo $test->getName();

// Runtime unbinding!
$test->unbind($model);

echo $test->getName(); // <- results in BadMethodCallException!
```

### Example 2 (method invocation by string)
```php
<?php
class Model {
    public function say($first, $second, $third = null){
        return "hello $first, $second, $third";
    }
}
class Test extends DelegateClass {}

// Execution
$test = new Test(new Model());

echo $test->send("say", 1, 2);
echo $test->send("say", 1, 2, 3);
```

### Example 3 (responding)
```php
<?php
class Model {
    public function say($first, $second, $third = null){
        return "hello $first, $second, $third";
    }
}
class Test extends DelegateClass {}

// Execution
$test = new Test(new Model());

$methodName = "say";
if($test->respond_to($methodName)){  // => true
    echo $test->send($methodName, 1, 2, 3);
};
```
