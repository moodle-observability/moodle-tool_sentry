Configuration
=============

The plugin has two modes. The prepend mode wins when it is configured; otherwise the plugin mode is used.

The ``tool_otel`` column of the tables shows whether the `moodle-tool_otel <https://moodle-observability.github.io/moodle-tool_otel/>`_ plugin has an equivalent planned setting.

Plugin mode (default)
---------------------

Configure it in *Site administration > Plugins > Admin tools > Sentry Configuration*. The SDK is started on the first Moodle event of each request, with the settings saved in the database. Errors that happen before that point (for example "Database connection failed") are not sent.

.. list-table::
   :header-rows: 1
   :widths: 25 47 28

   * - Setting
     - Description
     - tool_otel
   * - ``activate``
     - Turns on sending to Sentry.
     - ❌
   * - ``dsn``
     - DSN of the Sentry project.
     - ✅ Equivalent: OTLP endpoint, protocol and headers.
   * - ``javascriptloader``
     - URL of the project's JavaScript Loader, to capture browser errors.
     - ❌
   * - ``release``
     - Release name.
     - ❌
   * - ``environment``
     - Environment name (``production`` by default).
     - ❌
   * - ``server_name``
     - Server name sent with the events.
     - ❌
   * - ``send_default_pii``
     - Sends personal data added by the SDK integrations. Off by default.
     - ❌
   * - ``sample_rate``
     - Error sample rate, from 0.0 to 1.0.
     - ❌
   * - ``enable_tracing``
     - Turns on SDK tracing.
     - ✅ The whole plugin is about tracing.
   * - ``traces_sample_rate``
     - Trace sample rate, from 0.0 to 1.0.
     - ✅ Sampling rate.
   * - ``profiles_sample_rate``
     - Profiling sample rate. Depends on tracing.
     - ❌
   * - ``max_breadcrumbs``
     - Maximum number of breadcrumbs per event.
     - ❌
   * - ``max_request_body_size``
     - How much of the request body is sent (``never``, ``small``, ``medium``, ``always``).
     - ❌
   * - ``attach_stacktrace``
     - Attaches a stack trace to messages too, not only to exceptions.
     - ❌
   * - ``max_value_length``
     - Maximum length of event texts before they are cut.
     - ❌
   * - ``error_types``
     - Error types reported, in the format of PHP's ``error_reporting``.
     - ❌
   * - ``ignore_exceptions``
     - Exception classes that are not sent.
     - ❌
   * - ``ignore_transactions``
     - Transaction names that are not sent.
     - ❌
   * - ``in_app_include`` and ``in_app_exclude``
     - Module prefixes that belong or do not belong to the application, to highlight your own code in the stack trace.
     - ❌

The page also has the connection test button (see :doc:`usage`).

Prepend mode (before Moodle)
----------------------------

Available from version 5.3.0 (Moodle 5.3). PHP loads ``admin/tool/sentry/prepend.php`` before every script (web and CLI), so Sentry starts before ``config.php``, the database and the plugin settings. The mode is used only when **both** conditions are true:

1. ``auto_prepend_file`` points to the plugin's ``prepend.php``;
2. the ``SENTRY_DSN`` environment variable is set.

In ``php.ini`` (or a file in ``conf.d``, for example ``30-sentry.ini``):

.. code-block:: ini

   ; Moodle 5.3 keeps the code in public/.
   auto_prepend_file=/var/www/html/public/admin/tool/sentry/prepend.php

In this mode the plugin does not start the SDK again and ignores the PHP options of the settings page; only the JavaScript Loader is still used. The settings page shows a notice when the mode is active.

.. list-table::
   :header-rows: 1
   :widths: 25 20 27 28

   * - Variable
     - Default
     - Description
     - tool_otel
   * - ``SENTRY_DSN``
     -
     - DSN of the project. Required.
     - ❌ Environment variables not planned.
   * - ``SENTRY_ENVIRONMENT``
     - ``production``
     - Environment name.
     - ❌
   * - ``SENTRY_RELEASE``
     -
     - Release name, for example the image tag (``site@1.2.3``).
     - ❌
   * - ``SENTRY_SAMPLE_RATE``
     - ``1.0``
     - Error sample rate, from 0.0 to 1.0.
     - ❌
   * - ``SENTRY_IGNORE_EXCEPTIONS``
     - ``require_login_exception``, ``require_login_session_timeout_exception``, ``required_capability_exception``, ``invalid_parameter_exception``
     - Comma separated exception classes; replaces the default list.
     - ❌
   * - ``SENTRY_SEND_USER_ID``
     - ``false``
     - ``true`` sends only the numeric id of the Moodle user.
     - ❌

Fixed options in this mode: request bodies are never sent; no personal data is sent by default; the values of sensitive parameters (``token``, ``wstoken``, ``password``, ``sesskey``, ``key``, ``secret``, ``code``, ``ticket``...) are replaced with ``[Filtered]``; events are tagged with the Moodle component, the error code and the running task.

The variables must reach PHP:

* **PHP-FPM**: ``clear_env = no`` in the pool, or ``env[SENTRY_DSN] = ...`` (or ``fastcgi_param SENTRY_DSN ...`` in nginx).
* **Apache (mod_php)**: ``SetEnv SENTRY_DSN ...``.
* **Docker**: ``environment:`` of the PHP container.
* **CLI (cron)**: the variables of the shell that runs ``admin/cli/cron.php``.

Optional: exceptions thrown inside ``lib/setup.php`` (for example, with the database down) reach Moodle's handler before the plugin can chain the handlers. To capture them too, wrap the last line of ``config.php``:

.. code-block:: php

   try {
       require_once(__DIR__ . '/lib/setup.php');
   } catch (\Throwable $e) {
       if (class_exists('\tool_sentry\local\prepend', false)) {
           \tool_sentry\local\prepend::capture($e);
       }
       throw $e;
   }

To go back to the plugin mode, remove ``auto_prepend_file`` or unset ``SENTRY_DSN``.
