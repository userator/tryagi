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
    const LINE_SEPARATOR = "\n";

    /**
     * @var array
     */
    private $channelVariables = [];

    /**
     * @var resource
     */
    private $stdin;

    /**
     * @var resource
     */
    private $stdout;

    /**
     * @var int
     */
    private $timeout = 500000;

    /**
     * @var int
     */
    private $chunk = 1;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param stream $stdin
     * @param stream $stdout
     * @param LoggerInterface $logger
     */
    public function __construct($stdin, $stdout, LoggerInterface $logger)
    {
        $this->stdin = $stdin;
        $this->stdout = $stdout;
        $this->logger = $logger;
    }

    public function __destruct()
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
        $this->logger->debug(__METHOD__, func_get_args());

        $message = $this->read();
        $this->logger->debug(__METHOD__ . ' $message', [$message]);
        if (!strlen($message)) throw new RuntimeException('message empty');
        $this->channelVariables = $this->readChannelVariables($message);
    }

    /**
     * @param string $command
     * @return Result
     * @throws InvalidArgumentException
     */
    public function send(string $command): Result
    {
        $this->logger->debug(__METHOD__, func_get_args());

        if (!$this->write($command)) throw new RuntimeException('Stream write');
        $message = $this->read();
        $this->logger->debug(__METHOD__ . ' $message', [$message]);
        if (!strlen($message)) throw new RuntimeException('message empty');
        $output = $this->readResult($message);
        $this->logger->debug(__METHOD__ . ' $output', [$output]);
        return Result::fromArray($output);
    }

    // tools

    private function readChannelVariables(string $message): array
    {
        $this->logger->debug(__METHOD__, func_get_args());

        $lines = explode(self::LINE_SEPARATOR, $message);

        // filter channel-variable line
        $channelVariableLines = array_filter($lines, function ($line) {
            return $this->isChannelVariableLine($line);
        });

        $output = array_reduce($channelVariableLines, function ($carry, $line) {
            $item = $this->parseChannelVariableLine($line);
            $carry[$item['key']] = $item['value'];
            return $carry;
        }, []);

        $this->logger->debug(__METHOD__, $output);

        return $output;
    }

    private function readResult(string $message): array
    {
        $this->logger->debug(__METHOD__, func_get_args());

        $lines = explode(self::LINE_SEPARATOR, $message);

        // filter single and multi lines
        $singleAndMultiLines = array_filter($lines, function ($line) {
            return $this->isSingleLine($line) || $this->isMultiLine($line);
        });

        if ($this->isContainManySingleLines($singleAndMultiLines)) throw new RuntimeException('contain many single lines');

        // parse single and multi lines
        $parsedLines = array_map(function($line) {
            if ($this->isMultiLine($line)) return $this->parseMultiLine($line);
            if ($this->isSingleLine($line)) return $this->parseSingleLine($line);
        }, $singleAndMultiLines);

        // collapse lines
        $output = array_reduce($parsedLines, function ($carry, $item) {
            return [
                'code' => $item['code'],
                'result' => $item['result'],
                'data' => isset($carry['data']) ? $carry['data'] . $item['data'] : $item['data'],
            ];
        });

        $this->logger->debug(__METHOD__, $output);

        return $output;
    }

    private function write(string $command): bool
    {
        $this->logger->debug(__METHOD__, func_get_args());

        return (bool) fwrite($this->stdout, $command . self::LINE_SEPARATOR);
    }

    private function read(): string
    {
        $this->logger->debug(__METHOD__, func_get_args());

        $stdin = [$this->stdin];
        $stdout = [];
        $stderr = [];

        $output = '';
        while (is_resource($this->stdin) && !feof($this->stdin) && stream_select($stdin, $stdout, $stderr, 0, $this->timeout)) {
            $this->logger->debug(__METHOD__ . ' metadata:', [stream_get_meta_data($this->stdin)]);
            $output .= stream_get_contents($this->stdin, $this->chunk);
        }

        $this->logger->debug(__METHOD__, [$output]);

        return $output;
    }

    private function isChannelVariableLine(string $line): bool
    {
        $this->logger->debug(__METHOD__, func_get_args());
        $matches = [];
        if (false === preg_match('/^agi_.+$/ui', $line, $matches)) throw new RuntimeException('Regexp error');
        return (bool) $matches;
    }

    private function isSingleLine(string $line): bool
    {
        $this->logger->debug(__METHOD__, func_get_args());
        $matches = [];
        if (false === preg_match('/^\d{3} /ui', $line, $matches)) throw new RuntimeException('Regexp error');
        return (bool) $matches;
    }

    private function isMultiLine(string $line): bool
    {
        $this->logger->debug(__METHOD__, func_get_args());
        $matches = [];
        if (false === preg_match('/^\d{3}-/ui', $line, $matches)) throw new RuntimeException('Regexp error');
        return (bool) $matches;
    }

    private function parseChannelVariableLine(string $line): array
    {
        $this->logger->debug(__METHOD__, func_get_args());
        $matches = [];
        if (false === preg_match('/^(agi_.+): (.*)$/ui', $line, $matches)) throw new RuntimeException('Regexp error');
        return [
            'key' => $matches[1],
            'value' => $matches[2],
        ];
    }

    private function parseMultiLine(string $line): array
    {
        $this->logger->debug(__METHOD__, func_get_args());
        $matches = [];
        if (false === preg_match('/^(\d{3})-(.*)$/ui', $line, $matches)) throw new RuntimeException('Regexp error');
        return [
            'code' => $matches[1],
            'result' => '',
            'data' => $matches[2],
        ];
    }

    private function parseSingleLine(string $line): array
    {
        $this->logger->debug(__METHOD__, func_get_args());
        $matches = [];
        if (false === preg_match('/^(\d{3}) result=(-?\d+)(?: \(?(.*)\)?)?$/ui', $line, $matches)) throw new RuntimeException('Regexp error');
        return [
            'code' => $matches[1],
            'result' => $matches[2],
            'data' => isset($matches[3]) ? $matches[3] : '',
        ];
    }

    private function isContainManySingleLines(array $lines): bool
    {
        $singleLines = array_filter($lines, function ($line) {
            return $this->isSingleLine($line);
        });

        return count($singleLines) > 1;
    }

}
