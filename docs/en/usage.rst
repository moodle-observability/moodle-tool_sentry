Usage
=====

Once configured, the plugin works on its own: every Moodle error or exception becomes an event in the Sentry project, grouped into issues.

Test the connection
-------------------

In *Site administration > Plugins > Admin tools > Sentry Configuration*, click *Send Exception*. The plugin throws a test exception, sends it to Sentry and shows the result below the button. The exception should appear in the project within a few seconds.

What reaches Sentry
-------------------

.. list-table::
   :header-rows: 1
   :widths: 52 18 30

   * - Data
     - tool_sentry
     - tool_otel
   * - PHP exceptions and errors, with stack trace and the ``moodle_component`` tag.
     - ✅
     - ❌
   * - Fatal errors, captured when the script shuts down.
     - ✅
     - ❌
   * - Errors of scheduled and adhoc tasks, with the ``task`` context (URL ``cron://<host>/task/<component>/<class>``) and the ``task_type``, ``task_class``, ``task_component``, ``task_id`` and ``task_logid`` tags.
     - ✅
     - ✅ Planned, as task spans.
   * - Browser JavaScript errors, when the JavaScript Loader is configured.
     - ✅
     - ❌
   * - Transactions and profiles, when tracing is on.
     - ⚠️ Only what the SDK creates on its own; the plugin creates no Moodle spans.
     - ✅ Planned: spans for requests, database, ``curl``, web services and tasks.

Error page with the event id
----------------------------

In prepend mode, the id of the captured event comes back in the ``X-Sentry-Event-Id`` response header. The web server can show this id on its error page, so users can give it to support. This feature does not exist in ``tool_otel``.

Tasks in cron
-------------

In prepend mode, cron sends errors only if the ``SENTRY_*`` variables are in the environment of the shell that runs ``admin/cli/cron.php``. In plugin mode, cron uses the settings saved in the database.
