<?php
namespace StandardAuth\Controller\Component;

use Cake\Controller\Component\AuthComponent;
use Cake\Utility\Hash;

class StandardAuthComponent extends AuthComponent
{
    /**
     * approval
     * @author ito
     */
    public function approval($id, $hash)
    {
        if (empty($this->_config['authenticate'])) {
            return null;
        };

        $userModel  = $this->getAuthenticateUserModel();
        // ハッシュキーのチェック
        $controller = $this->_registry->getController();
        $reault     = $controller->{$userModel}->hashCertification($id, $hash);
        return $reault;
    }

    /**
     * resetPassword
     * @author ito
     */
    public function resetPassword()
    {
        if (empty($this->_config['authenticate'])) {
            return null;
        };

        $userModel = $this->getAuthenticateUserModel();
        $controller = $this->_registry->getController();
        $entity = $controller->{$userModel}->newEntity();
        if ($controller->request->is('post')) {
            $data = $controller->request->data;
            $entity = $controller->{$userModel}->patchEntity($entity, $data, ['validator' => 'resetPassword']);
            if (empty($entity->errors())) {
                $mail = $data[$controller->{$userModel}->config('mailField')];
                $result = $controller->{$userModel}->resetPassword($mail);
                if ($result) {
                    $controller->redirect($this->config('loginAction'));
                }
            }
        }

        $controller->set(compact('entity'));
    }

    /**
     * getAuthenticateUserModel
     * @author ito
     */
    private function getAuthenticateUserModel()
    {
        $authenticate = Hash::normalize((array)$this->_config['authenticate']);
        $userModel    = $authenticate['Form']['userModel'];
        return $userModel;
    }
}
