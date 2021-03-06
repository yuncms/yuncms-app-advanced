<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace api\modules\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\IndexAction;
use yii\web\ForbiddenHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yuncms\user\models\UserProfile;
use yuncms\attention\models\Attention;
use api\modules\v1\models\User;
use api\modules\v1\ActiveController;
use api\modules\v1\models\AvatarForm;
use api\modules\v1\models\Authentication;

/**
 * 用户接口
 * @package api\modules\v1\controllers
 */
class UserController extends ActiveController
{

    /**
     * @var string the model class name. This property must be set.
     */
    public $modelClass = 'api\modules\v1\models\User';

    /**
     * @var string the scenario used for updating a model.
     * @see \yii\base\Model::scenarios()
     */
    //public $updateScenario = 'update';

    /**
     * 定义操作
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        // disable the "delete" and "create" actions
        unset($actions['delete'], $actions['create']);
        return $actions;
    }

    /**
     * Prepares the data provider that should return the requested collection of the models.
     *
     * @param IndexAction $action
     * @param mixed $filter
     * @return ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function prepareDataProvider(IndexAction $action, $filter)
    {
        $query = User::find();
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        return Yii::createObject([
            'class' => ActiveDataProvider::className(),
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                    'id' => SORT_ASC,
                ]
            ],
        ]);
    }

    /**
     * Declares the allowed HTTP verbs.
     * Please refer to [[VerbFilter::actions]] on how to declare the allowed verbs.
     * @return array the allowed HTTP verbs.
     */
    protected function verbs()
    {
        return [
            'avatar' => ['POST'],
            'search' => ['GET'],
            'me' => ['GET'],
            'profile' => ['GET', 'PUT', 'PATCH'],
            'authentication' => ['GET', 'POST', 'PUT', 'PATCH'],
            'follow' => ['POST', 'DELETE'],
            'friends' => ['GET'],
            'followers' => ['GET'],
            'friendships' => ['GET'],
            'bind-mobile' => ['POST'],
        ];
    }

    /**
     * 用户搜索
     * @param string $username
     * @return ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSearch($username)
    {
        $query = User::find()->where(['like', 'username', $username])->orWhere(['like', 'nickname', $username]);

        return Yii::createObject([
            'class' => ActiveDataProvider::className(),
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                    'id' => SORT_ASC,
                ]
            ],
        ]);
    }

    /**
     * 读取用户扩展数据
     * @return \yuncms\user\models\UserExtra
     */
    public function actionExtra()
    {
        /** @var \yuncms\user\models\User $user */
        $user = Yii::$app->user->identity;
        return $user->extra;
    }

    /**
     * 获取个人基本资料
     * @return array
     */
    public function actionMe()
    {
        /** @var \yuncms\user\models\User $user */
        $user = Yii::$app->user->identity;
        return [
            'id' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'mobile_confirmed_at' => $user->mobile_confirmed_at,
            'isAuthentication' => Authentication::isAuthentication($user->id),
            'faceUrl' => $user->getAvatar()
        ];
    }

    /**
     * 获取个人扩展资料
     * @return UserProfile
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionProfile()
    {
        if (($model = UserProfile::findOne(['user_id' => Yii::$app->user->identity->getId()])) !== null) {
            if (!Yii::$app->request->isGet) {
                $model->load(Yii::$app->getRequest()->getBodyParams(), '');
                if ($model->save() === false && !$model->hasErrors()) {
                    throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
                }
            }
            return $model;
        } else {
            throw new NotFoundHttpException (Yii::t('yii', 'The requested page does not exist.'));
        }
    }

    /**
     * 关注别人
     * @param integer $id
     * @return Attention
     * @throws MethodNotAllowedHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionFollow($id)
    {
        $user = $this->findModel($id);
        if (Yii::$app->request->isPost) {
            /** @var Attention $model */
            if (($model = Attention::find()->where(['user_id' => Yii::$app->user->getId(), 'model_class' => \yuncms\user\models\User::className(), 'model_id' => $user->id])->one()) != null) {
                Yii::$app->getResponse()->setStatusCode(200);
                return $model;
            } else {
                $model = new Attention();
                $model->load(Yii::$app->request->post(), '');
                $model->model_class = \yuncms\user\models\User::className();
                $model->user_id = Yii::$app->user->getId();
                $model->model_id = $user->id;
                if ($model->save() === false && !$model->hasErrors()) {
                    throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
                }
                Yii::$app->getResponse()->setStatusCode(201);
                return $model;
            }
        } else if (Yii::$app->request->isDelete) {
            if (($model = Attention::find()->where(['user_id' => Yii::$app->user->getId(), 'model_class' => \yuncms\user\models\User::className(), 'model_id' => $user->id])->one()) != null) {
                if (($model->delete()) != false) {
                    Yii::$app->getResponse()->setStatusCode(204);
                } elseif (!$model->hasErrors()) {
                    throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
                }
            } else {
                throw new NotFoundHttpException("Object not found.");
            }
        } else {
            throw new MethodNotAllowedHttpException();
        }
    }

    /**
     * 获取用户的关注列表
     * @param string $id
     * @return ActiveDataProvider
     */
    public function actionFriends($id)
    {
        $user = User::findOne($id);
        $query = Attention::find()->where(['user_id' => $user->id, 'model_class' => User::className()]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    /**
     * 获取用户的粉丝列表
     * @param int $id
     * @return ActiveDataProvider
     */
    public function actionFollowers($id)
    {
        $user = User::findOne($id);
        $query = Attention::find()->where(['model_class' => User::className(), 'model_id' => $user->id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    /**
     * 获取两个用户之间是否存在关注关系
     * @param int $source_id 源用户的UID
     * @param int $target_id 目标用户的UID
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionFriendships($source_id, $target_id)
    {
        $source = $this->findModel($source_id);
        $target = $this->findModel($target_id);
        return [
            'target' => [
                "id" => $source->id,
                "username" => $source->username,
                "following" => Attention::find()->where(['user_id' => $source->id, 'model_class' => User::className(), 'model_id' => $target->id])->exists()

            ],
            'source' => [
                "id" => $target->id,
                "screen_name" => $target->username,
                "following" => Attention::find()->where(['user_id' => $target->id, 'model_class' => User::className(), 'model_id' => $source->id])->exists()
            ],
        ];
    }

    /**
     * 实名认证
     * @return null|Authentication
     * @throws MethodNotAllowedHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAuthentication()
    {
        if (Yii::$app->request->isPost) {
            if (($model = Authentication::findOne(['user_id' => Yii::$app->user->getId()])) === null) {
                $model = new Authentication();
                $model->scenario = Authentication::SCENARIO_CREATE;
            } else {
                $model->scenario = Authentication::SCENARIO_UPDATE;
            }
            $model->load(Yii::$app->getRequest()->getBodyParams(), '');
            if (($model->save()) != false) {
                $response = Yii::$app->getResponse();
                $response->setStatusCode(201);
                return $model;
            } elseif (!$model->hasErrors()) {
                throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
            }
            return $model;
        } else if (Yii::$app->request->isGet) {
            if (($model = Authentication::findOne(['user_id' => Yii::$app->user->getId()])) == null) {
                $model = new Authentication(['registrationPolicy' => true,]);
                $model->save();
            }
            return $model;
        }
        throw new MethodNotAllowedHttpException();
    }

    /**
     * 上传头像
     * @return AvatarForm|bool
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAvatar()
    {
        $model = new AvatarForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if (($user = $model->save()) != false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(200);
            return $user;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }
        return $model;
    }

    /**
     * 获取用户
     * @param int $id
     * @return User
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        if (($model = User::findOne($id)) != null) {
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
        if ($action === 'update' && $model->id !== Yii::$app->user->getId()) {
            throw new ForbiddenHttpException(sprintf('You can only %s users that you\'ve created.', $action));
        } else if ($action === 'delete') {
            throw new ForbiddenHttpException(sprintf('You can only %s users that you\'ve deleted.', $action));
        }
    }
}