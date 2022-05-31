<?php

namespace common\models;

use common\enums\MainSectionTypeEnum;
use common\models\traits\SafeSavingTrait;
use common\queries\MainSectionQuery;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the model class for table "main_sections".
 *
 * @property int $id
 * @property string $name Название Секции
 * @property string $type Тип Секции
 * @property int $file_id Картинка секции
 * @property int $is_visible Активность
 * @property int|null $position Позииция
 *
 * @property CaseMainSection[] $CaseMainSection
 */
class MainSection extends \yii\db\ActiveRecord
{
    use SafeSavingTrait;

    public ?int $next_id = null;
    public ?int $previous_id = null;

    public static function find(): MainSectionQuery
    {
        return Yii::createObject(MainSectionQuery::class, [static::class]);
    }

    public static function tableName(): string
    {
        return '{{%main_sections}}';
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['is_visible'], 'boolean'],
            [['file_id'], 'integer', 'min' => 1],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['file_id' => 'id']],
            [['position'], 'integer', 'min' => 0],
            [['type'], 'in', 'range' => MainSectionTypeEnum::all()],
            [['name'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'is_visible' => 'Is Visible',
            'position' => 'Position',
            'file_id' => 'File',
            'type' => 'Type',
        ];
    }

    public function beforeSave($insert): bool
    {
        if ($insert && !$this->position) {
            $this->position = $this->getNextPosition();
        }

        return parent::beforeSave($insert);
    }

    public function extraFields(): array
    {
        return [
            'cases',
            'file',
        ];
    }

    public function getMainSectionCases(): ActiveQuery
    {
        return $this->hasMany(CaseMainSection::class, ['section_id' => 'id']);
    }

    public function getFile(): ActiveQuery
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }

    public function getCases(): ActiveQuery
    {
        return $this->hasMany(Cases::class, ['id' => 'case_id'])
            ->viaTable(CaseMainSection::tableName(), ['section_id' => 'id'])
            ->leftJoin(CaseMainSection::tableName() . ' via', ['via.case_id' => new Expression(Cases::tableName() . '.id')])
            ->andWhere(['is_hidden' => false])
            ->orderBy('via.position')
        ;
    }

    private function getNextPosition(): int
    {
        $max = (int)self::find()->count();

        return $max + 1;
    }
}
