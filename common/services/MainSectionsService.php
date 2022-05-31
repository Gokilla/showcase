<?php

namespace common\services;

use common\models\CaseMainSection;
use common\models\MainSection;
use Yii;

class MainSectionsService
{
    /**
     * @throws \yii\db\Exception
     */
    public function saveSectionCases(MainSection $section, array $cases): void
    {
        CaseMainSection::deleteAll(['section_id' => $section->id]);

        foreach ($cases as $order => $caseId) {
            ++$order;
            $insert[] = [
                $section->id,
                $caseId,
                $order,
            ];
        }

        if (empty($insert)) {
            return;
        }

        Yii::$app->db->createCommand()->batchInsert(CaseMainSection::tableName(), [
            'section_id',
            'case_id',
            'position',
        ], $insert)->execute();
    }

    public function swapPositions(MainSection $firstModel, MainSection $secondModel): void
    {
        // Store current positions.
        $firstPos = $firstModel->position;
        $secondPos = $secondModel->position;

        // Dummy null value to avoid uniqueness fail.
        $firstModel->safeChange('position', null);

        // Set positions vise-versa.
        $secondModel->safeChange('position', $firstPos);
        $firstModel->safeChange('position', $secondPos);
    }
}
