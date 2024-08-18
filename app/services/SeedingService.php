<?php

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SeedingService
{
    static function createTestUser($fn, $ln)
    {
        $login = strtolower($ln . "_" . substr($fn, 0, 1));
        if (User::getByLogin($login)) {
            return false;
        }
        $fakePassword = strtolower($fn);
        $newUser = new User();
        $newUser->last_name = $ln;
        $newUser->first_name = $fn;
        $newUser->login = $login;
        $newUser->password = password_hash($fakePassword, PASSWORD_DEFAULT);
        $newUser->nose_email = UserHelper::generateUserEmail($fn, $ln);
        $newUser->real_email = "test@example.com";
        $newUser->phone = "0612345678";
        $newUser->permission = Permission::ROOT;
        $newUser->gender = Gender::M;
        $newUser->birthdate = date_create("1996-01-01");
        $newUser->status = UserStatus::ACTIVE;
        em()->persist($newUser);
        em()->flush();

        return [$newUser->login, $fakePassword];
    }

    static function createTestEvent()
    {
        $event = new Event();
        $event->name = "WE Championnats de France";
        $event->start_date = date_create()->add(new DateInterval("P10D"));
        $event->end_date = date_create()->add(new DateInterval("P11D"));
        $event->deadline = date_create()->add(new DateInterval("P9D"));

        // MD
        $activity_relay = new Activity();
        $activity_relay->event = $event;
        $activity_relay->deadline = $event->deadline;
        $activity_relay->name = "Relais";
        $activity_relay->date = date_create()->add(new DateInterval("P10D"));

        // LD
        $activity_ld = new Activity();
        $activity_ld->event = $event;
        $activity_ld->deadline = $event->deadline;
        $activity_ld->name = "LD";
        $activity_ld->date = date_create()->add(new DateInterval("P11D"));

        em()->persist($event);
        em()->persist($activity_relay);
        em()->persist($activity_ld);
        em()->flush();
    }

    static function applyMigrations()
    {
        $migrateCommand = new MigrateCommand(
            DependencyFactory::fromEntityManager(
                DBFactory::getConfig(),
                new ExistingEntityManager(em()),
            )
        );
        $input = new ArrayInput([]);
        $input->setInteractive(false);
        $exitcode = $migrateCommand->run($input, new BufferedOutput);
        return $exitcode === 0;
    }
}