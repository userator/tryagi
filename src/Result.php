<?php

namespace TryAGI;

class Result
{
    const CODE_SUCCESS = 200;
    const CODE_FAILURE = 500;

    /** @var string */
    protected $code = '';

    /** @var string */
    protected $result = '';

    /** @var string */
    protected $data = '';

    /**
     * @param string $code
     * @param string $result
     * @param string $data
     * @return Result
     */
    static public function fromArgs(string $code, string $result, string $data = ''): Result
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

    public function getCode(): string
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

    public function setCode(string $code)
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

    public function isCodeSuccess()
    {
        return floor($this->code / 100) * 100 == self::CODE_SUCCESS;
    }

    public function isCodeFailure()
    {
        return floor($this->code / 100) * 100 == self::CODE_FAILURE;
    }

}
