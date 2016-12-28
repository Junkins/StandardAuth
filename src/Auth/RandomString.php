<?php
namespace StandardAuth\Auth;

class RandomString {

    /**
     * get
     * @author ito
     */
    public function get()
    {
        return str_shuffle('1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }
}
