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
        return self::hasFeature($uid, $f->value);
    }

    /**
     * Get all feature from a user : first check the ClubFeatures level then the UserFeatures level
     * @return bool
     */
    static function hasFeature($uid, $feature)
    {
        $club_slug = ClubManagementService::getSelectedClubSlug();
        if (
            em()->createQuery("SELECT count(f) FROM ClubFeature f WHERE f.club = :c AND f.featureName = :feature AND f.enabled = :enabled")
                ->setParameters(["c" => $club_slug, "feature" => $feature, "enabled" => true])
                ->getSingleScalarResult()
        ) {
            $is_disabled = em()->createQuery("SELECT count(f) FROM UserFeature f WHERE f.user = :u AND f.featureName = :feature AND f.enabled = :enabled")
                ->setParameters(["u" => $uid, "feature" => $feature, "enabled" => false])
                ->getSingleScalarResult();
            return (!$is_disabled);
        }
        return false;
    }

    /**
     * list all feature for one user
     * @return UserFeature[]
     */
    static function listUser($uid)
    {
        return em()->createQuery("SELECT f FROM UserFeature f INDEX BY f.featureName LEFT JOIN ClubFeature c WITH f.featureName = c.featureName WHERE f.user = :u")->setParameters(["u" => $uid])->getResult();
    }

    /**
     * list all feature for one user
     * @return ClubFeature[]
     */
    static function listClub($slug = null, $service = null)
    {
        if (!$slug) {
            $slug = ClubManagementService::getSelectedClubSlug();
        }
        $em = $service->db->em() ?? em();
        return $em->createQuery("SELECT f from ClubFeature f INDEX BY f.featureName WHERE f.club = :c")->setParameters(["c" => $slug])->getResult();
    }
}