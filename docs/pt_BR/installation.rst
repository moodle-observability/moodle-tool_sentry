Instalação
==========

O pacote já traz o SDK PHP do Sentry na pasta ``vendor``: não é preciso rodar o Composer.

Pelo Diretório de Plugins
-------------------------

Baixe o ZIP em `moodle.org/plugins/tool_sentry <https://moodle.org/plugins/tool_sentry>`_ e instale em *Administração do site > Plugins > Instalar plugins*.

Com Git
-------

Na pasta raiz do Moodle (``public`` no Moodle 5.1 ou posterior), use a branch da sua versão do Moodle:

.. code-block:: bash

   # Moodle 5.3
   git clone -b TOOL_SENTRY_5_3 https://github.com/moodle-observability/moodle-tool_sentry.git admin/tool/sentry

   # Moodle 4.1 e 4.5
   git clone -b TOOL_SENTRY_1_0 https://github.com/moodle-observability/moodle-tool_sentry.git admin/tool/sentry

Depois abra *Administração do site > Notificações* para concluir a instalação, ou rode ``php admin/cli/upgrade.php``.

Em seguida, siga a :doc:`configuration`.

Desinstalação
-------------

1. Se usar o modo prepend, remova a linha ``auto_prepend_file`` do PHP e reinicie o PHP-FPM ou o Apache. Sem isso, o PHP procura um arquivo que deixou de existir e todas as páginas falham.
2. *Administração do site > Plugins > Visão geral dos plugins*, localize ``tool_sentry`` e clique em *Desinstalar*.
