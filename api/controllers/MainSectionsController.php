<?php

namespace api\controllers;

use api\components\BaseApiController;
use api\traits\OverrideSerializationTrait;
use api\traits\PrepareProviderTrait;
use backend\models\MainSectionSearch;
use common\models\MainSection;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use yii\filters\PageCache;
use yii\helpers\ArrayHelper;
use yii\rest\IndexAction;

class MainSectionsController extends BaseApiController
{
    use OverrideSerializationTrait;
    use PrepareProviderTrait;

    private const CACHE_TTL = 15; // Seconds.

    private const INDEX_EXPAND = [
        'file',
        'cases',
        'cases.file',
        'cases.lastSuccessfulGeneration',
        'cases.activeFeastCase',
        'cases.crowdfunding',
        'cases.limitedTime',
        'cases.limitedQuantity',
    ];

    public function behaviors(): array
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                self::AUTHENTICATOR_KEY => [
                    'class' => JwtHttpBearerAuth::class,
                    'optional' => ['index'],
                ],
                'pageCache' => [
                    'class' => PageCache::class,
                    'only' => ['index'],
                    'duration' => self::CACHE_TTL,
                    'variations' => [
                        Yii::$app->user->id,
                        Yii::$app->request->queryString,
                    ],
                ],
            ]
        );
    }

    public function actions(): array
    {
        return [
            /**
             * @SWG\Get(
             *          path="/main-sections",
             *          security={{"apiAuth":{}}},
             *          tags={"MainSections"},
             *          summary="Get all sections with cases.",
             *
             *     @SWG\Parameter(
             *          name="expand",
             *          in="query",
             *          description="Which mainsections relations fields should be present in response. By default these relations fields are missing. Multiple values separated by comma.",
             *          enum={
             *              "file",
             *              "cases",
             *              "cases.file",
             *              "cases.activeFeastCase",
             *              "cases.crowdfunding",
             *              "cases.limitedTime",
             *              "cases.limitedQuantity",
             *          },
             *          required=false,
             *          type="string",
             *      ),
             *
             *      @SWG\Response(
             *          response=200,
             *          description="Main sections collection response",
             *          @SWG\Schema(ref="#/definitions/MainSection")
             *      ),
             *
             *      @SWG\Response(
             *          response=405,
             *          description="Method Not Allowed",
             *          @SWG\Schema(ref="#/definitions/405")
             *      ),
             * )
             */
            'index' => [
                'class' => IndexAction::class,
                'modelClass' => MainSection::class,
                'dataFilter' => [
                    'class' => ActiveDataFilter::class,
                    'searchModel' => MainSectionSearch::class,
                ],
                'prepareDataProvider' => fn() => $this->prepareIndexProvider(),
            ],
        ];
    }

    protected function verbs(): array
    {
        return [
            'index' => ['GET'],
        ];
    }

    protected function getSerializerConfig(string $action): array
    {
        $config = parent::getSerializerConfig($action);

        $config['defaultExpand'] = self::INDEX_EXPAND;

        return $config;
    }

    private function prepareIndexProvider(): ActiveDataProvider
    {
        $searchModel = new MainSectionSearch();
        $searchModel->load(Yii::$app->request->queryParams, 'filter');
        $searchModel->is_visible = true; // Do not display hidden sections despite filter.

        return $this->prepareExpandedProvider($searchModel->search(), self::INDEX_EXPAND, self::INDEX_EXPAND);
    }
}
