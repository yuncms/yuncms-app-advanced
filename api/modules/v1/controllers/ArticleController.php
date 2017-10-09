<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace api\modules\v1\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use api\modules\v1\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\models\Article;
use yuncms\collection\models\Collection;

/**
 * Class ArticleController
 * @package api\modules\v1\controllers
 */
class ArticleController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Article';

    /**
     * Declares the allowed HTTP verbs.
     * Please refer to [[VerbFilter::actions]] on how to declare the allowed verbs.
     * @return array the allowed HTTP verbs.
     */
    protected function verbs()
    {
        return [
            'support' => ['POST'],
            'collection' => ['POST'],
        ];
    }

    /**
     * 文章收藏
     * @param int $id
     * @return array
     */
    public function actionCollection($id)
    {
        $model = $this->findModel($id);
        /*不能多次收藏*/
        $userCollect = Collection::findOne(['user_id' => Yii::$app->user->id, 'model' => get_class($model), 'model_id' => $id]);
        if ($userCollect) {
            $userCollect->delete();
            $model->updateCounters(['collections' => -1]);
            return ['status' => 'uncollect'];
        }

        $data = [
            'user_id' => Yii::$app->user->id,
            'model_id' => $id,
            'model' => get_class($model),
            'subject' => $model->title,
        ];

        $collect = Collection::create($data);
        if ($collect) {
            $model->updateCounters(['collections' => 1]);
            $collect->save();
        }
        return ['status' => 'collected'];
    }

    /**
     * 文章点赞
     * @param int $id
     * @return array
     */
    public function actionSupport($id)
    {
        $model = $this->findModel($id);
        $support = Support::findOne(['user_id' => Yii::$app->user->id, 'model' => get_class($model), 'model_id' => $id]);
        if ($support) {
            return ['status' => 'failed'];
        }
        return ['status' => 'success'];
    }


    /**
     * 获取 Model
     * @param int $id
     * @return Article
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        if (($model = Article::findOne($id)) != null) {
            return $model;
        } else {
            throw new NotFoundHttpException("Object not found: $id");
        }
    }

    /**
     * 检查当前用户的权限
     *
     * This method should be overridden to check whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param string $action the ID of the action to be executed
     * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     * @throws ForbiddenHttpException if the user does not have access
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action === 'update' || $action === 'delete') {
            if ($model->user_id !== Yii::$app->user->id) {
                throw new ForbiddenHttpException(sprintf('You can only %s articles that you\'ve created.', $action));
            }
        }
    }
}