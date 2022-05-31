<?php

namespace backend\models;

use common\models\MainSection;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * MainSectionSearch represents the model behind the search form of `common\models\MainSection`.
 */
class MainSectionSearch extends MainSection
{
    public function rules(): array
    {
        return [
            [['id', 'position'], 'integer'],
            [['is_visible'], 'boolean'],
            [['name'], 'safe'],
        ];
    }

    public function search(array $params = []): ActiveDataProvider
    {
        $query = MainSection::find()->orderBy('position');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'is_visible' => $this->is_visible,
            'position' => $this->position,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
