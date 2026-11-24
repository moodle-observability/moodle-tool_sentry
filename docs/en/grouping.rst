Issue grouping
==============

Sentry groups events into issues by a *fingerprint*. When the fingerprint of one problem changes from event to event, the same error opens many issues, every new issue sends a "new issue" alert, and the history (first seen, number of events, users) is split. Two Moodle details cause this, and the plugin handles both.

The JavaScript revision in the URLs
-----------------------------------

Moodle serves JavaScript and theme files with a revision number in the URL, for example::

   https://moodle.example/lib/requirejs.php/1795490314/core/first.js
   https://moodle.example/lib/javascript.php/1795490314/lib/requirejs/require.min.js
   https://moodle.example/r.php/core/esm/1795490314/@moodle/lms/core/ajax
   https://moodle.example/theme/yui_combo.php?rollup/1795490314/mcore-min.js

The revision changes on every **Purge all caches** and on every upgrade. Sentry groups browser errors by the stack trace, which includes these URLs: without care, each purge creates a new copy of every browser issue.

When the plugin starts the browser SDK (setting ``javascriptloader``), it adds a ``beforeSend`` callback that replaces the revision (a path segment of 9 to 11 digits followed by ``/`` or ``_``) with ``-`` in the file names of the stack frames and in the error message. The URL above is sent as ``lib/requirejs.php/-/core/first.js``. Ids in other URLs, such as ``pluginfile.php/1080803/...``, are not changed.

Messages with a session id
--------------------------

The PHP exception for a session lock timeout has the session id in its message (``Unable to obtain lock for session id session_5c...``, error code ``sessioncannotobtainlock``). Each session would become its own issue. The plugin sets a fixed fingerprint (exception class and error code) for the error codes in ``\tool_sentry\local\prepend::FINGERPRINT_ERRORCODES`` (``sessioncannotobtainlock`` and ``sessionwaiterr``), in both the prepend mode and the plugin mode.

Server-side fingerprinting rules
--------------------------------

The changes above only affect new events: existing issues are not merged. Select the duplicated issues in Sentry and use **Merge**.

The revision is gone, but the minified code itself changes when an upgrade or a new plugin version changes the JavaScript bundle (line and column of ``core/first.js``). Some errors still open new issues after such deploys. Sentry's *fingerprinting rules* group by error type and message instead, regardless of the stack trace. They are set per project, in **Settings > Projects > (project) > Issue Grouping > Fingerprint Rules**, and apply to new events without any change in Moodle.

Rules that work well for Moodle 5.x (browser project). Adapt them to the errors of your site:

.. code-block:: text

   # Import maps / ES modules (browsers without import maps, page cached across a purge)
   error.value:"Failed to resolve module specifier*" -> moodle-esm-resolve
   error.value:"Module specifier, *does not start with*" -> moodle-esm-resolve
   error.value:"Importing a module script failed*" -> moodle-esm-import
   error.value:"error loading dynamically imported module*" -> moodle-esm-import
   error.value:"Script error for \"core/first\"*" -> moodle-requirejs-core-first
   # Course index / reactive course editor started without its main element
   error.value:"Reactive components needs a main DOM element*" -> moodle-reactive-main-dom
   # YUI modules (M.core) not loaded
   error.value:"*new M.core.exception*" -> moodle-yui-mcore-exception

Browsers translate some messages (Firefox in Portuguese: ``Erro ao resolver especificador de módulo``). Add one rule per language that shows up in your issues, pointing to the same fingerprint.

For the PHP project, if events from older versions of the plugin are still arriving:

.. code-block:: text

   error.value:"*Unable to obtain lock for session*" -> moodle-session-lock
   error.value:"*waiting for session lock*" -> moodle-session-lock

Keep the rules narrow: a rule that matches too much (for example any ``TypeError``) merges unrelated bugs into one issue and hides them.
