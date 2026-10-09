{**
 * Public, collapsible AITD certificate shown on article landing pages.
 *}

{if $aitdDeclaration}
<style type="text/css">
{literal}
.aitd-publication-block {
    --aitd-ink: #263449;
    --aitd-muted: #596579;
    --aitd-blue: #315d85;
    --aitd-blue-soft: #edf5fb;
    --aitd-gold: #aa7b27;
    --aitd-gold-soft: #fbf6ea;
    --aitd-line: #d5dce5;
    margin-block: 1rem;
    color: var(--aitd-ink);
}

.aitd-publication-block * {
    box-sizing: border-box;
}

.aitd-disclosure {
    overflow: hidden;
    border: 1px solid var(--aitd-line);
    border-radius: 0.35rem;
    background: #fff;
    box-shadow: 0 0.15rem 0.5rem rgba(30, 47, 68, 0.1);
}

.aitd-summary {
    display: flex;
    min-height: 3.5rem;
    align-items: center;
    gap: 0.55rem;
    padding: 0.45rem 0.6rem;
    cursor: pointer;
    list-style: none;
    color: #fff;
    background: linear-gradient(120deg, #263449 0%, #315d85 100%);
}

.aitd-summary::-webkit-details-marker {
    display: none;
}

.aitd-summary:focus-visible {
    outline: 2px solid #e2b75e;
    outline-offset: -2px;
}

.aitd-summary-copy {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-direction: column;
    gap: 0.18rem;
}

.aitd-summary-title {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 0.86rem;
    font-weight: 400;
    line-height: 1.25;
}

.aitd-summary-meta {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-wrap: wrap;
    font-size: 0.65rem;
    line-height: 1.15;
}

.aitd-status {
    display: inline-flex;
    align-items: center;
    padding: 0.1rem 0.35rem;
    border: 1px solid rgba(255, 255, 255, 0.42);
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.1);
    font-weight: 400;
}

.aitd-summary-action {
    color: rgba(255, 255, 255, 0.85);
}

.aitd-chevron {
    width: 0.55rem;
    height: 0.55rem;
    flex: 0 0 0.55rem;
    border-right: 1px solid #fff;
    border-bottom: 1px solid #fff;
    transform: rotate(45deg) translateY(-0.12rem);
    transition: transform 180ms ease;
}

.aitd-disclosure[open] .aitd-chevron {
    transform: rotate(225deg) translate(-0.08rem, -0.08rem);
}

.aitd-certificate {
    position: relative;
    max-height: 30rem;
    overflow-y: auto;
    padding: 0.85rem 0.75rem;
    border-top: 2px solid var(--aitd-gold);
    background:
        linear-gradient(rgba(255, 255, 255, 0.97), rgba(255, 255, 255, 0.97)),
        repeating-linear-gradient(45deg, #f3f5f8 0, #f3f5f8 2px, #fff 2px, #fff 10px);
    font-family: Arial, Helvetica, sans-serif;
    font-size: 0.86rem;
    font-weight: 400;
    line-height: 1.3;
    scrollbar-color: var(--aitd-gold) #edf0f4;
    scrollbar-width: thin;
}

.aitd-certificate::-webkit-scrollbar {
    width: 0.4rem;
}

.aitd-certificate::-webkit-scrollbar-track {
    background: #edf0f4;
}

.aitd-certificate::-webkit-scrollbar-thumb {
    border-radius: 999px;
    background: var(--aitd-gold);
}

.aitd-certificate::before {
    position: absolute;
    inset: 0.3rem;
    border: 1px solid rgba(170, 123, 39, 0.25);
    border-radius: 0.15rem;
    content: "";
    pointer-events: none;
}

.aitd-certificate > * {
    position: relative;
}

.aitd-certificate-header {
    margin-bottom: 0.5rem;
    text-align: left;
}

.aitd-certificate-header h3 {
    display: none;
}

.aitd-certificate-header p {
    margin: 0;
    color: var(--aitd-muted);
    font-family: Arial, Helvetica, sans-serif;
    font-size: 0.86rem;
    font-style: normal;
    font-weight: 400;
    line-height: 1.3;
}

.aitd-certificate-section {
    margin-top: 0.7rem;
}

.aitd-certificate h4 {
    margin: 0 0 0.35rem;
    color: var(--aitd-ink);
    font-family: Arial, Helvetica, sans-serif;
    font-size: 0.86rem;
    font-weight: 400;
    line-height: 1.2;
}

.aitd-domain-grid {
    display: block;
}

.aitd-domain {
    padding: 0.4rem 0;
    border: 0;
    border-top: 1px solid var(--aitd-line);
    border-radius: 0;
    background: transparent;
}

.aitd-domain h5 {
    margin: 0 0 0.2rem;
    color: var(--aitd-blue);
    font-size: 0.86rem;
    font-weight: 400;
    line-height: 1.25;
}

.aitd-domain ul {
    margin: 0;
    padding-inline-start: 1rem;
}

.aitd-domain li {
    line-height: 1.35;
}

.aitd-domain li + li {
    margin-top: 0.18rem;
}

.aitd-technical dl {
    margin: 0;
    border-top: 1px solid var(--aitd-line);
}

.aitd-technical dl > div {
    display: grid;
    grid-template-columns: minmax(5.5rem, 0.4fr) 1fr;
    gap: 0.4rem;
    padding: 0.3rem 0;
    border-bottom: 1px solid var(--aitd-line);
}

.aitd-technical dt {
    font-weight: 400;
}

.aitd-technical dd {
    min-width: 0;
    margin: 0;
    overflow-wrap: anywhere;
}

.aitd-technical a {
    color: var(--aitd-blue);
    text-decoration-thickness: 1px;
    text-underline-offset: 0.12em;
}

.aitd-oversight {
    margin-top: 0.7rem;
    padding: 0.45rem 0.55rem;
    border-left: 2px solid var(--aitd-blue);
    border-radius: 0 0.2rem 0.2rem 0;
    background: var(--aitd-blue-soft);
}

.aitd-oversight h4,
.aitd-oversight p {
    margin-top: 0;
}

.aitd-oversight p {
    margin-bottom: 0.3rem;
    line-height: 1.25;
}

.aitd-oversight strong {
    font-weight: 400;
}

.aitd-oversight p:last-child {
    margin-bottom: 0;
}

.aitd-no-use {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0.6rem 0;
    padding: 0.55rem 0.65rem;
    border: 1px solid #d9c38f;
    border-radius: 0.25rem;
    background: var(--aitd-gold-soft);
    font-size: 0.86rem;
}

.aitd-no-use p {
    margin: 0;
}

.aitd-check {
    display: inline-flex;
    width: 1.45rem;
    height: 1.45rem;
    flex: 0 0 1.45rem;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--aitd-gold);
    border-radius: 50%;
    color: #785719;
    font-size: 0.72rem;
    font-weight: 800;
}

.aitd-certificate-footer {
    margin-top: 0.75rem;
    padding-top: 0.45rem;
    border-top: 1px dashed #a9b1bc;
    color: var(--aitd-muted);
    font-size: 0.63rem;
    line-height: 1.3;
}

.aitd-certificate-footer a {
    color: var(--aitd-blue);
    font-style: normal;
    text-decoration: underline;
    text-underline-offset: 0.12em;
}

@media (max-width: 36rem) {
    .aitd-summary {
        padding-inline: 0.5rem;
    }

    .aitd-technical dl > div {
        grid-template-columns: 1fr;
        gap: 0.08rem;
    }
}

@media print {
    .aitd-publication-block details:not([open]) > .aitd-certificate {
        display: block;
    }

    .aitd-summary-action,
    .aitd-chevron {
        display: none;
    }

    .aitd-disclosure {
        box-shadow: none;
    }

    .aitd-certificate {
        max-height: none;
        overflow: visible;
    }
}
{/literal}
</style>
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
