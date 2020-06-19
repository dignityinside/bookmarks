<?php

namespace common\models;

use common\components\behaviors\TimestampBehavior;
use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "categories".
 *
 * @property int $id
 * @property string $name
 * @property int $board_id
 * @property string|null $description
 * @property string|null $color color HEX-code
 * @property string|null $icon  fas fa-video
 * @property int $sort          Sorting order
 * @property string $created_at
 * @property string $updated_at
 *
 * relations
 * @property Board $board
 * @property Link[] $links
 *
 * getters
 * @property-read string $fName
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%categories}}';
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return [
            // by default allowed to edit only this fields
            static::SCENARIO_DEFAULT => ['name', 'board_id', 'description', 'color', 'icon'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // required
            [['name', 'board_id'], 'required'],
            // integer
            [['board_id'], 'integer'],
            // string max
            [['name', 'description', 'icon'], 'string', 'max' => 255],
            // string length
            [['color'], 'string', 'min' => 6, 'max' => 7],
            // exist
            [['board_id'], 'exist', 'targetRelation' => 'board'],
        ];
    }

    /**
     * Board relation
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBoard()
    {
        return $this->hasOne(Board::class, ['id' => 'board_id']);
    }

    /**
     * Links relation
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLinks()
    {
        return $this->hasMany(Link::class, ['category_id' => 'id'])->inverseOf('category');
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            'sortBehavior' => [
                'class' => \demi\sort\SortBehavior::class,
                'sortConfig' => [
                    'sortAttribute' => 'sort',
                    'condition' => static function (\yii\db\Query $query, self $model) {
                        $query->andWhere(['board_id' => $model->board_id]);
                    },
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        if ($this->color) {
            $this->color = ltrim($this->color, '#');
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        // Remove related links
        foreach ($this->links as $link) {
            $link->delete();
        }

        parent::afterDelete();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'board_id' => 'Board ID',
            'description' => 'Description',
            'color' => 'Color',
            'icon' => 'Icon',
            'sort' => 'Sorting order',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Return HTML-encoded name
     *
     * @return string
     */
    public function getFName()
    {
        return Html::encode($this->name);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\query\CategoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\CategoryQuery(get_called_class());
    }
}