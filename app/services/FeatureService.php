<?php class FeatureService
{

    static function globalEnabled(Feature $f)
    {
        return match ($f) {
            Feature::Messages => false,
            default => false
        };
    }

    static function enabled(Feature $f, $uid = null)
    {
        if (self::globalEnabled($f))
            return true;
        if (env("FEATURE_" . $f->value) == 'true')
            return true;
        $uid ??= User::getMainUserId();
        return UserFeature::hasFeature($uid, $f->value);
    }
}