# Declaración de transparencia sobre el uso de IA para OPS

Versión adaptada para **Open Preprint Systems (OPS) 3.5**, probada estructuralmente contra el código fuente de OPS 3.5.0-5. Versión del módulo: **0.3.0.1**.

La declaración es obligatoria de forma predeterminada. La administración del servidor de preprints puede convertirla en opcional desde la configuración del módulo.

## Funcionamiento

El módulo incorpora una declaración única para cada publicación o versión del preprint:

- aparece en el formulario de detalles durante el envío;
- permanece disponible en los metadatos del preprint durante el flujo editorial;
- conserva sus valores en `publication_settings` mediante las propiedades añadidas al esquema de publicación;
- presenta una declaración pública desplegable cuando la información está completa;
- añade metadatos legibles por máquinas en la página pública del preprint;
- incluye interfaces en español, inglés, portugués y portugués de Brasil.

El módulo no crea tablas adicionales.

## Modelo y licencia

El contenido del formulario adapta el **AI Transparency Declaration (AITD) Model v1.1**, creado por Sergio Santamarina y Carlos Authier, con contribuciones de Aamir Sohail. Consulte [CREDITS.md](CREDITS.md).

La adaptación de la declaración para su uso en OPS fue realizada por **Ismael Cáceres-Correa**, **Sociedad Realidad e Historia**.

Este módulo se distribuye bajo la Licencia Pública General Affero de GNU, versión 3.0.


## Seguridad de los campos de texto

Los campos de herramientas, versiones, enlace de registro y descripción de validación utilizan controles de texto plano. Los valores visibles en la página pública se escapan con Smarty `|escape`. El enlace se valida en el esquema como URL HTTP(S) y vuelve a comprobarse antes de mostrarse. Los datos de la declaración se persisten mediante el esquema y el repositorio nativos de publicaciones de OPS, que los almacenan en `publication_settings`; el módulo no ejecuta inserciones SQL directas.
