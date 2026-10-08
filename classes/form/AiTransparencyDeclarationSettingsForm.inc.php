<?php

import('lib.pkp.classes.form.Form');

class AiTransparencyDeclarationSettingsForm extends Form {
	var $_contextId;
	var $_plugin;

	function __construct($plugin, $contextId) {
		$this->_contextId = $contextId;
		$this->_plugin = $plugin;
		parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	function initData() {
		$this->setData('requireDeclaration', $this->_plugin->isDeclarationRequired($this->_contextId));
	}

	function readInputData() {
		$this->readUserVars(['requireDeclaration']);
	}

	function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());
		return parent::fetch($request, $template, $display);
	}

	function execute(...$functionArgs) {
		parent::execute(...$functionArgs);
		$this->_plugin->updateSetting($this->_contextId, 'requireDeclaration', (bool) $this->getData('requireDeclaration'));
	}
}
