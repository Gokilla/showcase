<?php

namespace backend\tests\functional\MainSections;

use backend\helpers\Permissions;
use backend\tests\FunctionalTester;
use Codeception\Stub;
use common\models\AdminUser;
use common\models\File;
use common\services\MainSectionsService;
use Yii;

class MainSectionCreateCest
{
    /**
     * @param FunctionalTester $I
     */
    public function redirectsNonAuthorizedAdmins(FunctionalTester $I): void
    {
        // Act
        $I->sendAjaxGetRequest(['main-sections/create']);

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

        // Act
        $I->amLoggedInAs($admin);
        $I->amOnRoute('main-sections/create');

        // Assert
        $I->seeResponseCodeIs(403);
    }

    /**
     * @param FunctionalTester $I
     */
    public function showsMainSectionCreatePage(FunctionalTester $I): void
    {
        // Arrange
        $admin = factory(AdminUser::class)->create();

        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission(Permissions::MAIN_SECTION_CONTROL);
        $auth->assign($permission, $admin->id);

        // Act
        $I->amLoggedInAs($admin);
        $I->amOnRoute('main-sections/create');

        // Assert
        $I->seeResponseCodeIs(200);
    }

    /**
     * @param FunctionalTester $I
     */
    public function createsNewMainSection(FunctionalTester $I): void
    {
        $admin = factory(AdminUser::class)->create();
        $auth = Yii::$app->authManager;

        $auth->assign($auth->getPermission(Permissions::MAIN_SECTION_CONTROL), $admin->id);

        $service = Stub::make(MainSectionsService::class, [
            'saveSectionCases' => function($arg1, $arg2) use ($I): void {
                $I->assertEquals('1', $arg1);
                $I->assertEquals('[]', $arg2);
            },
        ]);

        Yii::$container->set(MainSectionsService::class, fn() => $service);

        $file = factory(File::class)->create(['id' => 1]);

        // Act
        $I->amLoggedInAs($admin);
        $I->amOnRoute('main-sections/create');

        $I->fillField('MainSection[name]', 'Test');
        $I->fillField('MainSection[is_visible]', '1');
        $I->fillField('MainSection[file_id]', $file->id);
        $I->selectOption('MainSection[type]', 'Event');

        $I->click('Save');

        // Assert
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('Test');
        $I->seeResponseContains('1');
        $I->seeResponseContains('Event');
    }
}
