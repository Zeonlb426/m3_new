<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use ReCaptcha\ReCaptcha;

final class ReCaptchaRule implements Rule
{
    private $error_msg = '';
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        if (empty($value)) {
            $this->error_msg = ':attribute field is required.';

            return false;
        }

        $url = \config('app.url_recaptcha');
        $privateKey = \config('app.recaptcha_private_key', '6Ldl8J0mAAAAABNyvr015MyDeuDve6JkYIfnyhzs');

        $recaptcha = new ReCaptcha($privateKey);
        $resp = $recaptcha->setExpectedHostname($url)
            ->verify($value, $_SERVER['REMOTE_ADDR']);

        if (!$resp->isSuccess()) {
            $this->error_msg = 'Invalid response from google.';

            return false;
        }

        if ($resp->getScore() < 0.5) {
            $this->error_msg = 'Failed to validate captcha.';

            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->error_msg;
    }
}
