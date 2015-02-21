<?php namespace MODX\Shell\Command\User;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;
use modMail;
use modX;

/**
 * A command to help reset a user password
 */
class ResetPassword extends BaseCmd
{
    const MODX = true;

    protected $name = 'user:resetpassword';
    protected $description = 'Reset the given user password';

    protected function process()
    {
        $pk = $this->argument('identifier');
        if (is_numeric($pk)) {
            // Assume an ID was given
            $key = 'id';
        } elseif (strpos($pk, '@') !== false) {
            // Assume an email
            $key = 'Profile.email';
        } else {
            $key = 'username';
        }

        /** @var \modUser $user */
        $user = $this->modx->getObjectGraph('modUser', array('Profile' => array()), array($key => $pk));
        if (!$user) {
            return $this->error("No user found with {$key} : {$pk}");
        }

        /**
         * @see \SecurityLoginManagerController::handleForgotLogin
         */
        $email = $user->Profile->get('email');
        $activationHash = md5(uniqid(md5($email . '/' . $user->get('id')), true));

        $this->modx->getService('registry', 'registry.modRegistry');
        $this->modx->registry->getRegister('user', 'registry.modDbRegister');
        $this->modx->registry->user->connect();
        $this->modx->registry->user->subscribe('/pwd/reset/');
        $this->modx->registry->user->send('/pwd/reset/', array(md5($user->get('username')) => $activationHash), array('ttl' => 86400));

        $newPassword = $user->generatePassword();

        $user->set('cachepwd', $newPassword);
        $user->save();

        // send activation email
        $message = $this->modx->getOption('forgot_login_email');
        $placeholders = $user->toArray();
        $placeholders['url_scheme'] = $this->modx->getOption('url_scheme');
        $placeholders['http_host'] = $this->modx->getOption('http_host');
        $placeholders['manager_url'] = $this->modx->getOption('manager_url');
        $placeholders['hash'] = $activationHash;
        $placeholders['password'] = $newPassword;
        foreach ($placeholders as $k => $v) {
            if (is_string($v)) {
                $message = str_replace('[[+'.$k.']]', $v, $message);
            }
        }
        // Parse any modx tags
        $this->modx->setPlaceholders($placeholders);
        $this->modx->getParser()->processElementTags('', $message);

        $this->modx->getService('mail', 'mail.modPHPMailer');
        $this->modx->mail->set(modMail::MAIL_BODY, $message);
        $this->modx->mail->set(modMail::MAIL_FROM, $this->modx->getOption('emailsender'));
        $this->modx->mail->set(modMail::MAIL_FROM_NAME, $this->modx->getOption('site_name'));
        $this->modx->mail->set(modMail::MAIL_SENDER, $this->modx->getOption('emailsender'));
        $this->modx->mail->set(modMail::MAIL_SUBJECT, $this->modx->getOption('emailsubject'));
        $this->modx->mail->address('to', $email, $user->get('fullname'));
        $this->modx->mail->address('reply-to', $this->modx->getOption('emailsender'));
        $this->modx->mail->setHTML(true);
        if (!$this->modx->mail->send()) {
            $this->error("Error while trying to send the reset password to {$email}");
        } else {
            $this->info("Reset password sent to <comment>{$email}</comment>");
        }
        $this->modx->mail->reset();
    }

    protected function getArguments()
    {
        return array(
            array(
                'identifier',
                InputArgument::REQUIRED,
                'The user ID, username or email.'
            ),
        );
    }
}
