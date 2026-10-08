{**
 * Public, collapsible AITD certificate shown on article landing pages.
 *}

{if $aitdDeclaration}
<section
    class="item aitd-publication-block"
    lang="{$aitdDeclaration.lang|escape}"
    xml:lang="{$aitdDeclaration.lang|escape}"
    data-aitd-version="1.1"
    data-ai-use="{$aitdDeclaration.status|escape}"
>
    <details class="aitd-disclosure">
        <summary class="aitd-summary">
            <span class="aitd-summary-copy">
                <span class="aitd-summary-title">{$aitdDeclaration.title|escape}</span>
                <span class="aitd-summary-meta">
                    <span class="aitd-status aitd-status--{$aitdDeclaration.status|escape}">{$aitdDeclaration.statusLabel|escape}</span>
                    <span class="aitd-summary-action">{$aitdDeclaration.toggleLabel|escape}</span>
                </span>
            </span>
            <span class="aitd-chevron" aria-hidden="true"></span>
        </summary>

        <div class="aitd-certificate">
            <header class="aitd-certificate-header">
                <h3>{$aitdDeclaration.title|escape}</h3>
                {if $aitdDeclaration.status == 'used'}
                    <p>{$aitdDeclaration.introduction|escape}</p>
                {/if}
            </header>

            {if $aitdDeclaration.status == 'none'}
                <div class="aitd-no-use">
                    <span class="aitd-check" aria-hidden="true">✓</span>
                    <p>{$aitdDeclaration.noAiStatement|escape}</p>
                </div>
            {else}
                <section class="aitd-certificate-section" aria-labelledby="aitd-declared-uses">
                    <h4 id="aitd-declared-uses">{$aitdDeclaration.declaredUsesLabel|escape}</h4>
                    <div class="aitd-domain-grid">
                        {foreach from=$aitdDeclaration.domains item=domain}
                            <section class="aitd-domain">
                                <h5>{$domain.label|escape}</h5>
                                <ul>
                                    {foreach from=$domain.uses item=use}
                                        <li>{$use|escape}</li>
                                    {/foreach}
                                </ul>
                            </section>
                        {/foreach}
                    </div>
                </section>

                <section class="aitd-certificate-section aitd-technical" aria-labelledby="aitd-technical-details">
                    <h4 id="aitd-technical-details">{$aitdDeclaration.technicalDetailsLabel|escape}</h4>
                    <dl>
                        <div>
                            <dt>{$aitdDeclaration.toolsLabel|escape}</dt>
                            <dd>{$aitdDeclaration.tools|escape}</dd>
                        </div>
                        {if $aitdDeclaration.versions}
                            <div>
                                <dt>{$aitdDeclaration.versionsLabel|escape}</dt>
                                <dd>{$aitdDeclaration.versions|escape}</dd>
                            </div>
                        {/if}
                        {if $aitdDeclaration.promptLog}
                            <div>
                                <dt>{$aitdDeclaration.promptLogLabel|escape}</dt>
                                <dd><a href="{$aitdDeclaration.promptLog|escape}" target="_blank" rel="noopener noreferrer">{$aitdDeclaration.promptLog|escape}</a></dd>
                            </div>
                        {/if}
                    </dl>
                </section>

                <section class="aitd-oversight" aria-labelledby="aitd-human-oversight">
                    <h4 id="aitd-human-oversight">{$aitdDeclaration.humanOversightLabel|escape}</h4>
                    <p><strong>{$aitdDeclaration.validationLabel|escape}:</strong> {$aitdDeclaration.validation|escape}</p>
                    <p><strong>{$aitdDeclaration.responsibilityLabel|escape}:</strong> {$aitdDeclaration.responsibilityStatement|escape}</p>
                </section>
            {/if}

            <footer class="aitd-certificate-footer">
                <a href="https://doi.org/10.5281/zenodo.18601557" target="_blank" rel="noopener noreferrer">{$aitdDeclaration.attribution|escape}</a>
            </footer>
        </div>
    </details>
</section>
{/if}
