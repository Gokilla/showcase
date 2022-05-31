<?php

namespace backend\tests\functional\mainSections;

use backend\helpers\Permissions;
use backend\tests\FunctionalTester;
use common\models\AdminUser;
use common\models\MainSection;
use Yii;

class MainSectionDeleteCest
{
    /**
     * @param FunctionalTester $I
     */
    public function redirectsNonAuthorizedAdmins(FunctionalTester $I): void
    {
        // Arrange
        $mainSection = factory(MainSection::class)->create();

        // Act
        $I->sendAjaxPostRequest(['main-sections/delete', 'id' => $mainSection->id]);

        // Assert
        $I->seeResponseCodeIs(302);
    }

    /**
     * @param FunctionalTester $I
     */
    public function rejectsAdminWithoutPermission(FunctionalTester $I): void
    {
        // Arrange
        $admin = factory(AdminUser::class)->create();
        $mainSection = factory(MainSection::class)->create();

        // Act
        $I->amLoggedInAs($admin);
        $I->sendAjaxPostRequest(['main-sections/delete', 'id' => $mainSection->id]);

        // Assert
        $I->seeResponseCodeIs(403);
    }

    /**
     * @param FunctionalTester $I
     */
    public function respondsErrorIfNotFound(FunctionalTester $I): void
    {
        // Arrange
        $admin = factory(AdminUser::class)->create();

        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission(Permissions::MAIN_SECTION_CONTROL);
        $auth->assign($permission, $admin->id);

        $mainSection = factory(MainSection::class)->create();

        // Act
        $I->amLoggedInAs($admin);
        $I->sendAjaxPostRequest(['main-sections/delete', 'id' => 99999]);

        // Assert
        $I->seeResponseCodeIs(404);
    }

    /**
     * @param FunctionalTester $I
     */
    public function deleteSection(FunctionalTester $I): void
    {
        // Arrange
        $admin = factory(AdminUser::class)->create();

        $auth = Yii::$app->authManager;

        $auth->assign($auth->getPermission(Permissions::MAIN_SECTION_CONTROL), $admin->id);

        $section = factory(MainSection::class)->create();

        // Act
        $I->amLoggedInAs($admin);
        $I->sendAjaxPostRequest('main-sections/' . $section->id . '/delete');

        // Assert
        $I->seeResponseCodeIs(302);

        $I->assertNull(MainSection::findOne($section->id));
    }
}
