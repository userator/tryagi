<?php

namespace TryAGI;

/**
 * @author userator
 */
class ResultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Result $object
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Result();
    }

    /**
     * @covers TryAGI\Result::fromArgs
     */
    public function testFromArgs()
    {
        $this->assertInstanceOf(Result::class, $this->object->fromArgs(200, 1));
    }

    /**
     * @covers TryAGI\Result::fromArray
     */
    public function testFromArray()
    {
        $this->assertInstanceOf(Result::class, $this->object->fromArray(['code' => 200, 'result' => 1]));
    }

    /**
     * @covers TryAGI\Result::isCodeSuccess
     */
    public function testSuccessIsCodeSuccess()
    {
        $this->object->setCode(Result::CODE_SUCCESS);
        $this->assertTrue($this->object->isCodeSuccess());
    }

    /**
     * @covers TryAGI\Result::isCodeSuccess
     */
    public function testFailureIsCodeSuccess()
    {
        $this->object->setCode(Result::CODE_FAILURE);
        $this->assertFalse($this->object->isCodeSuccess());
    }

    /**
     * @covers TryAGI\Result::isCodeFailure
     */
    public function testSuccessIsCodeFailure()
    {
        $this->object->setCode(Result::CODE_FAILURE);
        $this->assertTrue($this->object->isCodeFailure());
    }

    /**
     * @covers TryAGI\Result::isCodeFailure
     */
    public function testFailureIsCodeFailure()
    {
        $this->object->setCode(Result::CODE_SUCCESS);
        $this->assertFalse($this->object->isCodeFailure());
    }

}
