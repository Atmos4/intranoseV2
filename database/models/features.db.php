<?php
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
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

    function __construct(User $user, Feature $feature)
    {
        $this->featureName = $feature->value;
        $this->user = $user;
    }

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

    /**
     * list all feature for one user
     * @return UserFeature[]
     */
    static function list($uid)
    {
        return em()->createQuery("SELECT f from UserFeature f INDEX BY f.featureName WHERE f.user = :u")->setParameters(["u" => $uid])->getResult();
    }
}

enum Feature: string
{
    case Messages = "Messages";
    case Carpooling = "Carpooling";

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