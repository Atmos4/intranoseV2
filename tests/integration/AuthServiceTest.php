<?php declare(strict_types=1);

final class AuthServiceTest extends BaseTestCase
{

    function testExistingUserCanLogin()
    {
        [$service, $user, $password] = $this->setupTest();
        $v = new Validator();
        $redirect = $service->tryLogin($user->login, $password, false, $v);
        $this->assertTrue($redirect);
        $this->assertEquals($_SESSION["user_id"], $user->id);
    }

    function testLoginWithEmail()
    {
        [$service, $user, $password] = $this->setupTest();
        $v = new Validator();
        $redirect = $service->tryLogin($user->real_email, $password, false, $v);
        $this->assertTrue($redirect);
        $this->assertEquals($_SESSION["user_id"], $user->id);
    }

    function testMissingUserCannotLogin()
    {
        [$service] = $this->setupTest();
        $v = new Validator();
        $redirect = $service->tryLogin("fakeLogin", "fakePassword", false, $v);
        $this->assertFalse($v->valid());
        $this->assertFalse($redirect);
        $this->assertFalse(isset($_SESSION["user_id"]));
    }

    function testLogoutWorks()
    {
        [$service, $user, $password] = $this->setupTest();
        $service->tryLogin($user->login, $password);
        $this->assertEquals($_SESSION["user_id"], $user->id);
        $service->logout();
        $this->assertFalse(isset($_SESSION["user_id"]));
    }

    /** @return array{AuthService, User, string} */
    function setupTest()
    {
        [$u, $pw] = SeedingService::createTestUser("Jon", "Doe", $this->db->em());
        return [new AuthService($this->db->em()), $u, $pw];
    }
}
