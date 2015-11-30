<?php

/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
class Shopware_Plugins_Frontend_SwagPasswordRestore_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql $db
     */
    private $db;

    /**
     * This function returns the name of the plugin
     *
     * @return string
     * */
    public function getLabel()
    {
        return 'Neue Passwort vergessen Funktion';
    }

    /**
     * Returns the current version of the plugin.
     *
     * @return mixed
     * @throws Exception
     */
    public function getVersion()
    {
        $info = json_decode(file_get_contents(__DIR__ . '/plugin.json'), true);

        if ($info) {
            return $info['currentVersion'];
        } else {
            throw new Exception('The plugin has an invalid version file.');
        }
    }

    /**
     * Returns an array with some information about the plugin.
     *
     * @return array
     */
    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
            'description' => file_get_contents($this->Path() . 'info.txt'),
            'link' => 'http://www.shopware.com/'
        );
    }

    /**
     * initialise the PDO connection
     */
    public function afterInit()
    {
        $this->db = Shopware()->Db();
    }


    /**
     * This function calls all necessary functions to install the plugin
     *
     * @return boolean
     * */
    public function install()
    {
        $this->updateDb();

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_Password',
            'onGetControllerPathFrontendPassword'
        );
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Frontend_Account', 'onPostDispatchAccount');

        $this->createConfig();

        return true;
    }

    /**
     * This function creates the backend configuration of the plugin
     *
     * @return boolean
     * */
    public function createConfig()
    {
        $form = $this->Form();

        $form->setElement(
            'boolean',
            'activatePlugin',
            array(
                'label' => 'Neue Funktion nutzen',
                'value' => true,
                'scope' => Shopware\Models\Config\Element::SCOPE_SHOP
            )
        );

        return true;
    }

    /**
     * This function uninstalls the plugin
     *
     * @return boolean
     * */
    public function uninstall()
    {
        $sql = "ALTER TABLE `s_core_optin` DROP `type`";
        $this->db->query($sql);

        return true;
    }

    /**
     * This function extends a new Template. This Template replaces the original password forgotten form with
     * a extended Version.
     *
     * @param Enlight_Controller_EventArgs $args
     */
    public function onPostDispatchAccount(Enlight_Controller_EventArgs $args)
    {
        if (!$this->Config()->get('activatePlugin')) {
            return;
        }

        /** @var Shopware_Controllers_Frontend_Account $controller */
        $controller = $args->getSubject();
        $view = $controller->View();

        $this->registerComponents();

        if ($controller->Request()->getActionName() == 'password') {
            $view->extendsTemplate('frontend/plugins/swag_password_restore/index.tpl');
        } else {
            $view->extendsTemplate('frontend/plugins/swag_password_restore/messages.tpl');
        }
    }

    /**
     * This function updates the Database.
     * First it adds two columns, one in the s_core_config_mails and one in the s_core_optin table. This is
     * necessary because those tables were changed in SW 5.0.4.
     * Than it implements the new password restore E-Mail Template
     *
     * @return boolean
     */
    public function updateDb()
    {
        $sql = "ALTER TABLE `s_core_optin` ADD `type` VARCHAR(255) NULL DEFAULT '' AFTER `id`";
        $this->db->query($sql);

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_mails` (`id`, `stateId`, `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `mailtype`, `context`) VALUES (NULL, NULL, 'sPLUGCONFIRMPASSWORDCHANGE', '{config name=mail}', '{config name=shopName}', 'Passwort vergessen - Passwort zurücksetzen', '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHallo,

im Shop {sShopURL} wurde eine Anfrage gestellt, um Ihr Passwort zurück zu setzen.

Bitte bestätigen Sie den unten stehenden Link, um ein neues Passwort zu definieren.

{sUrlReset}

Dieser Link ist nur für die nächsten 2 Stunden gültig. Danach muss das Zurücksetzen des Passwortes erneut beantragt werden.

Falls Sie Ihr Passwort nicht zurücksetzen möchten, ignorieren Sie diese E-Mail - es wird dann keine Änderung vorgenommen.

{config name=address}\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', '', '0', '', '2', '');
EOD;
        $this->db->query($sql);

        return true;
    }

    /**
     * This function returns the path of the controller.
     *
     * @return string
     */
    public function onGetControllerPathFrontendPassword()
    {
        $this->registerComponents();

        return $this->Path() . '/Controllers/Frontend/Password.php';
    }

    /**
     * This function registers the new Views directory.
     *
     */
    protected function registerComponents()
    {
        $this->Application()->Template()->addTemplateDir($this->Path() . 'Views/');

        $this->Application()->Snippets()->addConfigDir($this->Path() . 'Snippets/');
    }
}
