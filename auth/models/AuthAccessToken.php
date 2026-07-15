<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Uuid;

enum AuthAccessTokenType: string
{
    case ACTIVATE = 'ACTIVATE';
    case INVITE = 'INVITE';
    case RESET_PASSWORD = 'RESET_PASSWORD';
    case REMEMBER_ME = 'REMEMBER_ME';
}

#[Entity, Table(name: 'auth_access_tokens')]
class AuthAccessToken
{
    #[Id]
    #[Column(length: 36, unique: true)]
    public ?string $id = null;

    #[ManyToOne]
    public ?AuthUser $user = null;

    #[Column(nullable: true)]
    public ?string $hashed_validator = null;

    #[Column]
    public DateTime $expiration;

    #[Column(length: 20)]
    public AuthAccessTokenType $type;

    public function __construct(AuthUser $user, AuthAccessTokenType $type, DateInterval $duration = new DateInterval('PT5M'))
    {
        $this->id = Uuid::uuid4();
        $this->user = $user;
        $this->type = $type;
        $this->expiration = date_create()->add($duration);
    }

    /** This is added security against
     * - compromised DB with the hash
     * - timing attacks
     * Use this for long lived tokens */
    public function createHashedValidator(): string
    {
        $validator = bin2hex(random_bytes(32));
        $this->hashed_validator = password_hash($validator, PASSWORD_DEFAULT);
        return $validator;
    }

    public static function retrieve(EntityManager $em, string $uuid, bool $forceExit = false): ?AuthAccessToken
    {
        if (!Uuid::isValid($uuid)) {
            return $forceExit ? force_404("invalid token") : null;
        }
        // TODO: optimize with DQL if perf problems.
        $token = em->find(AuthAccessToken::class, $uuid);
        if (!$token) {
            return $forceExit ? force_404("token not found") : null;
        }
        if (date_create() > $token->expiration) {
            em->remove($token);
            em->flush();
            return $forceExit ? force_404("token expired") : null;
        }
        return $token;
    }

    public static function retrieveOrFail(string $uuid): AuthAccessToken
    {
        return self::retrieve($uuid, true) ?? throw new Exception("Token not found");
    }
}
