<?php namespace Kloos\Auth\Components;

use Auth;
use Mail;
use Flash;
use Validator;
use RainLab\User\Models\User;
use Cms\Classes\ComponentBase;

class ResetPassword extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'ResetPassword Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRequestResetLink()
    {
        $rules = [
            'email' => 'required|email|between:6,255',
        ];

        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            Flash::error(trans('kloos.auth::lang.flash.auth.email_validation_error'));
            return;
        }

        $user = User::findByEmail(post('email'));
        if (!$user || $user->is_guest) {
            Flash::error(trans('kloos.auth::lang.flash.auth.email_reset_error'));
            return;
        }

        $code = implode('!', [$user->id, $user->getResetPasswordCode()]);

        $link = $this->makeResetUrl($code);

        $data = [
            'name' => $user->name,
            'username' => $user->username,
            'link' => $link,
            'code' => $code
        ];

        Mail::send('kloos.auth::mail.restore', $data, function ($message) use ($user) {
            $message->to($user->email, $user->full_name);
        });

        Flash::success(trans('kloos.auth::lang.flash.auth.email_send_restore', ['email' => $user->email]));
    }

    public function onResetPassword()
    {
        $rules = [
            'code'     => 'required',
            'password' => 'required|between:' . User::getMinPasswordLength() . ',255'
        ];

        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            Flash::error(trans('kloos.auth::lang.flash.auth.email_reset_error'));
            return;
        }

        /*
         * Break up the code parts
         */
        $parts = explode('!', post('code'));
        if (count($parts) != 2) {
            Flash::error(trans('kloos.auth::lang.flash.auth.email_reset_error'));
            return;
        }

        list($userId, $code) = $parts;

        if (!strlen(trim($userId)) || !strlen(trim($code)) || !$code) {
            Flash::error(trans('kloos.auth::lang.flash.auth.email_reset_error'));
            return;
        }

        if (!$user = Auth::findUserById($userId)) {
            Flash::error(trans('kloos.auth::lang.flash.auth.email_reset_error'));
            return;
        }

        if (!$user->attemptResetPassword($code, post('password'))) {
            Flash::error(trans('kloos.auth::lang.flash.auth.email_reset_error'));
            return;
        }

        Flash::success(trans('kloos.auth::lang.flash.auth.email_restore_complete'));
    }

    public function makeResetUrl($code)
    {
        $params = [
            'code' => $code,
        ];

        return $this->currentPageUrl($params);
    }
}
