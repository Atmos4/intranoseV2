<?php

class Cli
{
    function __construct(public string $message = "")
    {
    }

    function out(string $m = "")
    {
        print $this->add($m)->flush() . PHP_EOL;
        return $this;
    }
    function flush()
    {
        $r = $this->message;
        $this->message = "";
        return $r;
    }
    function __tostring()
    {
        return $this->flush();
    }
    function exit($c = 1)
    {
        exit($c);
    }
    /**
     * Concatenates the provided string to the message
     */
    function add(string $m)
    {
        $this->message .= $m;
        return $this;
    }
    /**
     * Switches output to provided color
     */
    function color(CliColor $color)
    {
        $this->add($color->value);
        return $this;
    }
    /**
     * Resets color output
     */
    function reset()
    {
        return $this->color(CliColor::RESET);
    }
    /**
     * Preformatted error message with an xmark
     */
    function error($m)
    {
        return $this->color(CliColor::RED)->xmark()->reset()->out(" $m");
    }
    /**
     * Preformatted abort message, terminates the CLI with code 1.
     */
    function abort($m)
    {
        $this->error($m)->exit();
    }
    /**
     * Preformatted success message with a checkmark
     */
    function ok($m)
    {
        return $this->color(CliColor::GREEN)->check()->reset()->out(" $m");
    }
    /**
     * Preformatted warning message
     */
    function warning($m)
    {
        return $this->color(CliColor::YELLOW)->add("!")->reset()->out(" $m");
    }
    /**
     * Preformatted success message, terminates the CLI with code 0
     */
    function success($m = "All done")
    {
        exit(PHP_EOL . "$m \u{2728}" . PHP_EOL);
    }

    /**
     * Adds a check mark to the message
     */
    function check()
    {
        return $this->add("\u{2713}");
    }
    /**
     * x-mark
     */
    function xmark()
    {
        return $this->add("\u{2716}");
    }
    /**
     * new line
     */
    function br()
    {
        return $this->add(PHP_EOL);
    }
}

enum CliColor: string
{
    // Reset all
    case RESET = "\e[0m";

    // Attributes
    case BOLD = "\e[1m";
    case UN_BOLD = "\e[21m";
    case DIM = "\e[2m";
    case UN_DIM = "\e[22m";
    case UNDERLINED = "\e[4m";
    case UN_UNDERLINED = "\e[24m";
    case BLINK = "\e[5m";
    case UN_BLINK = "\e[25m";
    case REVERSE = "\e[7m";
    case UN_REVERSE = "\e[27m";
    case HIDDEN = "\e[8m";
    case UN_HIDDEN = "\e[28m";

    // Forground colors (Warning: some include bold / unbold)
    case BLACK = "\033[0;30m";
    case DARK_GRAY = "\033[1;30m";
    case RED = "\033[0;31m";
    case LIGHT_RED = "\033[1;31m";
    case GREEN = "\033[0;32m";
    case LIGHT_GREEN = "\033[1;32m";
    case YELLOW = "\033[0;33m";
    case BLUE = "\033[0;34m";
    case LIGHT_BLUE = "\033[1;34m";
    case MAGENTA = "\033[0;35m";
    case PURPLE = "\033[2;35m";
    case LIGHT_PURPLE = "\033[1;35m";
    case CYAN = "\033[0;36m";
    case LIGHT_CYAN = "\033[1;36m";
    case LIGHT_GRAY = "\033[2;37m";
    case BOLD_WHITE = "\033[1;38m";
    case WHITE = "\033[0;38m";
    case FG_DEFAULT = "\033[39m";
    case GRAY = "\033[0;90m";
    case LIGHT_RED_ALT = "\033[91m";
    case LIGHT_GREEN_ALT = "\033[92m";
    case LIGHT_YELLOW_ALT = "\033[93m";
    case LIGHT_YELLOW = "\033[1;93m";
    case LIGHT_BLUE_ALT = "\033[94m";
    case LIGHT_MAGENTA_ALT = "\033[95m";
    case LIGHT_CYAN_ALT = "\033[96m";
    case LIGHT_WHITE_ALT = "\033[97m";

    // Backgrounds
    case BG_BLACK = "\033[40m";
    case BG_RED = "\033[41m";
    case BG_GREEN = "\033[42m";
    case BG_YELLOW = "\033[43m";
    case BG_BLUE = "\033[44m";
    case BG_MAGENTA = "\033[45m";
    case BG_CYAN = "\033[46m";
    case BG_LIGHT_GRAY = "\033[47m";
    case BG_DEFAULT = "\033[49m";
    case BG_DARK_GRAY = "\e[100m";
    case BG_LIGHT_RED = "\e[101m";
    case BG_LIGHT_GREEN = "\e[102m";
    case BG_LIGHT_YELLOW = "\e[103m";
    case BG_LIGHT_BLUE = "\e[104m";
    case BG_LIGHT_MAGENTA = "\e[105m";
    case BG_LIGHT_CYAN = "\e[106m";
    case BG_WHITE = "\e[107m";
}