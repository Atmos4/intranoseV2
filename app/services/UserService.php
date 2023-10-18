<?php
class UserService
{

    /** @return User[] */
    static function getActiveUserList()
    {
        return em()->createQuery("SELECT u FROM User u WHERE u.status != :status ORDER BY u.last_name ASC, u.first_name ASC")
            ->setParameters(['status' => UserStatus::DEACTIVATED])->getResult();
    }


    /** @return DeactivatedUserDto[] */
    static function getDeactivatedUserList()
    {
        return em()->createQuery("SELECT NEW DeactivatedUserDto(u.id, u.last_name, u.first_name) FROM User u WHERE u.status = :status ORDER BY u.last_name ASC, u.first_name ASC")
            ->setParameters(['status' => UserStatus::DEACTIVATED])->getResult();
    }
}

// DTOs
class DeactivatedUserDto
{
    function __construct(
        public int $id,
        public string $last_name,
        public string $first_name,
    ) {

    }
}