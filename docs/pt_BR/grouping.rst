Agrupamento de issues
=====================

O Sentry agrupa os eventos em issues por uma *impressão digital* (fingerprint). Quando a impressão digital de um mesmo problema muda de um evento para outro, o mesmo erro abre várias issues, cada issue nova dispara um alerta de "nova issue" e o histórico (primeira ocorrência, número de eventos, usuários) se divide. Dois detalhes do Moodle causam isso, e o plugin trata os dois.

A revisão do JavaScript nas URLs
--------------------------------

O Moodle entrega os arquivos de JavaScript e do tema com um número de revisão na URL, por exemplo::

   https://moodle.example/lib/requirejs.php/1795490314/core/first.js
   https://moodle.example/lib/javascript.php/1795490314/lib/requirejs/require.min.js
   https://moodle.example/r.php/core/esm/1795490314/@moodle/lms/core/ajax
   https://moodle.example/theme/yui_combo.php?rollup/1795490314/mcore-min.js

A revisão muda a cada **Limpar todos os caches** e a cada atualização. O Sentry agrupa os erros do navegador pela pilha de chamadas, que inclui essas URLs: sem cuidado, cada limpeza de cache cria uma cópia nova de todas as issues do navegador.

Quando o plugin inicia o SDK do navegador (configuração ``javascriptloader``), ele acrescenta um ``beforeSend`` que troca a revisão (um trecho do caminho com 9 a 11 dígitos seguido de ``/`` ou ``_``) por ``-`` nos nomes de arquivo da pilha e na mensagem de erro. A URL acima é enviada como ``lib/requirejs.php/-/core/first.js``. Ids em outras URLs, como ``pluginfile.php/1080803/...``, não mudam.

Mensagens com o id da sessão
----------------------------

A exceção do PHP para o tempo esgotado do bloqueio de sessão traz o id da sessão na mensagem (``Não foi possível obter o bloqueio para a sessão id session_5c...``, código ``sessioncannotobtainlock``). Cada sessão viraria uma issue. O plugin fixa a impressão digital (classe da exceção e código do erro) para os códigos em ``\tool_sentry\local\prepend::FINGERPRINT_ERRORCODES`` (``sessioncannotobtainlock`` e ``sessionwaiterr``), no modo prepend e no modo plugin.

Regras de agrupamento no Sentry
-------------------------------

As mudanças acima valem só para os eventos novos: as issues que já existem não são unidas. Selecione as issues duplicadas no Sentry e use **Merge**.

A revisão some, mas o próprio código minificado muda quando uma atualização ou uma nova versão de plugin altera o pacote de JavaScript (linha e coluna do ``core/first.js``). Alguns erros ainda abrem issues novas depois desses deploys. As *fingerprinting rules* do Sentry agrupam pelo tipo e pela mensagem do erro, sem olhar a pilha. Elas ficam em cada projeto, em **Settings > Projects > (projeto) > Issue Grouping > Fingerprint Rules**, e valem para os eventos novos sem mudar nada no Moodle.

Regras que funcionam bem para o Moodle 5.x (projeto do navegador). Adapte aos erros do seu site:

.. code-block:: text

   # Import maps / módulos ES (navegadores sem import maps, página em cache depois de limpar o cache)
   error.value:"Failed to resolve module specifier*" -> moodle-esm-resolve
   error.value:"Module specifier, *does not start with*" -> moodle-esm-resolve
   error.value:"Importing a module script failed*" -> moodle-esm-import
   error.value:"error loading dynamically imported module*" -> moodle-esm-import
   error.value:"Script error for \"core/first\"*" -> moodle-requirejs-core-first
   # Índice do curso / editor reativo do curso iniciado sem o elemento principal
   error.value:"Reactive components needs a main DOM element*" -> moodle-reactive-main-dom
   # Módulos YUI (M.core) não carregados
   error.value:"*new M.core.exception*" -> moodle-yui-mcore-exception

Os navegadores traduzem algumas mensagens (Firefox em português: ``Erro ao resolver especificador de módulo``). Acrescente uma regra por idioma que aparecer nas suas issues, apontando para a mesma impressão digital.

No projeto do PHP, se ainda chegarem eventos de versões antigas do plugin:

.. code-block:: text

   error.value:"*bloqueio para a sess*" -> moodle-session-lock
   error.value:"*Unable to obtain lock for session*" -> moodle-session-lock
   error.value:"*waiting for session lock*" -> moodle-session-lock

Mantenha as regras estreitas: uma regra que pega demais (por exemplo, qualquer ``TypeError``) junta bugs diferentes numa issue só e os esconde.
