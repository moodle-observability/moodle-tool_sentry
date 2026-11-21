Uso
===

Depois de configurado, o plugin trabalha sozinho: cada erro ou exceção do Moodle vira um evento no projeto do Sentry, agrupado em issues.

Testar a conexão
----------------

Em *Administração do site > Plugins > Ferramentas de administração > Sentry Configuration*, clique em *Send Exception*. O plugin lança uma exceção de teste, envia para o Sentry e mostra o resultado abaixo do botão. A exceção deve aparecer no projeto em alguns segundos.

O que chega ao Sentry
---------------------

.. list-table::
   :header-rows: 1
   :widths: 52 18 30

   * - Dado
     - tool_sentry
     - tool_otel
   * - Exceções e erros PHP, com stack trace e a tag ``moodle_component``.
     - ✅
     - ❌
   * - Erros fatais, capturados no encerramento do script.
     - ✅
     - ❌
   * - Erros de tarefas agendadas e adhoc, com o contexto ``task`` (URL ``cron://<host>/task/<componente>/<classe>``) e as tags ``task_type``, ``task_class``, ``task_component``, ``task_id`` e ``task_logid``.
     - ✅
     - ✅ Previsto, como spans de tarefas.
   * - Erros de JavaScript do navegador, quando o JavaScript Loader está configurado.
     - ✅
     - ❌
   * - Transações e profiles, quando o tracing está ativo.
     - ⚠️ Só o que o SDK cria sozinho; o plugin não cria spans do Moodle.
     - ✅ Previsto: spans de requisições, banco, ``curl``, web services e tarefas.

Página de erro com o id do evento
---------------------------------

No modo prepend, o id do evento capturado volta no header ``X-Sentry-Event-Id`` da resposta. O servidor web pode mostrar esse id na página de erro, para o usuário informar ao suporte. Esse recurso não existe no ``tool_otel``.

Tarefas no cron
---------------

No modo prepend, o cron só envia erros se as variáveis ``SENTRY_*`` estiverem no ambiente do shell que roda ``admin/cli/cron.php``. No modo plugin, o cron usa as configurações salvas no banco.
