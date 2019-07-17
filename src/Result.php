<?php

namespace TryAGI;

class Result
{
    const CODE_200 = 200;
    const CODE_510 = 510;
    const CODE_511 = 511;
    const CODE_520 = 520;

    /** @var int */
    protected $code = 0;

    /** @var string */
    protected $result = '';

    /** @var string */
    protected $data = '';

    /**
     * @param string $text
     * @return Result
     */
    static public function fromText(string $text): Result
    {
        $output = new static();
        return $output;
    }

    /**
     * @param int $code
     * @param string $result
     * @param string $data
     * @return Result
     */
    static public function fromArgs(int $code, string $result, string $data = ''): Result
    {
        $output = new static();
        $output->setCode($code);
        $output->setResult($result);
        $output->setData($data);
        return $output;
    }

    /**
     * @param array $args
     * @return Result
     */
    static public function fromArray(array $args): Result
    {
        $output = new static();
        if (isset($args['code'])) $output->setCode($args['code']);
        if (isset($args['result'])) $output->setResult($args['result']);
        if (isset($args['data'])) $output->setData($args['data']);
        return $output;
    }

    // mutators

    public function getCode(): int
    {
        return $this->code;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setCode(int $code)
    {
        $this->code = $code;
    }

    public function setResult(string $result)
    {
        $this->result = $result;
    }

    public function setData(string $data)
    {
        $this->data = $data;
    }

    // tools

    private function isSeparatorLine(string $text)
    {
        
    }

    private function isMultiLine(string $text)
    {
        
    }

    private function isSingleLine(string $text)
    {
        
    }

    private function isChannelVariableLine(string $text)
    {
        
    }

}
