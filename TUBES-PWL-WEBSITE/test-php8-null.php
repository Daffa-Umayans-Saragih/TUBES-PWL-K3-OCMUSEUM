<?php
class Test {
    public $prop;
}
$t = new Test();
$t->prop = null;
try {
    echo $t->prop->email ?? 'Not Provided';
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage();
}
