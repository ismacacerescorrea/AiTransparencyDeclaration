<?php

/**
 * @file classes/form/AiTransparencyDeclarationSettingsForm.php
 *
 * Distributed under the GNU Affero General Public License v3.0.
 *
 * @class AiTransparencyDeclarationSettingsForm
 * @brief Settings for the AI Transparency Declaration plugin.
 */

namespace APP\plugins\generic\aiTransparencyDeclaration\classes\form;

use APP\plugins\generic\aiTransparencyDeclaration\AiTransparencyDeclarationPlugin;
use PKP\form\Form;

class AiTransparencyDeclarationSettingsForm extends Form
{
    public int $_contextId;
    public AiTransparencyDeclarationPlugin $_plugin;

    public function __construct(AiTransparencyDeclarationPlugin $plugin, int $contextId)
    {
        $this->_contextId = $contextId;
        $this->_plugin = $plugin;

        parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));

        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));
    }

    /**
     * @copydoc Form::initData()
     */
    public function initData()
    {
        $this->setData(
            'requireDeclaration',
            $this->_plugin->isDeclarationRequired($this->_contextId)
        );
    }

    /**
     * @copydoc Form::readInputData()
     */
    public function readInputData()
    {
        $this->readUserVars(['requireDeclaration']);
    }

    /**
     * @copydoc Form::execute()
     */
    public function execute(...$functionArgs)
    {
        parent::execute(...$functionArgs);

        $this->_plugin->updateSetting(
            $this->_contextId,
            'requireDeclaration',
            (bool) $this->getData('requireDeclaration')
        );
    }
}
