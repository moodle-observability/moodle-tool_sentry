Installation
============

The package already ships the Sentry PHP SDK in the ``vendor`` folder: you do not need to run Composer.

From the Plugins directory
--------------------------

Download the ZIP from `moodle.org/plugins/tool_sentry <https://moodle.org/plugins/tool_sentry>`_ and install it in *Site administration > Plugins > Install plugins*.

With Git
--------

In the Moodle root folder (``public`` on Moodle 5.1 or later), use the branch of your Moodle version:

.. code-block:: bash

   # Moodle 5.3
   git clone -b TOOL_SENTRY_5_3 https://github.com/moodle-observability/moodle-tool_sentry.git admin/tool/sentry

   # Moodle 4.1 and 4.5
   git clone -b TOOL_SENTRY_1_0 https://github.com/moodle-observability/moodle-tool_sentry.git admin/tool/sentry

Then open *Site administration > Notifications* to finish the installation, or run ``php admin/cli/upgrade.php``.

Next, follow the :doc:`configuration`.

Uninstall
---------

1. If you use the prepend mode, remove the ``auto_prepend_file`` line from PHP and restart PHP-FPM or Apache. Otherwise PHP looks for a file that no longer exists and every page fails.
2. *Site administration > Plugins > Plugins overview*, find ``tool_sentry`` and click *Uninstall*.
