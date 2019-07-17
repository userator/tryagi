<?php

namespace TryAGI;

use TryAGI\Exceptions\InvalidArgumentException;

/**
 * @author userator
 */
class RichClient extends Client
{
    public function say_digits(int $digits, string $escape = '')
    {
        return $this->send("SAY DIGITS $digits \"$escape\"");
    }

    public function stream_file($file, $escape = null, $offset = null)
    {
        $cmd = "STREAM FILE \"$file\" \"$escape\"";
        if ($offset != null) $cmd .= " $offset";
        return $this->send($cmd);
    }

    public function record_file($filename, $format, $escape, $timeout, $beep = false)
    {
        $beep = ($beep) ? ' BEEP ' : '';
        return $this->send("RECORD FILE \"$filename\" \"$format\" \"$escape\" $timeout" . $beep);
    }

    public function wait_for_digit(int $timeout = 500)
    {
        return $this->send("WAIT FOR DIGIT $timeout");
    }

    public function verbose($string)
    {
        return $this->send("VERBOSE \"$string\"");
    }

    public function db_get($fam, $key)
    {
        return $this->send("DATABASE GET \"$fam\" \"$key\"");
    }

    public function get_data($file, $maxdigits = '', $timeout = '')
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

    public function send_text($text)
    {
        return $this->send("SEND TEXT \"$text\"");
    }

    public function receive_char($timeout)
    {
        if (!is_int($timeout)) return false;
        return $this->send("RECEIVE CHAR $timeout");
    }

    public function tdd_mode($switch)
    {
        $switch = strtolower($switch);
        switch ($switch) {
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

    public function send_image(string $image)
    {
        return $this->send("SEND IMAGE \"$image\"");
    }

    public function say_number(int $num, string $digits)
    {
        return $this->send("SAY NUMBER $num \"$digits\"");
    }

    public function say_phonetic($str, $digits)
    {
        return $this->send("SAY PHONETIC \"$str\" \"$digits\"");
    }

    public function say_time($time, $digits)
    {
        return $this->send("SAY TIME $time \"$digits\"");
    }

    public function set_context($context)
    {
        return $this->send("SET CONTEXT \"$context\"");
    }

    public function set_extension($ext)
    {
        return $this->send("SET EXTENSION \"$ext\"");
    }

    public function set_priority($pri)
    {
        if (!is_int($pri)) throw new InvalidArgumentException('type invalid');
        return $this->send("SET PRIORITY $pri");
    }

    public function set_autohangup(int $time)
    {
        return $this->send("SET AUTOHANGUP $time");
    }

    public function exec(string $app, string $args = '')
    {
        return $this->send("EXEC $app \"$args\"");
    }

    public function set_callerid($number)
    {
        return $this->send("SET CALLERID \"$number\"");
    }

    public function channel_status($channel = null)
    {
        if ($channel) $channel = " \"$channel\"";
        return $this->send("CHANNEL STATUS" . $channel);
    }

    public function set_variable($name, $value)
    {
        return $this->send("SET VARIABLE \"$name\" \"$value\"");
    }

    public function get_variable($name)
    {
        return $this->send("GET VARIABLE \"$name\"");
    }

    public function set_music_on($switch, $class = null)
    {
        if ($class) $class = " \"$class\"";

        $switch = strtolower($switch);
        switch ($switch) {
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
        return $this->send("SET MUSIC ON $switch" . $class);
    }

    public function noop(string $string)
    {
        return $this->send("NOOP \"$string\"");
    }

}
