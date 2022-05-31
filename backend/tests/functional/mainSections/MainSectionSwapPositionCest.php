<?php

namespace backend\tests\functional\mainSections;

use backend\helpers\Permissions;
use backend\tests\FunctionalTester;
use Codeception\Stub;
use common\models\AdminUser;
use common\models\MainSection;
use common\services\MainSectionsService;
use Yii;

class MainSectionSwapPositionCest
{
    /**
     * @param FunctionalTester $I
     */
    public function redirectsNonAuthorizedAdmins(FunctionalTester $I): void
    {
        // Arrange
        $mainSection1 = factory(MainSection::class)->create();
        $mainSection2 = factory(MainSection::class)->create();

        // Act
        $I->sendAjaxPostRequest([
            'main-sections/swap-positions',
            'oldPosition' => $mainSection2->id,
            'newPosition' => $mainSection1->id,
        ]);

        // Assert
        $I->seeResponseCodeIs(302);
    }

    /**
     * @param FunctionalTester $I
     */
    public function updatePosition(FunctionalTester $I): void
    {
        // Arrange
        $mainSections = factory(MainSection::class, 2)->create();
        $admin = factory(AdminUser::class)->create();

        // Act
        $auth = Yii::$app->authManager;

        $auth->assign($auth->getPermission(Permissions::MAIN_SECTION_CONTROL), $admin->id);

        // Assert
        $I->amLoggedInAs($admin);
        $I->amOnRoute('main-sections');

        $I->sendAjaxPostRequest([
            'main-sections/swap-positions',
            'oldPosition' => $mainSections[1]['position'],
            'newPosition' => $mainSections[0]['position'],
        ]);

        $service = Stub::make(MainSectionsService::class, [
            'swapPositions' => function($arg1, $arg2) use ($I): void {
                $I->assertEquals('1', $arg1);
                $I->assertEquals('2', $arg2);
            },
        ]);

        Yii::$container->set(MainSectionsService::class, fn() => $service);

        $I->amOnRoute('main-sections');
    }
}
