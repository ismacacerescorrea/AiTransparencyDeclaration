<?php

/**
 * AI Transparency Declaration (AITD) for OJS 3.3.
 * Adaptation by Ismael Cáceres-Correa, Sociedad Realidad e Historia.
 */

import('lib.pkp.classes.plugins.GenericPlugin');
import('lib.pkp.classes.linkAction.LinkAction');
import('lib.pkp.classes.linkAction.request.AjaxModal');
import('lib.pkp.classes.core.JSONMessage');

if (!class_exists('AiTransparencyDeclarationPlugin', false)) {

class AiTransparencyDeclarationPlugin extends GenericPlugin {

	private $domains = [
		['field' => 'aiTransparencyConceptualization', 'label' => 'plugins.generic.aiTransparencyDeclaration.form.conceptualization', 'options' => ['hypothesis', 'framework', 'researchGap', 'methodologyBrainstorming', 'projectDesign']],
		['field' => 'aiTransparencyLiteratureReview', 'label' => 'plugins.generic.aiTransparencyDeclaration.form.literatureReview', 'options' => ['literatureSearch', 'paperSummarization', 'citationManagement', 'thematicAnalysis', 'referenceValidation']],
		['field' => 'aiTransparencyMethodology', 'label' => 'plugins.generic.aiTransparencyDeclaration.form.methodology', 'options' => ['codeGeneration', 'statisticalAnalysis', 'dataProcessing', 'visualization', 'methodSelection', 'simulationModeling']],
		['field' => 'aiTransparencyWriting', 'label' => 'plugins.generic.aiTransparencyDeclaration.form.writing', 'options' => ['drafting', 'paraphrasing', 'grammarChecking', 'styleEditing', 'translation', 'formatting']],
		['field' => 'aiTransparencyInterpretation', 'label' => 'plugins.generic.aiTransparencyDeclaration.form.interpretation', 'options' => ['resultsInterpretation', 'implications', 'limitations', 'futureResearch', 'conclusions']],
	];

	function register($category, $path, $mainContextId = null) {
		if (!parent::register($category, $path, $mainContextId)) return false;
		if (!$this->getEnabled($mainContextId)) return true;

		HookRegistry::register('Schema::get::publication', [$this, 'addPublicationSchema']);
		HookRegistry::register('Publication::getProperties', [$this, 'normalizePublicationProperties']);
		HookRegistry::register('Form::config::before', [$this, 'addEditorialFields']);
		HookRegistry::register('Templates::Submission::SubmissionMetadataForm::AdditionalMetadata', [$this, 'displaySubmissionFields']);
		HookRegistry::register('submissionsubmitstep3form::display', [$this, 'markClassicSubmissionForm']);
		HookRegistry::register('submissionsubmitstep3form::initdata', [$this, 'initSubmissionData']);
		HookRegistry::register('submissionsubmitstep3form::readuservars', [$this, 'readSubmissionData']);
		HookRegistry::register('submissionsubmitstep3form::validate', [$this, 'validateSubmissionData']);
		HookRegistry::register('submissionsubmitstep3form::execute', [$this, 'saveSubmissionData']);
		HookRegistry::register('TemplateManager::display', [$this, 'handleTemplateDisplay']);
		HookRegistry::register('Templates::Article::Details', [$this, 'displayArticleDeclaration']);
		return true;
	}

	function getDisplayName() { return __('plugins.generic.aiTransparencyDeclaration.name'); }
	function getDescription() { return __('plugins.generic.aiTransparencyDeclaration.description'); }

	function getActions($request, $verb) {
		$router = $request->getRouter();
		return array_merge($this->getEnabled() ? [new LinkAction(
			'settings',
			new AjaxModal($router->url($request, null, null, 'manage', null, ['verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic']), $this->getDisplayName()),
			__('manager.plugins.settings')
		)] : [], parent::getActions($request, $verb));
	}

	function manage($args, $request) {
		$this->import('classes.form.AiTransparencyDeclarationSettingsForm');
		$context = $request->getContext();
		if (!$context) return parent::manage($args, $request);
		switch ($request->getUserVar('verb')) {
			case 'settings':
				$form = new AiTransparencyDeclarationSettingsForm($this, $context->getId());
				$form->initData();
				return new JSONMessage(true, $form->fetch($request));
			case 'save':
				$form = new AiTransparencyDeclarationSettingsForm($this, $context->getId());
				$form->readInputData();
				if ($form->validate()) { $form->execute(); return new JSONMessage(true); }
				return new JSONMessage(false);
		}
		return parent::manage($args, $request);
	}

	function getFieldNames() {
		return [
			'aiTransparencyStatus', 'aiTransparencyConceptualization', 'aiTransparencyLiteratureReview',
			'aiTransparencyMethodology', 'aiTransparencyWriting', 'aiTransparencyInterpretation',
			'aiTransparencyUsesConfirmed', 'aiTransparencyTools', 'aiTransparencyVersions',
			'aiTransparencyPromptLog', 'aiTransparencyValidationMethod',
			'aiTransparencyValidationDetails', 'aiTransparencyResponsibility'
		];
	}

	function addPublicationSchema($hookName, $args) {
		$schema =& $args[0];
		$domainOptions = [];
		foreach ($this->domains as $domain) {
			$domainOptions[$domain['field']] = $domain['options'];
		}

		foreach ($this->getFieldNames() as $name) {
			if (isset($domainOptions[$name])) {
				$schema->properties->{$name} = (object) [
					'type' => 'array',
					'validation' => ['nullable', 'max:' . count($domainOptions[$name])],
					'items' => (object) [
						'type' => 'string',
						'validation' => ['in:' . implode(',', $domainOptions[$name])],
					],
				];
			} elseif (in_array($name, ['aiTransparencyUsesConfirmed', 'aiTransparencyResponsibility'], true)) {
				$schema->properties->{$name} = (object) [
					'type' => 'boolean',
					'validation' => ['nullable'],
				];
			} elseif ($name === 'aiTransparencyStatus') {
				$schema->properties->{$name} = (object) [
					'type' => 'string',
					'validation' => ['nullable', 'in:none,used'],
				];
			} elseif (in_array($name, ['aiTransparencyTools', 'aiTransparencyVersions'], true)) {
				$schema->properties->{$name} = (object) [
					'type' => 'string',
					'validation' => ['nullable', 'max:500'],
				];
			} elseif ($name === 'aiTransparencyPromptLog') {
				$schema->properties->{$name} = (object) [
					'type' => 'string',
					'validation' => ['nullable', 'max:2048', 'url:http,https'],
				];
			} elseif ($name === 'aiTransparencyValidationMethod') {
				$schema->properties->{$name} = (object) [
					'type' => 'string',
					'validation' => ['nullable', 'in:minimal,standard,rigorous,custom'],
				];
			} elseif ($name === 'aiTransparencyValidationDetails') {
				$schema->properties->{$name} = (object) [
					'type' => 'string',
					'validation' => ['nullable', 'max:2000'],
				];
			} else {
				$schema->properties->{$name} = (object) [
					'type' => 'string',
					'validation' => ['nullable'],
				];
			}
		}
		return false;
	}

	function normalizePublicationProperties($hookName, $args) {
		$values =& $args[0];
		$props = $args[2];
		foreach (array_column($this->domains, 'field') as $name) if (in_array($name, $props)) $values[$name] = isset($values[$name]) && is_array($values[$name]) ? $values[$name] : [];
		foreach (['aiTransparencyUsesConfirmed', 'aiTransparencyResponsibility'] as $name) if (in_array($name, $props)) $values[$name] = !empty($values[$name]);
		return false;
	}

	function getPublicationFromComponentForm($form) {
		if (isset($form->publication)) return $form->publication;
		if (!empty($form->action) && preg_match('#/publications/(\d+)#', $form->action, $matches)) return Services::get('publication')->get((int) $matches[1]);
		return null;
	}

	function addEditorialFields($hookName, $form) {
		if (!($form instanceof \PKP\components\forms\publication\PKPMetadataForm)) return false;
		$publication = $this->getPublicationFromComponentForm($form);
		if (!$publication) return false;
		$this->addComponentFields($form, $publication, false);
		return false;
	}

	function addComponentFields($form, $publication, $required) {
		$form->addField(new \PKP\components\forms\FieldHTML('aiTransparencyIntroduction', ['label' => __('plugins.generic.aiTransparencyDeclaration.form.title'), 'description' => __('plugins.generic.aiTransparencyDeclaration.form.description')]));
		$form->addField(new \PKP\components\forms\FieldOptions('aiTransparencyStatus', [
			'type' => 'radio', 'label' => __('plugins.generic.aiTransparencyDeclaration.form.status'),
			'options' => [
				['value' => 'none', 'label' => __('plugins.generic.aiTransparencyDeclaration.form.status.none')],
				['value' => 'used', 'label' => __('plugins.generic.aiTransparencyDeclaration.form.status.used')],
			], 'value' => $publication->getData('aiTransparencyStatus'), 'isRequired' => $this->isDeclarationRequired(Application::get()->getRequest()->getContext()->getId())
		]));
		foreach ($this->domains as $domain) {
			$options = [];
			foreach ($domain['options'] as $key) $options[] = ['value' => $key, 'label' => __('plugins.generic.aiTransparencyDeclaration.use.' . $key)];
			$form->addField(new \PKP\components\forms\FieldOptions($domain['field'], ['label' => __($domain['label']), 'description' => __('plugins.generic.aiTransparencyDeclaration.form.selectAll'), 'options' => $options, 'value' => (array) $publication->getData($domain['field']), 'showWhen' => ['aiTransparencyStatus', 'used']]));
		}
		$form->addField(new \PKP\components\forms\FieldOptions('aiTransparencyUsesConfirmed', ['label' => __('plugins.generic.aiTransparencyDeclaration.form.usesConfirmation'), 'options' => [['value' => true, 'label' => __('plugins.generic.aiTransparencyDeclaration.form.usesConfirmation.label')]], 'value' => (bool) $publication->getData('aiTransparencyUsesConfirmed'), 'isRequired' => $required, 'showWhen' => ['aiTransparencyStatus', 'used']]));
		$form->addField(new \PKP\components\forms\FieldText('aiTransparencyTools', ['label' => __('plugins.generic.aiTransparencyDeclaration.form.tools'), 'description' => __('plugins.generic.aiTransparencyDeclaration.form.tools.description'), 'value' => $publication->getData('aiTransparencyTools'), 'isRequired' => $required, 'showWhen' => ['aiTransparencyStatus', 'used']]));
		$form->addField(new \PKP\components\forms\FieldText('aiTransparencyVersions', ['label' => __('plugins.generic.aiTransparencyDeclaration.form.versions'), 'description' => __('plugins.generic.aiTransparencyDeclaration.form.versions.description'), 'value' => $publication->getData('aiTransparencyVersions'), 'showWhen' => ['aiTransparencyStatus', 'used']]));
		$form->addField(new \PKP\components\forms\FieldText('aiTransparencyPromptLog', ['label' => __('plugins.generic.aiTransparencyDeclaration.form.promptLog'), 'description' => __('plugins.generic.aiTransparencyDeclaration.form.promptLog.description'), 'inputType' => 'url', 'value' => $publication->getData('aiTransparencyPromptLog'), 'showWhen' => ['aiTransparencyStatus', 'used']]));
		$validationOptions = [];
		foreach (['minimal', 'standard', 'rigorous', 'custom'] as $key) $validationOptions[] = ['value' => $key, 'label' => __('plugins.generic.aiTransparencyDeclaration.form.validation.' . $key)];
		$form->addField(new \PKP\components\forms\FieldOptions('aiTransparencyValidationMethod', ['type' => 'radio', 'label' => __('plugins.generic.aiTransparencyDeclaration.form.validation'), 'description' => __('plugins.generic.aiTransparencyDeclaration.form.validation.description'), 'options' => $validationOptions, 'value' => $publication->getData('aiTransparencyValidationMethod'), 'default' => 'standard', 'isRequired' => $required, 'showWhen' => ['aiTransparencyStatus', 'used']]));
		$form->addField(new \PKP\components\forms\FieldTextarea('aiTransparencyValidationDetails', ['label' => __('plugins.generic.aiTransparencyDeclaration.form.validationDetails'), 'description' => __('plugins.generic.aiTransparencyDeclaration.form.validationDetails.description'), 'value' => $publication->getData('aiTransparencyValidationDetails'), 'isRequired' => $required, 'showWhen' => ['aiTransparencyValidationMethod', 'custom']]));
		$form->addField(new \PKP\components\forms\FieldOptions('aiTransparencyResponsibility', ['label' => __('plugins.generic.aiTransparencyDeclaration.form.responsibility'), 'description' => __('plugins.generic.aiTransparencyDeclaration.form.responsibility.description'), 'options' => [['value' => true, 'label' => __('plugins.generic.aiTransparencyDeclaration.form.responsibility.label')]], 'value' => (bool) $publication->getData('aiTransparencyResponsibility'), 'isRequired' => $required, 'showWhen' => ['aiTransparencyStatus', 'used']]));
		$form->addField(new \PKP\components\forms\FieldHTML('aiTransparencyAttribution', ['description' => __('plugins.generic.aiTransparencyDeclaration.form.attribution.description')]));
	}

	function initSubmissionData($hookName, $args) {
		$form = $args[0];
		$publication = $form->submission->getCurrentPublication();
		foreach ($this->getFieldNames() as $name) $form->setData($name, $publication->getData($name));
		if (!$form->getData('aiTransparencyValidationMethod')) $form->setData('aiTransparencyValidationMethod', 'standard');
		return false;
	}

	function readSubmissionData($hookName, $args) {
		$vars =& $args[1];
		$vars = array_values(array_unique(array_merge($vars, $this->getFieldNames())));
		return false;
	}

	function validateSubmissionData($hookName, $args) {
		$form = $args[0];
		$status = $form->getData('aiTransparencyStatus');
		if (!$status && $this->isDeclarationRequired($form->submission->getData('contextId'))) {
			$form->addError('aiTransparencyStatus', __('plugins.generic.aiTransparencyDeclaration.form.status'));
		}
		if ($status && !in_array($status, ['none', 'used'], true)) {
			$form->addError('aiTransparencyStatus', __('plugins.generic.aiTransparencyDeclaration.form.status'));
			return false;
		}
		if ($status !== 'used') return false;

		$count = 0;
		foreach ($this->domains as $domain) {
			$selected = array_values(array_filter((array) $form->getData($domain['field'])));
			if (array_diff($selected, $domain['options'])) {
				$form->addError($domain['field'], __($domain['label']));
			}
			$count += count(array_intersect($selected, $domain['options']));
		}
		if (!$count) $form->addError('aiTransparencyConceptualization', __('plugins.generic.aiTransparencyDeclaration.form.usesConfirmation'));
		if (!$form->getData('aiTransparencyUsesConfirmed')) $form->addError('aiTransparencyUsesConfirmed', __('plugins.generic.aiTransparencyDeclaration.form.usesConfirmation'));

		$tools = trim((string) $form->getData('aiTransparencyTools'));
		if ($tools === '') $form->addError('aiTransparencyTools', __('plugins.generic.aiTransparencyDeclaration.form.tools'));
		if ($this->textLength($tools) > 500) $form->addError('aiTransparencyTools', __('plugins.generic.aiTransparencyDeclaration.form.tools'));

		$versions = trim((string) $form->getData('aiTransparencyVersions'));
		if ($this->textLength($versions) > 500) $form->addError('aiTransparencyVersions', __('plugins.generic.aiTransparencyDeclaration.form.versions'));

		$promptLog = trim((string) $form->getData('aiTransparencyPromptLog'));
		if ($this->textLength($promptLog) > 2048 || ($promptLog !== '' && !$this->getSafePublicUrl($promptLog))) {
			$form->addError('aiTransparencyPromptLog', __('plugins.generic.aiTransparencyDeclaration.form.promptLog'));
		}

		$method = $form->getData('aiTransparencyValidationMethod');
		if (!in_array($method, ['minimal', 'standard', 'rigorous', 'custom'], true)) {
			$form->addError('aiTransparencyValidationMethod', __('plugins.generic.aiTransparencyDeclaration.form.validation'));
		}
		$validationDetails = trim((string) $form->getData('aiTransparencyValidationDetails'));
		if ($method === 'custom' && $validationDetails === '') {
			$form->addError('aiTransparencyValidationDetails', __('plugins.generic.aiTransparencyDeclaration.form.validationDetails'));
		}
		if ($this->textLength($validationDetails) > 2000) {
			$form->addError('aiTransparencyValidationDetails', __('plugins.generic.aiTransparencyDeclaration.form.validationDetails'));
		}
		if (!$form->getData('aiTransparencyResponsibility')) $form->addError('aiTransparencyResponsibility', __('plugins.generic.aiTransparencyDeclaration.form.responsibility'));
		return false;
	}

	function saveSubmissionData($hookName, $args) {
		$form = $args[0];
		$publication = $form->submission->getCurrentPublication();
		$domainOptions = [];
		foreach ($this->domains as $domain) $domainOptions[$domain['field']] = $domain['options'];

		foreach ($this->getFieldNames() as $name) {
			$value = $form->getData($name);
			if (isset($domainOptions[$name])) {
				$value = array_values(array_intersect($domainOptions[$name], array_filter((array) $value)));
			} elseif (in_array($name, ['aiTransparencyUsesConfirmed', 'aiTransparencyResponsibility'], true)) {
				$value = (bool) $value;
			} elseif (is_string($value)) {
				$value = trim($value);
			}
			$publication->setData($name, $value);
		}
		DAORegistry::getDAO('PublicationDAO')->updateObject($publication);
		return false;
	}

	function markClassicSubmissionForm($hookName, $args) {
		$templateMgr = TemplateManager::getManager(Application::get()->getRequest());
		$templateMgr->assign('aitdClassicSubmissionForm', true);
		return false;
	}

	function displaySubmissionFields($hookName, $args) {
		$templateMgr = $args[1];
		$output =& $args[2];
		if (!$templateMgr->getTemplateVars('aitdClassicSubmissionForm')) return false;
		$context = Application::get()->getRequest()->getContext();
		$required = $context ? $this->isDeclarationRequired($context->getId()) : true;
		$output .= $this->buildClassicSubmissionHtml($templateMgr, $required);
		return false;
	}

	function escapeHtml($value) {
		return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
	}

	function buildClassicSubmissionHtml($templateMgr, $required) {
		$status = $templateMgr->getTemplateVars('aiTransparencyStatus');
		$html = '<style>#aiTransparencyDeclaration,#aiTransparencyDeclaration legend,#aiTransparencyDeclaration label,#aiTransparencyDeclaration strong{font-weight:400}</style>';
		$html .= '<fieldset class="pkpFormField pkpFormField--options" id="aiTransparencyDeclaration">';
		$html .= '<legend>' . $this->escapeHtml(__('plugins.generic.aiTransparencyDeclaration.form.title')) . '</legend>';
		$html .= '<p class="pkp_help">' . $this->escapeHtml(__('plugins.generic.aiTransparencyDeclaration.form.description')) . '</p>';
		$html .= '<div class="section formButtons form_buttons"><span>' . $this->escapeHtml(__('plugins.generic.aiTransparencyDeclaration.form.status')) . '</span>';
		foreach (['none', 'used'] as $choice) {
			$checked = $status === $choice ? ' checked="checked"' : '';
			$requiredAttr = $required ? ' required="required"' : '';
			$html .= '<label style="display:block"><input type="radio" name="aiTransparencyStatus" value="' . $choice . '"' . $checked . $requiredAttr . '> ' . $this->escapeHtml(__('plugins.generic.aiTransparencyDeclaration.form.status.' . $choice)) . '</label>';
		}
		$html .= '</div><div class="aitd-ojs33-used">';

		foreach ($this->domains as $domain) {
			$selected = (array) $templateMgr->getTemplateVars($domain['field']);
			$html .= '<div class="section"><span>' . $this->escapeHtml(__($domain['label'])) . '</span>';
			foreach ($domain['options'] as $option) {
				$checked = in_array($option, $selected) ? ' checked="checked"' : '';
				$name = $this->escapeHtml($domain['field'] . '[]');
				$html .= '<label style="display:block"><input type="checkbox" name="' . $name . '" value="' . $this->escapeHtml($option) . '"' . $checked . '> ' . $this->escapeHtml(__('plugins.generic.aiTransparencyDeclaration.use.' . $option)) . '</label>';
			}
			$html .= '</div>';
		}

		$confirmed = $templateMgr->getTemplateVars('aiTransparencyUsesConfirmed') ? ' checked="checked"' : '';
		$html .= '<div class="section"><label><input type="checkbox" name="aiTransparencyUsesConfirmed" value="1"' . $confirmed . '> ' . $this->escapeHtml(__('plugins.generic.aiTransparencyDeclaration.form.usesConfirmation.label')) . '</label></div>';
		$html .= $this->classicTextField('aiTransparencyTools', __('plugins.generic.aiTransparencyDeclaration.form.tools'), $templateMgr->getTemplateVars('aiTransparencyTools'), true, 500, 'text');
		$html .= $this->classicTextField('aiTransparencyVersions', __('plugins.generic.aiTransparencyDeclaration.form.versions'), $templateMgr->getTemplateVars('aiTransparencyVersions'), false, 500, 'text');
		$html .= $this->classicTextField('aiTransparencyPromptLog', __('plugins.generic.aiTransparencyDeclaration.form.promptLog'), $templateMgr->getTemplateVars('aiTransparencyPromptLog'), false, 2048, 'url');

		$validation = $templateMgr->getTemplateVars('aiTransparencyValidationMethod');
		$html .= '<div class="section"><span>' . $this->escapeHtml(__('plugins.generic.aiTransparencyDeclaration.form.validation')) . '</span>';
		foreach (['minimal', 'standard', 'rigorous', 'custom'] as $method) {
			$checked = $validation === $method ? ' checked="checked"' : '';
			$html .= '<label style="display:block"><input type="radio" name="aiTransparencyValidationMethod" value="' . $method . '"' . $checked . ' required="required"> ' . $this->escapeHtml(__('plugins.generic.aiTransparencyDeclaration.form.validation.' . $method)) . '</label>';
		}
		$html .= '</div>';
		$html .= '<div class="section aitd-ojs33-custom"><label><span>' . $this->escapeHtml(__('plugins.generic.aiTransparencyDeclaration.form.validationDetails')) . '</span><textarea name="aiTransparencyValidationDetails" rows="4" maxlength="2000">' . $this->escapeHtml($templateMgr->getTemplateVars('aiTransparencyValidationDetails')) . '</textarea></label></div>';
		$responsibility = $templateMgr->getTemplateVars('aiTransparencyResponsibility') ? ' checked="checked"' : '';
		$html .= '<div class="section"><label><input type="checkbox" name="aiTransparencyResponsibility" value="1"' . $responsibility . '> ' . $this->escapeHtml(__('plugins.generic.aiTransparencyDeclaration.form.responsibility.label')) . '</label></div>';
		$html .= '</div><p class="pkp_help">' . __('plugins.generic.aiTransparencyDeclaration.form.attribution.description') . '</p></fieldset>';
		$html .= '<script>$(function(){function aitdToggle(){var u=$(\'input[name="aiTransparencyStatus"]:checked\').val()===\'used\';$(\'.aitd-ojs33-used\').toggle(u).find(\':input\').prop(\'disabled\',!u);var c=u&&$(\'input[name="aiTransparencyValidationMethod"]:checked\').val()===\'custom\';$(\'.aitd-ojs33-custom\').toggle(c).find(\':input\').prop(\'disabled\',!c);}$(\'#submitStep3Form\').on(\'change\',\'input[name="aiTransparencyStatus"],input[name="aiTransparencyValidationMethod"]\',aitdToggle);aitdToggle();});</script>';
		return $html;
	}

	function classicTextField($name, $label, $value, $required, $maxLength = null, $inputType = 'text') {
		$type = in_array($inputType, ['text', 'url'], true) ? $inputType : 'text';
		$maxLengthAttr = $maxLength ? ' maxlength="' . (int) $maxLength . '"' : '';
		return '<div class="section"><label><span>' . $this->escapeHtml($label) . '</span><input type="' . $type . '" name="' . $this->escapeHtml($name) . '" value="' . $this->escapeHtml($value) . '"' . $maxLengthAttr . ($required ? ' required="required"' : '') . '></label></div>';
	}

	function textLength($value) {
		$value = (string) $value;
		return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
	}

	function getSafePublicUrl($value) {
		$url = trim((string) $value);
		if ($url === '' || $this->textLength($url) > 2048 || !filter_var($url, FILTER_VALIDATE_URL)) return null;
		$scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
		return in_array($scheme, ['http', 'https'], true) ? $url : null;
	}

	function handleTemplateDisplay($hookName, $args) {
		$templateMgr =& $args[0];
		$template =& $args[1];
		if ($template !== 'frontend/pages/article.tpl') return false;
		$declaration = $this->getCompleteDeclaration($templateMgr->getTemplateVars('publication'));
		if (!$declaration) return false;
		$templateMgr->assign('aitdDeclaration', $declaration);
		$request = Application::get()->getRequest();
		$templateMgr->addStyleSheet('aiTransparencyDeclarationCertificate', rtrim($request->getBaseUrl(), '/') . '/' . $this->getPluginPath() . '/styles/aitd-certificate.css', ['contexts' => ['frontend'], 'priority' => STYLE_SEQUENCE_LAST]);
		$templateMgr->addHeader('aiTransparencyDeclarationMetadata', '<meta name="ai-transparency-declaration" content="AITD v1.1">' . "\n" . '<meta name="ai-transparency-use" content="' . htmlspecialchars($declaration['status'], ENT_QUOTES, 'UTF-8') . '">');
		return false;
	}

	function displayArticleDeclaration($hookName, $args) {
		$templateMgr =& $args[1]; $output =& $args[2];
		$declaration = $templateMgr->getTemplateVars('aitdDeclaration');
		if (!$declaration) $declaration = $this->getCompleteDeclaration($templateMgr->getTemplateVars('publication'));
		if ($declaration) { $templateMgr->assign('aitdDeclaration', $declaration); $output .= $templateMgr->fetch($this->getTemplateResource('articleDeclaration.tpl')); }
		return false;
	}

	function getCompleteDeclaration($publication) {
		if (!$publication) return null;
		$status = $publication->getData('aiTransparencyStatus');
		if (!in_array($status, ['none', 'used'], true)) return null;
		$locale = $this->getPublicLocale();
		$d = [
			'status' => $status, 'lang' => strpos($locale, 'es') === 0 ? 'es' : (strpos($locale, 'pt') === 0 ? 'pt' : 'en'),
			'title' => __('plugins.generic.aiTransparencyDeclaration.name', [], $locale),
			'toggleLabel' => __('plugins.generic.aiTransparencyDeclaration.public.toggle', [], $locale),
			'statusLabel' => __('plugins.generic.aiTransparencyDeclaration.public.status.' . $status, [], $locale),
			'introduction' => __('plugins.generic.aiTransparencyDeclaration.public.introduction', [], $locale),
			'noAiStatement' => __('plugins.generic.aiTransparencyDeclaration.public.noAi', [], $locale),
			'declaredUsesLabel' => __('plugins.generic.aiTransparencyDeclaration.public.declaredUses', [], $locale),
			'technicalDetailsLabel' => __('plugins.generic.aiTransparencyDeclaration.public.technicalDetails', [], $locale),
			'toolsLabel' => __('plugins.generic.aiTransparencyDeclaration.public.tools', [], $locale),
			'versionsLabel' => __('plugins.generic.aiTransparencyDeclaration.public.versions', [], $locale),
			'promptLogLabel' => __('plugins.generic.aiTransparencyDeclaration.public.promptLog', [], $locale),
			'humanOversightLabel' => __('plugins.generic.aiTransparencyDeclaration.public.humanOversight', [], $locale),
			'validationLabel' => __('plugins.generic.aiTransparencyDeclaration.public.validation', [], $locale),
			'responsibilityLabel' => __('plugins.generic.aiTransparencyDeclaration.public.responsibility', [], $locale),
			'responsibilityStatement' => __('plugins.generic.aiTransparencyDeclaration.form.responsibility.label', [], $locale),
			'attribution' => __('plugins.generic.aiTransparencyDeclaration.public.attribution', [], $locale),
			'domains' => [], 'tools' => null, 'versions' => null, 'promptLog' => null, 'validation' => null
		];
		if ($status === 'none') return $d;
		if (!$publication->getData('aiTransparencyUsesConfirmed') || !$publication->getData('aiTransparencyResponsibility')) return null;
		foreach ($this->domains as $domain) {
			$selected = array_values(array_intersect($domain['options'], (array) $publication->getData($domain['field'])));
			if (!$selected) continue;
			$uses = []; foreach ($selected as $use) $uses[] = __('plugins.generic.aiTransparencyDeclaration.use.' . $use, [], $locale);
			$d['domains'][] = ['label' => preg_replace('/^\d+\.\s*/u', '', __($domain['label'], [], $locale)), 'uses' => $uses];
		}
		if (!$d['domains'] || !trim((string) $publication->getData('aiTransparencyTools'))) return null;
		$method = $publication->getData('aiTransparencyValidationMethod');
		if (!in_array($method, ['minimal', 'standard', 'rigorous', 'custom'], true)) return null;
		$validation = $method === 'custom' ? trim((string) $publication->getData('aiTransparencyValidationDetails')) : __('plugins.generic.aiTransparencyDeclaration.form.validation.' . $method, [], $locale);
		if (!$validation) return null;
		$d['tools'] = trim((string) $publication->getData('aiTransparencyTools'));
		$d['versions'] = trim((string) $publication->getData('aiTransparencyVersions')) ?: null;
		$d['promptLog'] = $this->getSafePublicUrl($publication->getData('aiTransparencyPromptLog'));
		$d['validation'] = $validation;
		return $d;
	}

	function getPublicLocale() {
		$locale = strtolower(str_replace('-', '_', AppLocale::getLocale()));
		if (strpos($locale, 'es') === 0) return 'es_ES';
		if (strpos($locale, 'pt_br') === 0) return 'pt_BR';
		if (strpos($locale, 'pt') === 0) return 'pt_PT';
		return 'en_US';
	}

	function isDeclarationRequired($contextId) {
		$value = $this->getSetting($contextId, 'requireDeclaration');
		return $value === null ? true : (bool) $value;
	}
}

}

return new AiTransparencyDeclarationPlugin();
