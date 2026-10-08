**Documentation:** [Español](README.md) | English
# AI Transparency Declaration (AITD) Plugin

A plugin designed to integrate the **AI Transparency Declaration (AITD) Model** into Public Knowledge Project (PKP) publishing platforms: **Open Journal Systems (OJS), Open Monograph Press (OMP), and Open Preprint Systems (OPS)**.

The project aims to incorporate structured AI-use disclosures into the editorial workflows of scholarly journals, scholarly book publishing platforms, and preprint servers.


## Platforms and compatibility

| Platform | Status | Tested versions |
|---|---|---|
| Open Journal Systems (OJS) | Available | 3.3.0-22, 3.4.0-10, 3.5.0-5 |
| Open Preprint Systems (OPS) | Available | 3.5.0-5 |
| Open Monograph Press (OMP) | Planned | Not yet available |

The plugin is distributed in separate packages for the supported platforms and versions. The versions listed above refer exclusively to distributions in which the plugin has been tested. They should not be interpreted as a guarantee of compatibility with all releases within the same version family.

## Original model and attribution

The plugin adapts the **AI Transparency Declaration (AITD) Model v1.1**, developed by **Sergio Santamarina and Carlos Authier**, with contributions from **Aamir Sohail**.

Original model: https://doi.org/10.5281/zenodo.18601557

The technical adaptation for PKP platforms is developed by **Ismael Cáceres-Correa**, **Sociedad Realidad e Historia**.

This implementation does not claim authorship of the original conceptual model. It provides a software adaptation for integration into PKP editorial workflows.

For further attribution details, see [CREDITS.md](CREDITS.md).

## License

The plugin is distributed under the **GNU Affero General Public License, version 3.0 only (AGPL-3.0-only)**.

See [LICENSE](LICENSE) for the complete license terms.

## Text field security

In the documented OJS implementation, fields for AI tools, versions, record links, and validation descriptions use plain-text controls.

Values displayed on public article pages are escaped using Smarty's `|escape` modifier.

Record links are validated as HTTP(S) URLs at the schema level and checked again before display.

Declaration data is persisted through OJS's native publication schema and repository, which store the information in `publication_settings`. The plugin does not perform direct SQL insertions.

These implementation details describe the documented OJS version and must be verified separately for other platform distributions.
