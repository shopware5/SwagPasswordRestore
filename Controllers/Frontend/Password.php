<?php

use Shopware\Models\Customer\Customer;

class Shopware_Controllers_Frontend_Password extends Enlight_Controller_Action
{
    /**
     * @var sAdmin
     */
    protected $admin;

    /**
     * Init controller method
     */
    public function init()
    {
        $this->admin = Shopware()->Modules()->Admin();
    }

    /**
     * Send new account password
     */
    public function passwordAction()
    {
        $this->View()->assign('sTarget', $this->Request()->getParam('sTarget'));

        if ($this->Request()->isPost()) {
            $errors = $this->sendResetPasswordConfirmationMail($this->Request()->getParam('email'));
            if (!empty($errors['sErrorMessages'])) {
                $this->View()->assign('sFormData', $this->Request()->getPost());
                $this->View()->assign('sErrorFlag', $errors['sErrorFlag']);
                $this->View()->assign('sErrorMessages', $errors['sErrorMessages']);
            } else {
                $this->View()->assign('sSuccess', true);
            }
        }
    }

    /**
     * Send a mail asking the customer, if he actually wants to reset his password
     *
     * @param string $email
     * @return array
     */
    public function sendResetPasswordConfirmationMail($email)
    {
        $snippets = Shopware()->Snippets()->getNamespace('frontend/account/password');

        if (empty($email)) {
            return array('sErrorMessages' => array($snippets->get('ErrorForgotMail')));
        }

        $userID = Shopware()->Modules()->Admin()->sGetUserByMail($email);
        if (empty($userID)) {
            return array('sErrorMessages' => array($snippets->get('ErrorForgotMailUnknown')));
        }

        $hash = \Shopware\Components\Random::getAlphanumericString(32);

        $router = $this->Front()->Router();
        $context = array(
            'sUrlReset' => $router->assemble(array('controller' => 'Password', 'action' => 'resetPassword', 'hash' => $hash)),
            'sUrl'      => $router->assemble(array('controller' => 'Password', 'action' => 'resetPassword')),
            'sKey'      => $hash
        );

        // Send mail
        $mail = Shopware()->TemplateMail()->createMail('sPLUGCONFIRMPASSWORDCHANGE', $context);
        $mail->addTo($email);
        try {
            $mail->send();
        } catch (Exception $e) {
            return array('sErrorMessages' => array($snippets->get('ErrorForgotMailUnknown')));
        }

        // Add the hash to the optin table
        $sql = "INSERT INTO `s_core_optin` (`type`, `datum`, `hash`, `data`) VALUES ('password', NOW(), ?, ?)";
        Shopware()->Db()->query($sql, array($hash, $userID));

        return array();
    }

    /**
     * Shows the reset password form and triggers password reset on submit
     */
    public function resetPasswordAction()
    {
        $hash = $this->Request()->getParam('hash', null);
        $newPassword = $this->Request()->getParam('password', null);
        $passwordConfirmation = $this->Request()->getParam('passwordConfirmation', null);

        $this->View()->assign('hash', $hash);

        if (!$this->Request()->isPost()) {
            return;
        }

        list($errors, $errorMessages) = $this->validatePasswordResetForm($hash, $newPassword, $passwordConfirmation);

        $customerModel = null;

        if (empty($errors)) {
            try {
                $customerModel = $this->resetPassword($hash, $newPassword);
            } catch (\Exception $e) {
                $errorMessages[] = $e->getMessage();
            }
        }

        if (!empty($errorMessages)) {
            $this->View()->assign('sErrorFlag', $errors);
            $this->View()->assign('sErrorMessages', $errorMessages);

            return;
        }

        // Perform a login for the user and redirect him to his account
        $this->admin->sSYSTEM->_POST['email'] = $customerModel->getEmail();
        $this->admin->sLogin();

        $target = $this->Request()->getParam('sTarget', 'account');

        $this->redirect(
            array(
                'controller' => $target,
                'action' => 'index',
                'success' => 'resetPassword'
            )
        );
    }

    /**
     * Validates the data of the password reset form
     *
     * @param string $hash
     * @param string $newPassword
     * @param string $passwordConfirmation
     * @return array
     */
    public function validatePasswordResetForm($hash, $newPassword, $passwordConfirmation)
    {
        $errors = array();
        $errorMessages = array();
        $resetPasswordNamespace = Shopware()->Snippets()->getNamespace('frontend/account/reset_password');
        $frontendNamespace = Shopware()->Snippets()->getNamespace('frontend');

        if (empty($hash)) {
            $errors['hash'] = true;
            $errorMessages[] = $resetPasswordNamespace->get(
                'PasswordResetNewLinkError',
                'Confirmation link not found. Note that the confirmation link is only valid for 2 hours. After that you have to request a new confirmation link.'
            );
        }

        if ($newPassword !== $passwordConfirmation) {
            $errors['password'] = true;
            $errors['passwordConfirmation'] = true;
            $errorMessages[] = $frontendNamespace->get(
                'RegisterPasswordNotEqual',
                'The passwords do not match.'
            );
        }

        if (!$newPassword
            || strlen(trim($newPassword)) == 0
            || !$passwordConfirmation
            || (strlen($newPassword) < Shopware()->Config()->get('sMINPASSWORD'))
        ) {
            $errorMessages[] = $this->View()->fetch(
                'string:' . $frontendNamespace->get(
                    'RegisterPasswordLength',
                    'Your password should contain at least {config name=\"MinPassword\"} characters'
                )
            );
            $errors['password'] = true;
            $errors['passwordConfirmation'] = true;
        }

        return array($errors, $errorMessages);
    }

    /**
     * Performs a password reset based on a given s_core_optin hash
     *
     * @param string $hash
     * @param string $password
     * @return Customer
     * @throws Exception
     */
    public function resetPassword($hash, $password)
    {
        $resetPasswordNamespace = Shopware()->Snippets()->getNamespace('frontend/account/reset_password');

        $em = Shopware()->Models();

        $this->deleteExpiredOptInItems();

        $sql = "SELECT * FROM s_core_optin WHERE hash = ? AND type = ?";
        $confirmRow = Shopware()->Db()->fetchRow($sql, array($hash, 'password'));

        if (!$confirmRow) {
            throw new Exception(
                $resetPasswordNamespace->get(
                    'PasswordResetNewLinkError',
                    'Confirmation link not found. Please check the spelling. Note that the confirmation link is only valid for 2 hours. After that you have to require a new confirmation link.'
                )
            );
        }

        /** @var $customer Customer */
        $customer = $em->find('Shopware\Models\Customer\Customer', $confirmRow['data']);
        if (!$customer) {
            throw new Exception(
                $resetPasswordNamespace->get(
                    sprintf('PasswordResetNewMissingId', $confirmRow),
                    sprintf('Could not find the user with the ID "%s".', $confirmRow['data'])
                )
            );
        }

        // Generate the new password
        /** @var \Shopware\Components\Password\Manager $passwordEncoder */
        $passwordEncoder = Shopware()->PasswordEncoder();

        $encoderName = $passwordEncoder->getDefaultPasswordEncoderName();
        $password = $passwordEncoder->encodePassword($password, $encoderName);

        Shopware()->Db()->query(
            'UPDATE s_user SET password = ?, encoder = ? WHERE id = ?',
            array($password, $encoderName, $customer->getId())
        );

        Shopware()->Db()->query(
            'DELETE FROM s_core_optin WHERE id = ?',
            array($confirmRow['id'])
        );

        return $customer;
    }

    /**
     * Delete old expired password-hashes after two hours
     */
    private function deleteExpiredOptInItems()
    {
        $date = new \DateTime('-2 hours');
        $date = date_format($date, "Y-m-d H:i:s");

        $sql = 'DELETE FROM s_core_optin WHERE datum <= ? AND type = "password"';
        Shopware()->Db()->query($sql, array($date));
    }
}
