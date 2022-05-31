<?php

namespace backend\tests\functional\mainSections;

use backend\helpers\Permissions;
use backend\tests\FunctionalTester;
use common\models\AdminUser;
use common\models\File;
use common\models\MainSection;
use Yii;

class MainSectionUpdateCest
{
    /**
     * @param FunctionalTester $I
     */
    public function redirectsNonAuthorizedAdmins(FunctionalTester $I): void
    {
        // Arrange
        $mainSection = factory(MainSection::class)->create();

        // Act
        $I->sendAjaxGetRequest(['main-sections/update', 'id' => $mainSection->id]);

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
        $I->amOnRoute('main-sections/update', ['id' => $mainSection->id]);

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
        $I->amOnRoute('main-sections/update', ['id' => 999999]);

        // Assert
        $I->seeResponseCodeIs(404);
    }

    /**
     * @param FunctionalTester $I
     */
    public function showsMainSectionUpdatePage(FunctionalTester $I): void
    {
        // Arrange
        $admin = factory(AdminUser::class)->create();

        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission(Permissions::MAIN_SECTION_CONTROL);
        $auth->assign($permission, $admin->id);

        $mainSection = factory(MainSection::class)->create();

        // Act
        $I->amLoggedInAs($admin);
        $I->amOnRoute('main-sections/update', ['id' => $mainSection->id]);

        // Assert
        $I->seeResponseCodeIs(200);
    }

    /**
     * @param FunctionalTester $I
     */
    public function updatesMainSection(FunctionalTester $I): void
    {
        // Arrange
        $admin = factory(AdminUser::class)->create();

        $auth = Yii::$app->authManager;

        $auth->assign($auth->getPermission(Permissions::MAIN_SECTION_CONTROL), $admin->id);

        $mainSection = factory(MainSection::class)->create();
        $file = factory(File::class)->create(['id' => 1]);

        // Act
        $I->amLoggedInAs($admin);
        $I->amOnRoute('main-sections/update', ['id' => $mainSection->id]);

        $I->fillField('MainSection[name]', 'Test Title');
        $I->fillField('MainSection[is_visible]', '1');
        $I->fillField('MainSection[file_id]', $file->id);
        $I->selectOption('MainSection[type]', 'Event');

        $I->click('Save');

        // Assert
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('Test Title');
        $I->seeResponseContains('1');
        $I->seeResponseContains('Event');
    }
}
