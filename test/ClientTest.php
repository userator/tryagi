<?php

namespace TryAGI;

/**
 * @author userator
 */
class ClientTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @var resource
     */
    protected $stdin;

    /**
     * @var resource
     */
    protected $stdout;

    /**
     * @var Client
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->filename = tempnam(sys_get_temp_dir(), rand());

        // streams
        $this->stdin = fopen($this->filename, 'rb');
        $this->stdout = fopen($this->filename, 'ab');

        // client
        $this->object = new Client($this->stdin, $this->stdout, new \Psr\Log\NullLogger());
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unlink($this->filename);
    }

    protected function writeChannelVariablesMessage($stdout)
    {
        $message = 'agi_request: /path/to/agi.php' . "\n";
        $message .= 'agi_channel: XXX/xxx-xxxxxxxx' . "\n";
        $message .= 'agi_language: en' . "\n";
        $message .= 'agi_type: XXX' . "\n";
        $message .= 'agi_uniqueid: 000000000.00' . "\n";
        $message .= 'agi_version: 00.0.0' . "\n";
        $message .= 'agi_callerid: 000' . "\n";
        $message .= 'agi_calleridname: Firstname Lastname' . "\n";
        $message .= 'agi_callingpres: 0' . "\n";
        $message .= 'agi_callingani2: 0' . "\n";
        $message .= 'agi_callington: 0' . "\n";
        $message .= 'agi_callingtns: 0' . "\n";
        $message .= 'agi_dnid: 00000' . "\n";
        $message .= 'agi_rdnis: unknown' . "\n";
        $message .= 'agi_context: context' . "\n";
        $message .= 'agi_extension: 00000' . "\n";
        $message .= 'agi_priority: 0' . "\n";
        $message .= 'agi_enhanced: 0.0' . "\n";
        $message .= 'agi_accountcode: ' . "\n";
        $message .= 'agi_threadid: 00000000000000' . "\n";
        $message .= 'agi_arg_1: xxxxx' . "\n";
        $message .= 'agi_arg_2: --xxxxxx=00' . "\n";
        $message .= '' . "\n";

        fwrite($stdout, $message);
    }

    protected function writeSuccessSingleResultMessageWithData($stdout)
    {
        $message = '200 result=1 foo=bar' . "\n";

        fwrite($stdout, $message);
    }

    protected function writeSuccessSingleResultMessageWithoutData($stdout)
    {
        $message = '200 result=1 ' . "\n";

        fwrite($stdout, $message);
    }

    protected function writeSuccessMultiResultMessage($stdout)
    {
        $message = '200-data-part-1&' . "\n";
        $message .= '200-data-part-2&' . "\n";
        $message .= '200 result=1 end' . "\n";

        fwrite($stdout, $message);
    }

    protected function writeFailureSingleResultMessageWithData($stdout)
    {
        $message = '500 result=-1 foo=bar' . "\n";

        fwrite($stdout, $message);
    }

    protected function writeFailureSingleResultMessageWithoutData($stdout)
    {
        $message = '500 result=-1 ' . "\n";

        fwrite($stdout, $message);
    }

    protected function writeFailureMultiResultMessage($stdout)
    {
        $message = '500-data-part-1&' . "\n";
        $message .= '500-data-part-2&' . "\n";
        $message .= '500 result=-1 end' . "\n";

        fwrite($stdout, $message);
    }

    /**
     * @covers TryAGI\Client::init
     */
    public function testInit()
    {
        $this->assertCount(0, $this->object->getChannelVariables());
        $this->writeChannelVariablesMessage($this->stdout);
        $this->object->init();
        $this->assertCount(22, $this->object->getChannelVariables());
    }

    /**
     * @covers TryAGI\Client::send
     */
    public function testSendSuccessSingleResultMessageWithData()
    {
        $this->writeSuccessSingleResultMessageWithData($this->stdout);
        $result = $this->object->send('VERBOSE "test verbose message"');
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals('200', $result->getCode());
        $this->assertEquals('1', $result->getResult());
        $this->assertEquals('foo=bar', $result->getData());
    }

    /**
     * @covers TryAGI\Client::send
     */
    public function testSendSuccessSingleResultMessageWithoutData()
    {
        $this->writeSuccessSingleResultMessageWithoutData($this->stdout);
        $result = $this->object->send('VERBOSE "test verbose message"');
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals('200', $result->getCode());
        $this->assertEquals('1', $result->getResult());
        $this->assertEquals('', $result->getData());
    }

    /**
     * @covers TryAGI\Client::send
     */
    public function testSendSuccessMultiResultMessage()
    {
        $this->writeSuccessMultiResultMessage($this->stdout);
        $result = $this->object->send('VERBOSE "test verbose message"');
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals('200', $result->getCode());
        $this->assertEquals('1', $result->getResult());
        $this->assertEquals('data-part-1&data-part-2&end', $result->getData());
    }

    /**
     * @covers TryAGI\Client::send
     */
    public function testSendFailureSingleResultMessageWithData()
    {
        $this->writeFailureSingleResultMessageWithData($this->stdout);
        $result = $this->object->send('VERBOSE "test verbose message"');
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals('500', $result->getCode());
        $this->assertEquals('-1', $result->getResult());
        $this->assertEquals('foo=bar', $result->getData());
    }

    /**
     * @covers TryAGI\Client::send
     */
    public function testSendFailureSingleResultMessageWithoutData()
    {
        $this->writeFailureSingleResultMessageWithoutData($this->stdout);
        $result = $this->object->send('VERBOSE "test verbose message"');
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals('500', $result->getCode());
        $this->assertEquals('-1', $result->getResult());
        $this->assertEquals('', $result->getData());
    }

    /**
     * @covers TryAGI\Client::send
     */
    public function testSendFailureMultiResultMessage()
    {
        $this->writeFailureMultiResultMessage($this->stdout);
        $result = $this->object->send('VERBOSE "test verbose message"');
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals('500', $result->getCode());
        $this->assertEquals('-1', $result->getResult());
        $this->assertEquals('data-part-1&data-part-2&end', $result->getData());
    }

}
