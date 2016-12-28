<?php
namespace StandardAuth\Model\Behavior;

use ArrayObject;
use Cake\Auth\WeakPasswordHasher;
use Cake\Event\Event;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Behavior;
use Cake\I18n\FrozenTime;
use Cake\Routing\Router;
use StandardAuth\Auth\RandomString;
use StandardAuth\Mailer\AuthEmail;

class StandardAuthBehavior extends Behavior
{

    // URlとして使用できない文字列
    private $disabledUrlString = [
        '<', '>', '#', '"', '%', '{',
        '}', '|', '\\', '^', '[', ']',
        '`', ':', '?', '#', '/', '?',
        '@', '!', '$', '&', "'", '(',
        ')', '*', '+', ',', ';', '=',
    ];

    protected $_defaultConfig = [
        'activeField'              => 'active',
        'nameField'                => 'name',
        'mailField'                => 'email',
        'passwordField'            => 'password',
        'resetedField'             => 'reseted',
        'tmpHashField'             => 'tmp_hash',
        'tmpHashIssuanceTimeField' => 'tmp_hash_issuance_time',
        'hashAvailableHour'        => 24,
        'approvalController'       => 'Users',
        'approvalAction'           => 'approval',
    ];

    /**
     * beforeSave
     * @author ito
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        if ($entity->isNew()) {
            $entity->{$this->_config['activeField']}  = false; // 初期登録時は未有効化
            $entity->{$this->_config['tmpHashField']} = $this->getHashKey(); // ハッシュキー
            $entity->{$this->_config['tmpHashIssuanceTimeField']}  = new FrozenTime(); // ハッシュキーの発行時間
        }
    }

    /**
     * afterSave
     * @author ito
     */
    public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        if ($entity->isNew()) {
            $approvalUrl = $this->getApprovalUrl($entity);

            // 承認依頼メールの送付
            $mailer = $this->getMailer();
            $mailer->sendRequestApproval(
                $entity->{$this->_config['mailField']},
                $entity->{$this->_config['nameField']},
                $approvalUrl
            );
        }
    }

    ####################################################################################

    /**
     * hashCertification
     * @author ito
     */
    public function hashCertification($id, $hash)
    {
        $activeField              = $this->_config['activeField'];
        $tmpHashField             = $this->_config['tmpHashField'];
        $tmpHashIssuanceTimeField = $this->_config['tmpHashIssuanceTimeField'];
        $hashAvailableHour        = $this->_config['hashAvailableHour'];

        $now = new FrozenTime();
        $availableCriteriaTime = $now->subDays($hashAvailableHour);

        // 対象のデータが存在するかチェック
        $alias = $this->_table->alias();
        $query = $this->_table->find();
        $query->where([
            $alias . '.id' => $id,
            $alias . '.' . $activeField  => false,
            $alias . '.' . $tmpHashField => $hash,
            $alias . '.' . $tmpHashIssuanceTimeField . ' >'=> $availableCriteriaTime,
        ]);

        // 対象のユーザーが存在しない場合
        if ($query->isEmpty()) {
            return false;
        }

        // 有効化
        $updated = $query->first();
        $updated->{$activeField} = true;
        return $this->_table->save($updated);
    }

    /**
     * resetPassword
     * @author ito
     */
    public function resetPassword($email)
    {
        $mailField     = $this->_config['mailField'];
        $nameField     = $this->_config['nameField'];
        $passwordField = $this->_config['passwordField'];
        $resetedField  = $this->_config['resetedField'];

        $alias  = $this->_table->alias();
        $query  = $this->_table->find();
        $query->where([$alias . '.' . $mailField => $email]);
        $entity = $query->first();

        // 新しいパスワードの発行
        $newPassword = $this->getNewPassward();
        $entity->{$this->_config['passwordField']} = $newPassword;
        $entity->{$this->_config['resetedField']}  = true;

        $result = $this->_table->save($entity);
        // メール送信
        $mailer = $this->getMailer();
        $mailer->sendResetedPassward(
                $entity->{$mailField},
                $entity->{$nameField},
                $newPassword
            );

        return $result;
    }

    ####################################################################################

    /**
     * getHashKey
     * ハッシュキーの生成
     * @author ito
     */
    protected function getHashKey()
    {
        // 文字列をランダムにシャッフルする
        $source  = (new RandomString)->get();
        $source .= uniqid(rand());

        // ハッシュキーはURLに使用されるので。URLに認められていない文字を削除
        $hash = (new WeakPasswordHasher)->hash($source);

        foreach ($this->disabledUrlString as $string) {
            if ( $string != '%' ) {
                $hash = str_replace('%' . $string . '%', '', $hash);
            } else {
                $hash = str_replace('/' . $string . '/', '', $hash);
            }
        }

        return $hash;
    }

    /**
     * getApprovalUrl
     * 承認用パスワード作成
     * @author ito
     */
    protected function getApprovalUrl($entity)
    {
        // 絶対パスで発行
        return Router::url([
                'controller' => $this->_config['approvalController'],
                'action'     => $this->_config['approvalAction'],
                $entity->id,
                $entity->{$this->_config['tmpHashField']},
            ], true);
    }

    /**
     * getNewPassward
     * @author ito
     */
    protected function getNewPassward()
    {
        // 16文字のランダム文字列
        return substr((new RandomString)->get(), 0, 15);
    }

    /**
     * getMailer
     * メーラーオブジェクトの取得
     * @author ito
     */
    protected function getMailer()
    {
        return new AuthEmail('default');
    }

    ####################################################################################
}

