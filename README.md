# StandardAuth

## Introduction
Authentication mail function when creating a user.
Password reminder function.

## Setup
1. you load StandardAuthComponent.
```php
<?php
namespace App\Controller

use Cake\Controller\Controller;

/**
 * AdminAppController
 */
class AdminAppController extends Controller
{
  /**
   * initialize
   */
  public function initialize()
  {
      parent::initialize();
      $this->loadComponent('StandardAuth.StandardAuth', [
        'authenticate' => [
            'Form' => [
                'userModel' => 'Admins',
                'fields' => [
                    'username' => 'username',
                    'password' => 'password'
                ],
            ]
        ],
      ]);
  }
}
```

2. you load StandardAuthBehavior.
```php
<?php
namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * AdminsTable
 */
class AdminsTable extends Table
{
  /**
   * initialize
   */
  public function initialize(array $config)
  {
      $this->addBehavior('StandardAuth.StandardAuth');
  }
}
```

3. you define action on Controller.
```php
<?php

    /**
     * approval
     */
    public function approval($id, $hash)
    {
        $this->Admins->get($id);
        $result = $this->StandardAuth->approval($id, $hash);
        if ($result) {
            $this->Flash->success(__('The user has been saved.'));
        } else {
            $this->Flash->error(__('Invalid credentials, try again'));
        }
        return $this->redirect(['action' => 'login']);
    }

    /**
    * reset
    * @author ito
    */
    public function reset()
    {
        $this->viewBuilder()->layout('login');
        $entity = $this->Admins->newEntity();
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $entity = $this->Admins->patchEntity($entity, $data, ['validate' => 'reset']);
            if (
                empty($entity->errors()) &&
                $this->Admins->resetPassword($entity->email)
            ) {
                $this->Flash->success(__('The user has been saved.'));
                return $this->redirect(['action' => 'login']);
            } else {
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
        }

        $this->set(compact('entity'));
        $this->set('_serialize', ['entity']);
    }
}
```
