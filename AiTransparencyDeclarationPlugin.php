<?php

/**
 * @file AiTransparencyDeclarationPlugin.php
 *
 * Copyright (c) 2026
 * Distributed under the GNU Affero General Public License v3.0.
 *
 * @class AiTransparencyDeclarationPlugin
 * @brief Adds a preprint-level AI Transparency Declaration (AITD) to OPS.
 * @author Ismael Cáceres-Correa, Sociedad Realidad e Historia
 */

namespace APP\plugins\generic\aiTransparencyDeclaration;

use APP\core\Application;
use APP\plugins\generic\aiTransparencyDeclaration\classes\form\AiTransparencyDeclarationSettingsForm;
use APP\template\TemplateManager;
use PKP\components\forms\FieldHTML;
use PKP\components\forms\FieldOptions;
use PKP\components\forms\FieldText;
use PKP\components\forms\FieldTextarea;
use PKP\components\forms\FormComponent;
use PKP\components\forms\publication\Details;
use PKP\components\forms\publication\PKPMetadataForm;
use PKP\components\forms\submission\ForTheEditors;
use PKP\core\JSONMessage;
use PKP\facades\Locale;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class AiTransparencyDeclarationPlugin extends GenericPlugin
{
    private const USE_DOMAINS = [
        'conceptualization' => [
            'field' => 'aiTransparencyConceptualization',
            'label' => 'plugins.generic.aiTransparencyDeclaration.form.conceptualization',
            'options' => [
                'hypothesis',
                'framework',
                'researchGap',
                'methodologyBrainstorming',
                'projectDesign',
            ],
        ],
        'literatureReview' => [
            'field' => 'aiTransparencyLiteratureReview',
            'label' => 'plugins.generic.aiTransparencyDeclaration.form.literatureReview',
            'options' => [
                'literatureSearch',
                'paperSummarization',
                'citationManagement',
                'thematicAnalysis',
                'referenceValidation',
            ],
        ],
        'methodology' => [
            'field' => 'aiTransparencyMethodology',
            'label' => 'plugins.generic.aiTransparencyDeclaration.form.methodology',
            'options' => [
                'codeGeneration',
                'statisticalAnalysis',
                'dataProcessing',
                'visualization',
                'methodSelection',
                'simulationModeling',
            ],
        ],
        'writing' => [
            'field' => 'aiTransparencyWriting',
            'label' => 'plugins.generic.aiTransparencyDeclaration.form.writing',
            'options' => [
                'drafting',
                'paraphrasing',
                'grammarChecking',
                'styleEditing',
                'translation',
                'formatting',
            ],
        ],
        'interpretation' => [
            'field' => 'aiTransparencyInterpretation',
            'label' => 'plugins.generic.aiTransparencyDeclaration.form.interpretation',
            'options' => [
                'resultsInterpretation',
                'implications',
                'limitations',
                'futureResearch',
                'conclusions',
            ],
        ],
    ];

    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null)
    {
        if (!parent::register($category, $path, $mainContextId)) {
            return false;
        }

        if ($this->getEnabled($mainContextId)) {
            Hook::add('Schema::get::publication', [$this, 'addPublicationSchema']);
            Hook::add('Form::config::before', [$this, 'addDeclarationFields']);
            Hook::add('TemplateManager::display', [$this, 'handleTemplateDisplay']);
            Hook::add('Templates::Preprint::Details', [$this, 'displayPreprintDeclaration']);
        }

        return true;
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName(): string
    {
        return __('plugins.generic.aiTransparencyDeclaration.name');
    }

    /**
     * @copydoc Plugin::getDescription()
     */
    public function getDescription(): string
    {
        return __('plugins.generic.aiTransparencyDeclaration.description');
    }

    /**
     * @copydoc Plugin::getActions()
     */
    public function getActions($request, $verb)
    {
        $router = $request->getRouter();

        return array_merge(
            $this->getEnabled() ? [
                new LinkAction(
                    'settings',
                    new AjaxModal(
                        $router->url(
                            $request,
                            null,
                            null,
                            'manage',
                            null,
                            [
                                'verb' => 'settings',
                                'plugin' => $this->getName(),
                                'category' => 'generic',
                            ]
                        ),
                        $this->getDisplayName()
                    ),
                    __('manager.plugins.settings'),
                    null
                ),
            ] : [],
            parent::getActions($request, $verb)
        );
    }

    /**
     * @copydoc Plugin::manage()
     */
    public function manage($args, $request)
    {
        switch ($request->getUserVar('verb')) {
            case 'settings':
                $context = $request->getContext();
                if (!$context) {
                    return parent::manage($args, $request);
                }

                $form = new AiTransparencyDeclarationSettingsForm($this, $context->getId());
                $form->initData();
                return new JSONMessage(true, $form->fetch($request));

            case 'save':
                $context = $request->getContext();
                if (!$context) {
                    return parent::manage($args, $request);
                }

                $form = new AiTransparencyDeclarationSettingsForm($this, $context->getId());
                $form->readInputData();
                if ($form->validate()) {
                    $form->execute();
                    return new JSONMessage(true);
                }
                return new JSONMessage(false);
        }

        return parent::manage($args, $request);
    }

    /**
     * Extend the publication schema with preprint-level AITD properties.
     */
    public function addPublicationSchema($hookName, $args): bool
    {
        $schema = $args[0];

        $schema->properties->aiTransparencyStatus = (object) [
            'type' => 'string',
            'validation' => ['nullable', 'in:none,used'],
        ];

        $schema->properties->aiTransparencyTools = (object) [
            'type' => 'string',
            'validation' => ['nullable', 'max:500'],
        ];

        $schema->properties->aiTransparencyVersions = (object) [
            'type' => 'string',
            'validation' => ['nullable', 'max:500'],
        ];

        $schema->properties->aiTransparencyPromptLog = (object) [
            'type' => 'string',
            'validation' => ['nullable', 'max:2048', 'url:http,https'],
        ];

        $schema->properties->aiTransparencyValidationMethod = (object) [
            'type' => 'string',
            'validation' => ['nullable', 'in:minimal,standard,rigorous,custom'],
        ];

        $schema->properties->aiTransparencyValidationDetails = (object) [
            'type' => 'string',
            'validation' => ['nullable', 'max:2000'],
        ];

        foreach (self::USE_DOMAINS as $domain) {
            $schema->properties->{$domain['field']} = (object) [
                'type' => 'array',
                'validation' => ['nullable', 'max:' . count($domain['options'])],
                'items' => (object) [
                    'type' => 'string',
                    'validation' => ['in:' . implode(',', $domain['options'])],
                ],
            ];
        }

        foreach ([
            'aiTransparencyUsesConfirmed',
            'aiTransparencyResponsibility',
        ] as $propertyName) {
            $schema->properties->{$propertyName} = (object) [
                'type' => 'boolean',
                'validation' => ['nullable'],
            ];
        }

        return Hook::CONTINUE;
    }

    /**
     * Add the declaration to the submission Details form and to the
     * publication metadata form used during the editorial workflow.
     *
     * @param string $hookName
     * @param FormComponent $form
     */
    public function addDeclarationFields($hookName, $form): bool
    {
        $isSubmissionDetails = $form instanceof Details;
        $isEditorialMetadata = $form instanceof PKPMetadataForm
            && !($form instanceof ForTheEditors);

        if (!$isSubmissionDetails && !$isEditorialMetadata) {
            return Hook::CONTINUE;
        }

        $request = Application::get()->getRequest();
        $context = $request->getContext();
        if (!$context || !isset($form->publication)) {
            return Hook::CONTINUE;
        }

        $publication = $form->publication;
        $isRequired = $this->isDeclarationRequired($context->getId());
        $showWhenAiUsed = ['aiTransparencyStatus', 'used'];
        $firstDeclarationFieldIndex = count($form->fields);

        $form->addField(new FieldHTML('aiTransparencyIntroduction', [
            'label' => __('plugins.generic.aiTransparencyDeclaration.form.title'),
        ]));

        $form->addField(new FieldOptions('aiTransparencyStatus', [
            'type' => 'radio',
            'label' => __('plugins.generic.aiTransparencyDeclaration.form.status'),
            'description' => __('plugins.generic.aiTransparencyDeclaration.form.status.description'),
            'options' => [
                [
                    'value' => 'none',
                    'label' => __('plugins.generic.aiTransparencyDeclaration.form.status.none'),
                ],
                [
                    'value' => 'used',
                    'label' => __('plugins.generic.aiTransparencyDeclaration.form.status.used'),
                ],
            ],
            'value' => $publication->getData('aiTransparencyStatus'),
            'isRequired' => $isRequired,
        ]));

        $this->addUseField(
            $form,
            'aiTransparencyConceptualization',
            'plugins.generic.aiTransparencyDeclaration.form.conceptualization',
            [
                'hypothesis',
                'framework',
                'researchGap',
                'methodologyBrainstorming',
                'projectDesign',
            ],
            (array) $publication->getData('aiTransparencyConceptualization'),
            $showWhenAiUsed
        );

        $this->addUseField(
            $form,
            'aiTransparencyLiteratureReview',
            'plugins.generic.aiTransparencyDeclaration.form.literatureReview',
            [
                'literatureSearch',
                'paperSummarization',
                'citationManagement',
                'thematicAnalysis',
                'referenceValidation',
            ],
            (array) $publication->getData('aiTransparencyLiteratureReview'),
            $showWhenAiUsed
        );

        $this->addUseField(
            $form,
            'aiTransparencyMethodology',
            'plugins.generic.aiTransparencyDeclaration.form.methodology',
            [
                'codeGeneration',
                'statisticalAnalysis',
                'dataProcessing',
                'visualization',
                'methodSelection',
                'simulationModeling',
            ],
            (array) $publication->getData('aiTransparencyMethodology'),
            $showWhenAiUsed
        );

        $this->addUseField(
            $form,
            'aiTransparencyWriting',
            'plugins.generic.aiTransparencyDeclaration.form.writing',
            [
                'drafting',
                'paraphrasing',
                'grammarChecking',
                'styleEditing',
                'translation',
                'formatting',
            ],
            (array) $publication->getData('aiTransparencyWriting'),
            $showWhenAiUsed
        );

        $this->addUseField(
            $form,
            'aiTransparencyInterpretation',
            'plugins.generic.aiTransparencyDeclaration.form.interpretation',
            [
                'resultsInterpretation',
                'implications',
                'limitations',
                'futureResearch',
                'conclusions',
            ],
            (array) $publication->getData('aiTransparencyInterpretation'),
            $showWhenAiUsed
        );

        $form->addField(new FieldOptions('aiTransparencyUsesConfirmed', [
            'label' => __('plugins.generic.aiTransparencyDeclaration.form.usesConfirmation'),
            'options' => [[
                'value' => true,
                'label' => __('plugins.generic.aiTransparencyDeclaration.form.usesConfirmation.label'),
            ]],
            'value' => (bool) $publication->getData('aiTransparencyUsesConfirmed'),
            'isRequired' => true,
            'showWhen' => $showWhenAiUsed,
        ]));

        $form->addField(new FieldText('aiTransparencyTools', [
            'label' => __('plugins.generic.aiTransparencyDeclaration.form.tools'),
            'description' => __('plugins.generic.aiTransparencyDeclaration.form.tools.description'),
            'value' => $publication->getData('aiTransparencyTools'),
            'isRequired' => true,
            'showWhen' => $showWhenAiUsed,
        ]));

        $form->addField(new FieldText('aiTransparencyVersions', [
            'label' => __('plugins.generic.aiTransparencyDeclaration.form.versions'),
            'description' => __('plugins.generic.aiTransparencyDeclaration.form.versions.description'),
            'value' => $publication->getData('aiTransparencyVersions'),
            'showWhen' => $showWhenAiUsed,
        ]));

        $form->addField(new FieldText('aiTransparencyPromptLog', [
            'label' => __('plugins.generic.aiTransparencyDeclaration.form.promptLog'),
            'description' => __('plugins.generic.aiTransparencyDeclaration.form.promptLog.description'),
            'inputType' => 'url',
            'value' => $publication->getData('aiTransparencyPromptLog'),
            'showWhen' => $showWhenAiUsed,
        ]));

        $form->addField(new FieldOptions('aiTransparencyValidationMethod', [
            'type' => 'radio',
            'label' => __('plugins.generic.aiTransparencyDeclaration.form.validation'),
            'description' => __('plugins.generic.aiTransparencyDeclaration.form.validation.description'),
            'options' => [
                [
                    'value' => 'minimal',
                    'label' => __('plugins.generic.aiTransparencyDeclaration.form.validation.minimal'),
                ],
                [
                    'value' => 'standard',
                    'label' => __('plugins.generic.aiTransparencyDeclaration.form.validation.standard'),
                ],
                [
                    'value' => 'rigorous',
                    'label' => __('plugins.generic.aiTransparencyDeclaration.form.validation.rigorous'),
                ],
                [
                    'value' => 'custom',
                    'label' => __('plugins.generic.aiTransparencyDeclaration.form.validation.custom'),
                ],
            ],
            'value' => $publication->getData('aiTransparencyValidationMethod'),
            'default' => 'standard',
            'isRequired' => true,
            'showWhen' => $showWhenAiUsed,
        ]));

        $form->addField(new FieldTextarea('aiTransparencyValidationDetails', [
            'label' => __('plugins.generic.aiTransparencyDeclaration.form.validationDetails'),
            'description' => __('plugins.generic.aiTransparencyDeclaration.form.validationDetails.description'),
            'size' => 'small',
            'value' => $publication->getData('aiTransparencyValidationDetails'),
            'isRequired' => true,
            'showWhen' => ['aiTransparencyValidationMethod', 'custom'],
        ]));

        $form->addField(new FieldOptions('aiTransparencyResponsibility', [
            'label' => __('plugins.generic.aiTransparencyDeclaration.form.responsibility'),
            'description' => __('plugins.generic.aiTransparencyDeclaration.form.responsibility.description'),
            'options' => [[
                'value' => true,
                'label' => __('plugins.generic.aiTransparencyDeclaration.form.responsibility.label'),
            ]],
            'value' => (bool) $publication->getData('aiTransparencyResponsibility'),
            'isRequired' => true,
            'showWhen' => $showWhenAiUsed,
        ]));

        $form->addField(new FieldHTML('aiTransparencyAttribution', [
            'description' => __('plugins.generic.aiTransparencyDeclaration.form.attribution.description'),
        ]));

        $this->assignDeclarationFieldsToFormGroup($form, $firstDeclarationFieldIndex);

        return Hook::CONTINUE;
    }

    /**
     * Add public assets and machine-readable metadata to preprint pages only
     * when a complete declaration exists.
     */
    public function handleTemplateDisplay(string $hookName, array $args): bool
    {
        /** @var TemplateManager $templateMgr */
        $templateMgr = $args[0];
        $template = $args[1];

        if ($template !== 'frontend/pages/preprint.tpl') {
            return Hook::CONTINUE;
        }

        $publication = $templateMgr->getTemplateVars('publication');
        $declaration = $this->getCompleteDeclaration($publication);
        if ($declaration === null) {
            return Hook::CONTINUE;
        }

        $templateMgr->assign('aitdDeclaration', $declaration);

        $request = Application::get()->getRequest();
        $styleUrl = rtrim($request->getBaseUrl(), '/')
            . '/' . $this->getPluginPath()
            . '/styles/aitd-certificate.css';

        $templateMgr->addStyleSheet(
            'aiTransparencyDeclarationCertificate',
            $styleUrl,
            [
                'contexts' => ['frontend'],
                'priority' => STYLE_SEQUENCE_LAST,
            ]
        );

        $metadata = sprintf(
            '<meta name="ai-transparency-declaration" content="AITD v1.1">%s'
                . '<meta name="ai-transparency-use" content="%s">%s'
                . '<meta name="ai-transparency-language" content="%s">',
            "\n",
            htmlspecialchars($declaration['status'], ENT_QUOTES, 'UTF-8'),
            "\n",
            htmlspecialchars($declaration['lang'], ENT_QUOTES, 'UTF-8')
        );
        $templateMgr->addHeader('aiTransparencyDeclarationMetadata', $metadata);

        return Hook::CONTINUE;
    }

    /**
     * Insert a theme-independent, collapsible declaration block into the
     * preprint details area used by OPS themes.
     */
    public function displayPreprintDeclaration(string $hookName, array $args): bool
    {
        /** @var TemplateManager $templateMgr */
        $templateMgr = $args[1];
        $output =& $args[2];

        $declaration = $templateMgr->getTemplateVars('aitdDeclaration');
        if (!$declaration) {
            $publication = $templateMgr->getTemplateVars('publication');
            $declaration = $this->getCompleteDeclaration($publication);
        }

        if ($declaration === null) {
            return Hook::CONTINUE;
        }

        $templateMgr->assign('aitdDeclaration', $declaration);
        $output .= $templateMgr->fetch($this->getTemplateResource('preprintDeclaration.tpl'));

        return Hook::CONTINUE;
    }

    /**
     * Return a localized public declaration only when all essential data is
     * present. Older preprints and partially completed declarations return null.
     */
    private function getCompleteDeclaration($publication): ?array
    {
        if (!$publication || !method_exists($publication, 'getData')) {
            return null;
        }

        $status = $publication->getData('aiTransparencyStatus');
        if (!in_array($status, ['none', 'used'], true)) {
            return null;
        }

        $locale = $this->getPublicLocale();
        $lang = str_starts_with(strtolower($locale), 'es')
            ? 'es'
            : (str_starts_with(strtolower($locale), 'pt') ? 'pt' : 'en');

        $declaration = [
            'status' => $status,
            'lang' => $lang,
            'locale' => $locale,
            'title' => __('plugins.generic.aiTransparencyDeclaration.name', [], $locale),
            'toggleLabel' => __('plugins.generic.aiTransparencyDeclaration.public.toggle', [], $locale),
            'statusLabel' => __(
                'plugins.generic.aiTransparencyDeclaration.public.status.' . $status,
                [],
                $locale
            ),
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
            'domains' => [],
            'tools' => null,
            'versions' => null,
            'promptLog' => null,
            'validation' => null,
        ];

        // A recorded declaration of no AI use is complete on its own.
        if ($status === 'none') {
            return $declaration;
        }

        if (
            !(bool) $publication->getData('aiTransparencyUsesConfirmed')
            || !(bool) $publication->getData('aiTransparencyResponsibility')
        ) {
            return null;
        }

        $selectedUseCount = 0;
        foreach (self::USE_DOMAINS as $domain) {
            $selected = array_values(array_intersect(
                $domain['options'],
                array_filter((array) $publication->getData($domain['field']), 'is_string')
            ));

            if (!$selected) {
                continue;
            }

            $selectedUseCount += count($selected);
            $domainLabel = preg_replace(
                '/^\d+\.\s*/u',
                '',
                __($domain['label'], [], $locale)
            );
            $declaration['domains'][] = [
                'label' => $domainLabel,
                'uses' => array_map(
                    fn (string $use): string => __(
                        'plugins.generic.aiTransparencyDeclaration.use.' . $use,
                        [],
                        $locale
                    ),
                    $selected
                ),
            ];
        }

        if ($selectedUseCount === 0) {
            return null;
        }

        $tools = trim((string) $publication->getData('aiTransparencyTools'));
        if ($tools === '') {
            return null;
        }

        $validationMethod = $publication->getData('aiTransparencyValidationMethod');
        if (!in_array($validationMethod, ['minimal', 'standard', 'rigorous', 'custom'], true)) {
            return null;
        }

        if ($validationMethod === 'custom') {
            $validation = trim((string) $publication->getData('aiTransparencyValidationDetails'));
            if ($validation === '') {
                return null;
            }
        } else {
            $validation = __(
                'plugins.generic.aiTransparencyDeclaration.form.validation.' . $validationMethod,
                [],
                $locale
            );
        }

        $declaration['tools'] = $tools;
        $declaration['versions'] = trim((string) $publication->getData('aiTransparencyVersions')) ?: null;
        $declaration['promptLog'] = $this->getSafePublicUrl(
            trim((string) $publication->getData('aiTransparencyPromptLog'))
        );
        $declaration['validation'] = $validation;

        return $declaration;
    }

    /**
     * Use Spanish and Portuguese when requested by OPS; use English for every
     * other page language.
     */
    private function getPublicLocale(): string
    {
        $locale = strtolower(str_replace('-', '_', Locale::getLocale()));

        if (str_starts_with($locale, 'es')) {
            return 'es';
        }
        if (str_starts_with($locale, 'pt_br')) {
            return 'pt_BR';
        }
        if (str_starts_with($locale, 'pt')) {
            return 'pt';
        }
        return 'en';
    }

    /**
     * Only expose HTTP(S) prompt-log links in the public preprint block.
     */
    private function getSafePublicUrl(string $url): ?string
    {
        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], true) ? $url : null;
    }

    /**
     * Assign fields added by the plugin to a visible group.
     *
     * The submission wizard creates its default page and group before the
     * Form::config::before hook runs. Fields added at that point are therefore
     * not assigned automatically and the UI will not render them unless the
     * plugin supplies a group ID.
     */
    private function assignDeclarationFieldsToFormGroup(
        FormComponent $form,
        int $firstDeclarationFieldIndex
    ): void {
        $groupId = 'default';

        if (!empty($form->groups)) {
            $firstGroupKey = array_key_first($form->groups);
            $firstGroup = $form->groups[$firstGroupKey];
            if (is_array($firstGroup) && !empty($firstGroup['id'])) {
                $groupId = $firstGroup['id'];
            }
        }

        foreach (array_slice($form->fields, $firstDeclarationFieldIndex) as $field) {
            if (empty($field->groupId)) {
                $field->groupId = $groupId;
            }
        }
    }

    /**
     * Add a checkbox group for one AITD domain.
     */
    private function addUseField(
        FormComponent $form,
        string $fieldName,
        string $labelKey,
        array $optionKeys,
        array $value,
        array $showWhen
    ): void {
        $options = [];
        foreach ($optionKeys as $optionKey) {
            $options[] = [
                'value' => $optionKey,
                'label' => __("plugins.generic.aiTransparencyDeclaration.use.{$optionKey}"),
            ];
        }

        $form->addField(new FieldOptions($fieldName, [
            'label' => __($labelKey),
            'description' => __('plugins.generic.aiTransparencyDeclaration.form.selectAll'),
            'options' => $options,
            'value' => $value,
            'showWhen' => $showWhen,
        ]));
    }

    /**
     * The declaration is required by default. Preprint server managers can make it
     * optional in the plugin settings.
     */
    public function isDeclarationRequired(int $contextId): bool
    {
        $setting = $this->getSetting($contextId, 'requireDeclaration');
        return $setting === null ? true : (bool) $setting;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias(
        '\APP\plugins\generic\aiTransparencyDeclaration\AiTransparencyDeclarationPlugin',
        '\AiTransparencyDeclarationPlugin'
    );
}
