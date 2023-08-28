<?php
class Formatter extends Singleton
{
    private IntlDateFormatter $formatter;

    protected function __construct()
    {
        $this->formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE, "Europe/Paris");
    }

    static function get(string $format = null)
    {
        if ($format) {
            self::getInstance()->formatter->setPattern($format);
        }
        return self::getInstance()->formatter;
    }
}