<?php

namespace api\tests;

/**
 * Inherited Methods.
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

    public const TEST_AUTH_KEY = 'testauthkey';

    public function authAsBot(string $authToken = self::TEST_AUTH_KEY): void
    {
        $this->haveHttpHeader('Authorization', "Bearer $authToken");
    }

    public function authAsApiClient(string $authToken = self::TEST_AUTH_KEY): void
    {
        $this->haveHttpHeader('Authorization', "Bearer $authToken");
    }
}
