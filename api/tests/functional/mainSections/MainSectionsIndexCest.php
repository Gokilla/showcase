<?php

namespace api\tests\functional\mainSections;

use api\tests\FunctionalTester;
use Codeception\Example;
use common\models\Users;
use common\tests\InvalidPayloadTrait;
use Yii;
use yii\helpers\Url;

class MainSectionsIndexCest
{
    use InvalidPayloadTrait;

    public function _after(): void
    {
        Yii::$app->clear('cache');
    }

    public function respondsMainSectionsWithoutExpand(FunctionalTester $I): void
    {
        // Act
        $I->sendGet(Url::to(['/main-sections']));

        // Assert
        $I->seeResponseCodeIs(200);

        $response = $I->grabAjaxResponse();

        $I->assertIsArray($response);
        $I->assertArrayHasKey('data', $response);
        $I->assertIsArray($response['data']);

        /**
         * We have sections in DB already by refactoring migration.
         *
         * @see m220215_121035_create_main_sections_table
         */
        $I->assertCount(18, $response['data']);
    }

    public function respondsListForAuthorizedUser(FunctionalTester $I): void
    {
        // Arrange
        $user = factory(Users::class)->create();

        // Act
        $I->amLoggedInAs($user);
        $I->sendGet(Url::to(['/main-sections']));

        // Assert
        $I->seeResponseCodeIs(200);

        $response = $I->grabAjaxResponse();

        $I->assertIsArray($response);
        $I->assertArrayHasKey('data', $response);
        $I->assertIsArray($response['data']);
    }

    public function respondsMainSectionsWithExpands(FunctionalTester $I): void
    {
        // Arrange
        // Act
        $I->sendGet(Url::to(['/main-sections']), ['expand' => 'file']);

        // Assert
        $I->seeResponseCodeIs(200);

        $response = $I->grabAjaxResponse();

        $I->assertIsArray($response);
        $I->assertArrayHasKey('data', $response);
        $I->assertIsArray($response['data']);
    }

    public function respondsCorrectCorsHeadersOnOptions(FunctionalTester $I): void
    {
        // Arrange
        $user = factory(Users::class)->create(['id' => 4321]);

        // Act
        $I->amLoggedInAs($user);
        $I->sendOptions(Url::to(['/main-sections']));

        // Assert
        $I->seeResponseCodeIs(200);

        $I->seeHttpHeader('access-control-allow-headers', 'Content-Type, Authorization, X-Requested-With');
        $I->seeHttpHeader('allow', 'GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');
        $I->seeHttpHeader('access-control-allow-methods', 'GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');

        $response = $I->grabAjaxResponse();

        $I->assertNull($response);
    }

    /**
     * @dataProvider _invalidPayloadProvider
     */
    public function respondsErrorOnInvalidFilter(FunctionalTester $I, Example $example): void
    {
        // Act
        $I->sendGet(Url::to(['/main-sections']), ['filter' => $example['payload']]);

        // Assert
        $I->seeResponseCodeIs(422);
    }

    /**
     * @dataProvider _invalidHttpMethodDataProvider
     */
    public function respondsErrorIfInvalidHttpMethod(FunctionalTester $I, Example $example): void
    {
        // Act
        $I->send($example['httpMethod'], Url::to(['/main-sections']));

        // Assert
        $I->seeResponseCodeIs(405);
    }

    public function _invalidPayloadProvider(): array
    {
        $valid = [
            'id' => 1234,
            'name' => 'Name',
            'file_id' => 65,
            'position' => 1,
            'is_visible' => 1,
        ];

        $invalid = [
            'id' => [0, 'NaN'],
            'position' => [0, 'NaN'],
            'name' => [str_pad('a', 256, 'b')],
            'is_visible' => ['NotBool'],
        ];

        return $this->composeInvalidPayloads($valid, $invalid);
    }

    public function _invalidHttpMethodDataProvider(): array
    {
        return [
            ['httpMethod' => 'POST'],
            ['httpMethod' => 'PUT'],
            ['httpMethod' => 'PATCH'],
            ['httpMethod' => 'DELETE'],
            ['httpMethod' => 'HEAD'],
        ];
    }
}
