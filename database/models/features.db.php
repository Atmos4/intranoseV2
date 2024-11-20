<?php
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'user_features')]
class UserFeature
{
    #[Id, Column]
    public string|null $featureName = null;

    #[Id, ManyToOne]
    public User $user;

    #[Column]
    public bool $enabled = false;

    /**
     * Get all feature from a user
     * @return bool
     */
    static function hasFeature($uid, $feature)
    {
        return !!em()->createQuery("SELECT count(f) FROM UserFeature f WHERE f.user = :u AND f.featureName = :feature AND f.enabled = :enabled")
            ->setParameters(["u" => $uid, "feature" => $feature, "enabled" => true])
            ->getSingleScalarResult();
    }
}

enum Feature: string
{
    case Messages = "messages";

    function globalEnabled()
    {
        return match ($this) {
            Feature::Messages => false,
            default => false
        };
    }

    function enabled($uid = null)
    {
        if ($this->globalEnabled())
            return true;

        if (env("FEATURE_" . $this->value) == 'true')
            return true;

        $uid ??= User::getMainUserId();
        return UserFeature::hasFeature($uid, $this->value);
    }
}