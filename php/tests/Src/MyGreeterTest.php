<?php

use PHPUnit\Framework\TestCase;
use Src\MyGreeter;

class MyGreeterTest extends TestCase
{
    private MyGreeter $greeter;

    public function setUp(): void
    {
        $this->greeter = new MyGreeter();
    }

    public function test_init()
    {
        // 这个方法是不是提前要把$this->greeter = new MyGreeter(); 这行代码加上
        // 或者调用这个方法前需要把setUp()先调用，生成实例化对象，会影响结果
        $this->assertInstanceOf(
            MyGreeter::class,
            $this->greeter
        );
    }

    public function test_greeting()
    {
        // 这个方法是不是提前要把$this->greeter = new MyGreeter(); 这行代码加上
        // 或者调用这个方法前需要把setUp()先调用，生成实例化对象，不然直接调用方法会报错的
        $this->assertTrue(
            strlen($this->greeter->greeting()) > 0
        );
    }
}
