<?php

namespace backend\tests\functional\mainSections;

use backend\helpers\Permissions;
use backend\tests\FunctionalTester;
use common\models\AdminUser;
use common\models\MainSection;
use Yii;

class MainSectionIndexCest
{
    /**
     * @param FunctionalTester $I
     */
    public function redirectsNonAuthorizedAdmins(FunctionalTester $I): void
    {
        // Arrange
        factory(MainSection::class, 2)->create();

        // Act
        $I->sendAjaxGetRequest(['main-sections']);

        // Assert
        $I->seeResponseCodeIs(302);
    }

    /**
     * @param FunctionalTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function rejectsAdminWithoutPermission(FunctionalTester $I): void
    {
        // Arrange
        $admin = factory(AdminUser::class)->create();

        factory(MainSection::class, 2)->create();

        // Act
        $I->amLoggedInAs($admin);
        $I->amOnRoute('main-sections');

        // Assert
        $I->seeResponseCodeIs(403);
    }

    /**
     * @param FunctionalTester $I
     */
    public function showsIndexPage(FunctionalTester $I): void
    {
        // Arrange
        $admin = factory(AdminUser::class)->create();

        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission(Permissions::MAIN_SECTION_ACCESS);
        $auth->assign($permission, $admin->id);

        factory(MainSection::class, 3)->create();

        // Act
        $I->amLoggedInAs($admin);
        $I->amOnRoute('main-sections');

        // Assert
        $I->seeResponseCodeIs(200);
        $I->dontSeeResponseContains('GridView error'); // Means no code errors in grid view
    }
}
