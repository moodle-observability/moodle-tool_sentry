moodle-tool_sentry
==================

**Sentry error tracking and APM for Moodle.**

Sends Moodle errors, exceptions and performance data to `Sentry <https://sentry.io/>`_, in the cloud (sentry.io) or self-hosted. It can start before Moodle, through ``auto_prepend_file``, and so capture even database connection failures.

Features
--------

The ``tool_otel`` column shows whether the `moodle-tool_otel <https://moodle-observability.github.io/moodle-tool_otel/>`_ plugin (OpenTelemetry tracing, version 0.1.0) also offers the feature. See the full comparison in :doc:`otel-vs-sentry`.

.. list-table::
   :header-rows: 1
   :widths: 52 18 30

   * - Feature
     - tool_sentry
     - tool_otel
   * - Capture of exceptions and PHP errors with their real source file and line.
     - ✅
     - ❌
   * - Capture of fatal errors (``E_ERROR``, ``E_PARSE``...) when the script shuts down.
     - ✅
     - ❌
   * - Prepend mode: starts before ``config.php`` and captures errors that happen before the database, such as "Database connection failed".
     - ✅
     - ❌
   * - ``moodle_component`` tag with the Moodle component where the error happened.
     - ✅
     - ❌
   * - Context of scheduled and adhoc tasks (type, class, component, id and log of the task).
     - ✅ On errors.
     - ✅ Task spans, with ``$CFG->task_log_class``.
   * - Browser JavaScript errors, through the Sentry JavaScript Loader.
     - ✅
     - ❌
   * - Stable issue grouping: browser errors without the JavaScript revision of the URLs, session lock errors without the session id (:doc:`grouping`).
     - ✅
     - ❌
   * - Performance tracing.
     - ⚠️ Partial: enables SDK tracing (``enable_tracing``, ``traces_sample_rate``), without Moodle spans.
     - ⚠️ Partial: spans for requests, web services and tasks, database aggregates per request; no ``curl`` spans.
   * - Profiling (``profiles_sample_rate``).
     - ⚠️ Depends on SDK tracing.
     - ❌
   * - Sending to Sentry (sentry.io or self-hosted).
     - ✅
     - ⚠️ Traces only, through OTLP.
   * - Sending to other backends (Jaeger, Grafana Tempo and other OTLP-compatible backends).
     - ❌
     - ✅
   * - Data protection: values of sensitive parameters (``token``, ``password``, ``sesskey``...) replaced with ``[Filtered]`` and no personal data by default.
     - ⚠️ In prepend mode only.
     - ✅ Spans without personal data and without query strings; ``redact_span`` hook.
   * - Id of the captured event in the ``X-Sentry-Event-Id`` response header, for the web server error page.
     - ⚠️ In prepend mode only.
     - ❌
   * - Connection test button (*Send Exception*) on the settings page.
     - ✅
     - ❌

Legend: ✅ available; ⚠️ partial or only under the stated condition; ❌ not offered.

Requirements
------------

.. list-table::
   :header-rows: 1

   * - Plugin version
     - Moodle
     - PHP
   * - 5.3.x (branch ``TOOL_SENTRY_5_3``)
     - 5.3
     - 8.3+
   * - 1.0.x (branch ``TOOL_SENTRY_1_0``)
     - 4.1 and 4.5
     - 8.1+

You also need a Sentry project and its DSN.

Links
-----

* `Source code <https://github.com/moodle-observability/moodle-tool_sentry>`_
* `Issues <https://github.com/moodle-observability/moodle-tool_sentry/issues>`_
* `Moodle Plugins directory <https://moodle.org/plugins/tool_sentry>`_
* `Moodle Observability <https://moodle-observability.github.io/>`_

.. toctree::
   :hidden:
   :maxdepth: 1

   installation
   configuration
   usage
   grouping
   otel-vs-sentry
