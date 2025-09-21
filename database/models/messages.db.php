<?php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'messages')]
class Message
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public string $content;

    #[Column]
    public DateTime $sentAt;

    #[ManyToOne]
    public User $sender;

    #[ManyToOne(targetEntity: Conversation::class, inversedBy: "messages")]
    public Conversation $conversation;


    public function __construct(User $sender, Conversation $conversation, string $content)
    {
        $this->sender = $sender;
        $this->conversation = $conversation;
        $this->content = $content;
        $this->sentAt = new DateTime();
    }
}

#[Entity, Table(name: 'conversations')]
class Conversation
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public string $name = "";

    #[Column(type: "string", nullable: true, unique: true)]
    public ?string $private_hash;

    #[OneToMany(mappedBy: "conversation", targetEntity: UserConversation::class)]
    public Collection $participants;

    #[OneToMany(mappedBy: "conversation", targetEntity: Message::class)]
    public Collection $messages;


    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    static function upsertPrivateConversation(User $u1, User $u2): Conversation
    {
        assert($u1->id != $u2->id, "Cannot create a conversation with yourself");
        $ids = [$u1->id, $u2->id];
        sort($ids);
        $hash = implode("_", $ids);

        $conversations = em()->createQuery("SELECT c from Conversation c
                WHERE c.private_hash = :hash")
            ->setParameter("hash", $hash)
            ->getResult();

        if (empty($conversations)) {
            $conv = new Conversation();
            $conv->private_hash = $hash;

            $user1conv = new UserConversation($u1, $conv);
            $user1conv->directUser = $u2;
            $user2conv = new UserConversation($u2, $conv);
            $user2conv->directUser = $u1;

            $conv->participants->add($user1conv);
            $conv->participants->add($user2conv);
            em()->persist($conv);
            em()->persist($user1conv);
            em()->persist($user2conv);
            em()->flush();
            return $conv;
        }
        return $conversations[0];
    }

    static function getAllFromUser(string $user_id)
    {
        $conversations = em()->createQuery("SELECT c,p,u from Conversation c
            JOIN c.participants p
            LEFT JOIN p.directUser u
            WHERE p.user = :user_id")
            ->setParameter("user_id", $user_id)
            ->getResult();

        return $conversations;
    }

    static function getUrlFromHash(string $hash, string $user_id)
    {
        $ids = explode("_", $hash);
        if ($ids[0] == $user_id)
            return $ids[1];
        return $ids[0];
    }

    function getMessages()
    {
        return em()->createQuery("SELECT m from Message m WHERE m.conversation = :conv ORDER BY m.sentAt DESC")->setParameter("conv", $this->id)->getResult();
    }

    function sendMessage($user, $content)
    {
        $m = new Message($user, $this, $content);
        $this->messages->add($m);
        em()->persist($m);
        em()->flush();
    }
}
#[Entity]
#[Table(name: "user_conversations")]
class UserConversation
{
    #[Id]
    #[ManyToOne]
    public User $user;

    #[Id]
    #[ManyToOne]
    public Conversation $conversation;

    /** Only set for private conversations */
    #[ManyToOne]
    public User|null $directUser = null;

    #[Column]
    public DateTime $joinedAt;

    #[Column]
    public DateTime $lastRead;

    #[Column]
    public bool $hasUnreadMessages = false;

    public function __construct(User $user, Conversation $conversation)
    {
        $this->user = $user;
        $this->conversation = $conversation;
        $this->joinedAt = new DateTime();
        $this->lastRead = new DateTime();
    }
}