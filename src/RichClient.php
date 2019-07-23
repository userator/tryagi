<?php

namespace TryAGI;

use TryAGI\Client;
use TryAGI\Exceptions\InvalidArgumentException;

/**
 * @author userator
 */
class RichClient extends Client
{
    public function sayDigits(int $digits, string $escape = '')
    {
        return $this->send("SAY DIGITS $digits \"$escape\"");
    }

    public function streamFile($file, $escape = null, $offset = null)
    {
        return $this->send("STREAM FILE \"$file\" \"$escape\"" . !is_null($offset) ? " $offset" : '');
    }

    public function recordFile($filename, $format, $escape, $timeout, $beep = false)
    {
        return $this->send("RECORD FILE \"$filename\" \"$format\" \"$escape\" $timeout" . ($beep) ? ' BEEP ' : '');
    }

    public function waitForDigit(int $timeout = 500)
    {
        return $this->send("WAIT FOR DIGIT $timeout");
    }

    public function verbose($string)
    {
        return $this->send("VERBOSE \"$string\"");
    }

    public function dbGet($fam, $key)
    {
        return $this->send("DATABASE GET \"$fam\" \"$key\"");
    }

    public function getData($file, $maxdigits = '', $timeout = '')
    {
        return $this->send("GET DATA \"$file\" \"$timeout\" \"$maxdigits\"");
    }

    public function hangup()
    {
        return $this->send("HANGUP");
    }

    public function answer()
    {
        return $this->send("ANSWER");
    }

    public function sendText($text)
    {
        return $this->send("SEND TEXT \"$text\"");
    }

    public function receiveChar(int $timeout)
    {
        return $this->send("RECEIVE CHAR $timeout");
    }

    public function tddMode($switch)
    {
        switch (strtolower($switch)) {
            case "1":
            case "+":
            case "t":
            case "true":
            case "on":
                $switch = "on";
                break;
            case "0":
            case "-":
            case "f":
            case "false":
            case "nil":
            case "off":
                $switch = "off";
                break;
            default:
                throw new InvalidArgumentException('type invalid');
        }
        return $this->send("TDD MODE $switch");
    }

    public function sendImage(string $image)
    {
        return $this->send("SEND IMAGE \"$image\"");
    }

    public function sayNumber(int $num, string $digits)
    {
        return $this->send("SAY NUMBER $num \"$digits\"");
    }

    public function sayPhonetic($str, $digits)
    {
        return $this->send("SAY PHONETIC \"$str\" \"$digits\"");
    }

    public function sayTime($time, $digits)
    {
        return $this->send("SAY TIME $time \"$digits\"");
    }

    public function setContext($context)
    {
        return $this->send("SET CONTEXT \"$context\"");
    }

    public function setExtension($ext)
    {
        return $this->send("SET EXTENSION \"$ext\"");
    }

    public function setPriority(int $pri)
    {
        return $this->send("SET PRIORITY $pri");
    }

    public function setAutohangup(int $time)
    {
        return $this->send("SET AUTOHANGUP $time");
    }

    public function exec(string $app, string $args = '')
    {
        return $this->send("EXEC $app \"$args\"");
    }

    public function setCallerid($number)
    {
        return $this->send("SET CALLERID \"$number\"");
    }

    public function channelStatus($channel = null)
    {
        return $this->send("CHANNEL STATUS" . !is_null($channel) ? " $channel" : '');
    }

    public function setVariable($name, $value)
    {
        return $this->send("SET VARIABLE \"$name\" \"$value\"");
    }

    public function getVariable($name)
    {
        return $this->send("GET VARIABLE \"$name\"");
    }

    public function setMusicOn($switch, $class = null)
    {
        switch (strtolower($switch)) {
            case true:
            case "1":
            case "+":
            case "t":
            case "true":
            case "on":
                $switch = "on";
                break;
            case false:
            case "0":
            case "-":
            case "f":
            case "false":
            case "nil":
            case "off":
                $switch = "off";
                break;
            default:
                throw new InvalidArgumentException('type invalid');
        }
        return $this->send("SET MUSIC ON $switch" . !is_null($class) ? " $class" : '');
    }

    public function noop(string $string)
    {
        return $this->send("NOOP \"$string\"");
    }

}
