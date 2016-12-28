<?php
namespace StandardAuth\Mailer;

use Cake\Mailer\Email;

class AuthEmail extends Email
{
    protected $subjectRequestApprovalMail = '[TEST]アカウント登録承認メール';
    protected $subjectResetedPassward     = '[TEST]アカウントパスワードリセット';

    /**
     * sendRequestApproval
     * アカウント承認のリクエストメールの送付
     * @author ito
     */
    public function sendRequestApproval(
        $toMail,
        $name,
        $url
    ){
        $this->template('StandardAuth.request_approval')
             ->emailFormat('text')
             ->to($toMail)
             ->subject($this->subjectRequestApprovalMail)
             ->viewVars([
                'name'        => $name,
                'approvalUrl' => $url
             ])
             ->send();
    }

    /**
     * sendResetedPassward
     * リセットしたランダムパスワードの送付
     * @author ito
     */
    public function sendResetedPassward(
        $toMail,
        $name,
        $newPassword
    ){
        $result = $this->template('StandardAuth.reseted_passward')
             ->emailFormat('text')
             ->to($toMail)
             ->subject($this->subjectResetedPassward)
             ->viewVars([
                'name'        => $name,
                'newPassword' => $newPassword
             ])
             ->send();
    }

}
