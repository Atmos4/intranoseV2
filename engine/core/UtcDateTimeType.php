<?php
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\ConversionException;

/**
 * Custom DateTime type that stores all dates in UTC in the database
 * and automatically converts to/from Europe/Paris timezone for the application.
 * 
 * This provides transparent timezone handling at the ORM level:
 * - When loading from DB: UTC -> Europe/Paris
 * - When saving to DB: Europe/Paris -> UTC
 */
class UtcDateTimeType extends DateTimeType
{
    public const DISPLAY_TIMEZONE = 'Europe/Paris';

    /**
     * Convert from database value (UTC) to PHP DateTime object (Europe/Paris)
     * 
     * @param mixed $value The database value
     * @param AbstractPlatform $platform The database platform
     * @return \DateTime|null DateTime in local timezone
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value || $value instanceof \DateTime) {
            return $value;
        }
        // Read from DB as UTC, then convert to Europe/Paris
        $converted = \DateTime::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            self::getUtc()
        );
        if (!$converted) {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                $platform->getDateTimeFormatString()
            );
        }
        $converted->setTimezone(self::getLocal());
        return $converted;
    }

    /**
     * Convert from PHP DateTime object (Europe/Paris) to database value (UTC)
     * 
     * @param mixed $value The PHP DateTime value
     * @param AbstractPlatform $platform The database platform
     * @return string|null DateTime string in UTC timezone
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!($value instanceof \DateTime)) {
            throw new \InvalidArgumentException('Expected DateTime object, got ' . gettype($value));
        }

        $utcDateTime = clone $value;
        $utcDateTime->setTimezone(self::getUtc());

        return parent::convertToDatabaseValue($utcDateTime, $platform);
    }

    /**
     * Get the name of this type
     */
    public function getName(): string
    {
        return 'datetime';
    }

    private static function getUtc(): DateTimeZone
    {
        return new DateTimeZone('UTC');
    }

    private static function getLocal(): DateTimeZone
    {
        return new DateTimeZone(self::DISPLAY_TIMEZONE);
    }
}
