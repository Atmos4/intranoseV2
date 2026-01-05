<?php

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SeedingService
{
    static function createTestUser($first_name, $last_name, $em, $login = null, $password = null)
    {
        $login = $login ?: self::getFakeLogin($first_name, $last_name);
        if (UserService::getByLogin($em, $login)) {
            return false;
        }
        $password = $password ?: self::getFakePassword($first_name);

        $newUser = new User();
        $newUser->last_name = $last_name;
        $newUser->first_name = $first_name;
        $newUser->login = $login;
        $newUser->password = password_hash($password, PASSWORD_DEFAULT);
        $newUser->real_email = "test@example.com";
        $newUser->phone = "0612345678";
        $newUser->permission = Permission::ROOT;
        $newUser->gender = Gender::M;
        $newUser->birthdate = date_create("1996-01-01");
        $newUser->status = UserStatus::ACTIVE;
        $em->persist($newUser);
        $em->flush();

        return [$newUser, $password];
    }

    static function getFakeLogin($first_name, $last_name)
    {
        return strtolower($last_name . "_" . substr($first_name, 0, 1));

    }
    static function getFakePassword($first_name)
    {
        return strtolower($first_name);

    }

    static function createTestEvent($em)
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
        $activity_relay->start_date = date_create()->add(new DateInterval("P10D"));
        $activity_relay->end_date = date_create()->add(new DateInterval("P10D"));

        // LD
        $activity_ld = new Activity();
        $activity_ld->event = $event;
        $activity_ld->deadline = $event->deadline;
        $activity_ld->name = "LD";
        $activity_ld->start_date = date_create()->add(new DateInterval("P10D"));
        $activity_ld->end_date = date_create()->add(new DateInterval("P10D"));

        $em->persist($event);
        $em->persist($activity_relay);
        $em->persist($activity_ld);
        $em->flush();
    }

    static function applyMigrations(DB $db)
    {
        $migrateCommand = new MigrateCommand(
            DependencyFactory::fromEntityManager(
                DBFactory::getConfig($db),
                new ExistingEntityManager($db->em()),
            )
        );
        return self::runCommand($migrateCommand);
    }

    static function generateProxies(EntityManager $em)
    {
        $command = new GenerateProxiesCommand(new SingleManagerProvider($em));
        return self::runCommand($command);
    }

    private static function runCommand(Command $c)
    {
        $input = new ArrayInput([]);
        $input->setInteractive(false);
        $exitcode = $c->run($input, new BufferedOutput);
        return $exitcode === 0;
    }
}
