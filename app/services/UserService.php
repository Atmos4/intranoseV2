<?php
class UserService
{

    /** @return ActiveUserDto[] */
    static function getActiveUserList()
    {
        return em()->createQuery("SELECT NEW ActiveUserDto(u.id, u.last_name, u.first_name, u.nose_email, u.phone) FROM User u WHERE u.status != :status ORDER BY u.last_name ASC, u.first_name ASC")
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
class ActiveUserDto
{
    function __construct(
        public int $id,
        public string $last_name,
        public string $first_name,
        public string $nose_email,
        public string $phone,
    ) {

    }
}

class DeactivatedUserDto
{
    function __construct(
        public int $id,
        public string $last_name,
        public string $first_name,
    ) {

    }
}