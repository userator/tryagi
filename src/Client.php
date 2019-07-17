<?php

namespace TryAGI;

use Psr\Log\LoggerInterface;
use TryAGI\Exceptions\InvalidArgumentException;
use TryAGI\Exceptions\RuntimeException;
use TryAGI\Result;

/**
 * @author userator
 */
class Client
{
    const CODE_200 = 200;
    const CODE_510 = 510;
    const CODE_511 = 511;
    const CODE_520 = 520;

    /**
     * @var array
     */
    private $channelVariables = [];

    /**
     * @var resource
     */
    private $stdin = STDIN;

    /**
     * @var resource
     */
    private $stdout = STDOUT;

    /**
     * @var int
     */
    private $timeout = 1000000;

    /**
     * @var int
     */
    private $chunk = 512;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    function __destruct()
    {
        fclose($this->stdin);
        fclose($this->stdout);
    }

    // mutators

    public function getChannelVariables(): array
    {
        return $this->channelVariables;
    }

    public function getStdin()
    {
        return $this->stdin;
    }

    public function getStdout()
    {
        return $this->stdout;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getChunk(): int
    {
        return $this->chunk;
    }

    public function setChannelVariables(array $channelVariables)
    {
        $this->channelVariables = $channelVariables;
    }

    public function setStdin($stdin)
    {
        $this->stdin = $stdin;
    }

    public function setStdout($stdout)
    {
        $this->stdout = $stdout;
    }

    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    public function setChunk(int $chunk)
    {
        $this->chunk = $chunk;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    // business logic

    public function init()
    {
        $this->channelVariables = $this->readChannelVariables($this->read());
    }

    /**
     * @param string $command
     * @return \Response
     * @throws InvalidArgumentException
     */
    public function send(string $command): Result
    {
        $this->write($command);
        $message = $this->read();
        return Result::fromArray($this->readResult($message));
    }

    // tools

    private function readChannelVariables(string $message): array
    {
        $lines = explode("\n", $message);

        if (!$lines) throw new RuntimeException('lines do not exist');

        $output = [];

        foreach ($lines as $line) {
            if (!$this->isChannelVariableLine($line)) continue;
            $parsed = $this->parseChannelVariableLine($line);
            $output[$parsed['key']] = $parsed['value'];
        }

        return $output;
    }

    private function readResult(string $message): array
    {
        $lines = explode("\n", $message);

        if (!$lines) throw new RuntimeException('lines do not exist');

        $lines = array_filter($lines, function ($line) {
            return $this->isSingleLine($line) || $this->isMultiLine($line);
        });

        $singleLines = array_filter($lines, function ($line) {
            return $this->isSingleLine($line);
        });

        if (count($singleLines) > 1) throw new RuntimeException('detect many single-line');

        $parsedLines = array_map(function($line) {
            if ($this->isMultiLine($line)) return $this->parseMultiLine($line);
            if ($this->isSingleLine($line)) return $this->parseSingleLine($line);
        }, $lines);

        return $this->collapseLines($parsedLines);
    }

    private function write(string $text): int
    {
        $output = fwrite($this->stdout, $text);
        if (false === $output) throw new RuntimeException('Stream write');
        return $output;
    }

    private function read(): string
    {
        $stdin = [$this->stdin];
        $stdout = [];
        $stderr = [];

        $output = '';
        while (is_resource($this->stdin) && !feof($this->stdin)) {
            $ready = stream_select($stdin, $stdout, $stderr, 0, $this->timeout);
            if (false === $ready) throw new RuntimeException('Stream error');
            if (0 === $ready) throw new RuntimeException('Stream timeout');
            $output .= stream_get_contents($this->stdin, $this->chunk);
        }

        return $output;
    }

    private function isSeparatorLine(string $line): bool
    {
        return $line === '';
    }

    private function isChannelVariableLine(string $line): bool
    {
        $matches = [];
        if (false === preg_match('/^agi_.+$/', preg_quote($line), $matches)) throw new RuntimeException('Regexp error');
        return (bool) $matches;
    }

    private function isSingleLine(string $line): bool
    {
        $matches = [];
        if (false === preg_match('/^\d{3} /', preg_quote($line), $matches)) throw new RuntimeException('Regexp error');
        return (bool) $matches;
    }

    private function isMultiLine(string $line): bool
    {
        $matches = [];
        if (false === preg_match('/^\d{3}-/', preg_quote($line), $matches)) throw new RuntimeException('Regexp error');
        return (bool) $matches;
    }

    private function parseChannelVariableLine(string $line): array
    {
        $matches = [];
        if (false === preg_match('/^(agi_.+): (.*)$/', preg_quote($line), $matches)) throw new RuntimeException('Regexp error');
        return [
            'key' => $matches[1],
            'value' => $matches[2],
        ];
    }

    private function parseMultiLine(string $line): array
    {
        $matches = [];
        if (false === preg_match('/^(\d{3})-(.*)$/', preg_quote($line), $matches)) throw new RuntimeException('Regexp error');
        return [
            'code' => $matches[1],
            'result' => '',
            'data' => $matches[2],
        ];
    }

    private function parseSingleLine(string $line): array
    {
        $matches = [];
        if (false === preg_match('/^(\d{3}) result=(-?\d+)(?: \(?(.*)\)?)?$/', preg_quote($line), $matches)) throw new RuntimeException('Regexp error');
        return [
            'code' => $matches[1],
            'result' => $matches[2],
            'data' => isset($matches[3]) ? $matches[3] : '',
        ];
    }

    private function collapseLines(array $lines): array
    {
        return array_reduce($lines, function ($carry, $item) {
            return [
                'code' => $item['code'],
                'result' => $item['result'],
                'data' => $carry['data'] . $item['data'],
            ];
        }, [
            'code' => '',
            'result' => '',
            'data' => '',
        ]);
    }

}
