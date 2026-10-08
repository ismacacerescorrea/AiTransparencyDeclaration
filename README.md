
## Modelo y licencia

El contenido del formulario adapta el **AI Transparency Declaration (AITD) Model v1.1**, creado por Sergio Santamarina y Carlos Authier, con contribuciones de Aamir Sohail. Consulte [CREDITS.md](CREDITS.md).

La adaptación de la declaración para su uso en OJS fue realizada por **Ismael Cáceres-Correa**, **Sociedad Realidad e Historia**.

Este módulo se distribuye bajo la Licencia Pública General Affero de GNU, versión 3.0.

## Seguridad de los campos de texto

Los campos de herramientas, versiones, enlace de registro y descripción de validación utilizan controles de texto plano. Los valores visibles en la página pública se escapan con Smarty `|escape`. El enlace se valida en el esquema como URL HTTP(S) y vuelve a comprobarse antes de mostrarse. Los datos de la declaración se persisten mediante el esquema y el repositorio nativos de publicaciones de OJS, que los almacenan en `publication_settings`; el módulo no ejecuta inserciones SQL directas.
