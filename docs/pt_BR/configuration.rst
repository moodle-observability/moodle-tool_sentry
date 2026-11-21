Configuração
============

O plugin tem dois modos. O modo prepend tem prioridade quando está configurado; caso contrário, vale o modo plugin.

A coluna ``tool_otel`` das tabelas indica se o plugin `moodle-tool_otel <https://moodle-observability.github.io/moodle-tool_otel/>`_ tem configuração equivalente.

Modo plugin (padrão)
--------------------

Configure em *Administração do site > Plugins > Ferramentas de administração > Sentry Configuration*. O SDK é iniciado no primeiro evento do Moodle de cada requisição, com as configurações salvas no banco. Erros anteriores a esse ponto (por exemplo, "Database connection failed") não são enviados.

.. list-table::
   :header-rows: 1
   :widths: 25 47 28

   * - Configuração
     - Descrição
     - tool_otel
   * - ``activate``
     - Ativa o envio para o Sentry.
     - ❌
   * - ``dsn``
     - DSN do projeto no Sentry.
     - ✅ Equivalente: endpoint, protocolo e headers do OTLP.
   * - ``javascriptloader``
     - URL do JavaScript Loader do projeto, para capturar erros do navegador.
     - ❌
   * - ``release``
     - Nome da release.
     - ❌
   * - ``environment``
     - Nome do ambiente (``production`` por padrão).
     - ❌
   * - ``server_name``
     - Nome do servidor enviado com os eventos.
     - ❌
   * - ``send_default_pii``
     - Envia dados pessoais adicionados pelas integrações do SDK. Desligado por padrão.
     - ❌
   * - ``sample_rate``
     - Taxa de amostragem dos erros, de 0.0 a 1.0.
     - ❌
   * - ``enable_tracing``
     - Ativa o tracing do SDK.
     - ✅ O plugin inteiro é de tracing.
   * - ``traces_sample_rate``
     - Taxa de amostragem dos traces, de 0.0 a 1.0.
     - ✅ Taxa de amostragem.
   * - ``profiles_sample_rate``
     - Taxa de amostragem do profiling. Depende do tracing.
     - ❌
   * - ``max_breadcrumbs``
     - Quantidade máxima de breadcrumbs por evento.
     - ❌
   * - ``max_request_body_size``
     - Quanto do corpo da requisição é enviado (``never``, ``small``, ``medium``, ``always``).
     - ❌
   * - ``attach_stacktrace``
     - Anexa stack trace também às mensagens, além das exceções.
     - ❌
   * - ``max_value_length``
     - Tamanho máximo dos textos do evento antes de serem cortados.
     - ❌
   * - ``error_types``
     - Tipos de erro reportados, no formato do ``error_reporting`` do PHP.
     - ❌
   * - ``ignore_exceptions``
     - Classes de exceção que não são enviadas.
     - ❌
   * - ``ignore_transactions``
     - Nomes de transações que não são enviadas.
     - ❌
   * - ``in_app_include`` e ``in_app_exclude``
     - Prefixos de módulos que pertencem ou não à aplicação, para destacar o código próprio no stack trace.
     - ❌

A página também tem o botão de teste de conexão (veja :doc:`usage`).

Modo prepend (antes do Moodle)
------------------------------

Disponível a partir da versão 5.3.0 (Moodle 5.3). O PHP carrega ``admin/tool/sentry/prepend.php`` antes de cada script (web e CLI), e o Sentry inicia antes do ``config.php``, do banco e das configurações do plugin. O modo só é usado quando as **duas** condições valem:

1. ``auto_prepend_file`` aponta para o ``prepend.php`` do plugin;
2. a variável de ambiente ``SENTRY_DSN`` está definida.

No ``php.ini`` (ou num arquivo em ``conf.d``, por exemplo ``30-sentry.ini``):

.. code-block:: ini

   ; O Moodle 5.3 mantém o código em public/.
   auto_prepend_file=/var/www/html/public/admin/tool/sentry/prepend.php

Neste modo, o plugin não inicia o SDK de novo e ignora as opções PHP da página de configuração; só o JavaScript Loader continua sendo usado. A página de configuração mostra um aviso quando o modo está ativo.

.. list-table::
   :header-rows: 1
   :widths: 25 20 27 28

   * - Variável
     - Padrão
     - Descrição
     - tool_otel
   * - ``SENTRY_DSN``
     -
     - DSN do projeto. Obrigatória.
     - ✅ ``OTEL_EXPORTER_OTLP_ENDPOINT`` e as demais variáveis ``OTEL_*``.
   * - ``SENTRY_ENVIRONMENT``
     - ``production``
     - Nome do ambiente.
     - ❌
   * - ``SENTRY_RELEASE``
     -
     - Nome da release, por exemplo a tag da imagem (``site@1.2.3``).
     - ❌
   * - ``SENTRY_SAMPLE_RATE``
     - ``1.0``
     - Taxa de amostragem dos erros, de 0.0 a 1.0.
     - ❌
   * - ``SENTRY_IGNORE_EXCEPTIONS``
     - ``require_login_exception``, ``require_login_session_timeout_exception``, ``required_capability_exception``, ``invalid_parameter_exception``
     - Classes de exceção separadas por vírgula; substitui a lista padrão.
     - ❌
   * - ``SENTRY_SEND_USER_ID``
     - ``false``
     - ``true`` envia só o id numérico do usuário do Moodle.
     - ❌

Opções fixas neste modo: o corpo das requisições nunca é enviado; nenhum dado pessoal é enviado por padrão; os valores de parâmetros sensíveis (``token``, ``wstoken``, ``password``, ``sesskey``, ``key``, ``secret``, ``code``, ``ticket``...) viram ``[Filtered]``; os eventos recebem tags com o componente do Moodle, o código do erro e a tarefa em execução.

As variáveis precisam chegar ao PHP:

* **PHP-FPM**: ``clear_env = no`` no pool, ou ``env[SENTRY_DSN] = ...`` (ou ``fastcgi_param SENTRY_DSN ...`` no nginx).
* **Apache (mod_php)**: ``SetEnv SENTRY_DSN ...``.
* **Docker**: ``environment:`` do container PHP.
* **CLI (cron)**: as variáveis do shell que roda ``admin/cli/cron.php``.

Opcional: exceções lançadas dentro de ``lib/setup.php`` (por exemplo, com o banco fora do ar) chegam ao handler do Moodle antes que o plugin encadeie os handlers. Para capturá-las também, envolva a última linha do ``config.php``:

.. code-block:: php

   try {
       require_once(__DIR__ . '/lib/setup.php');
   } catch (\Throwable $e) {
       if (class_exists('\tool_sentry\local\prepend', false)) {
           \tool_sentry\local\prepend::capture($e);
       }
       throw $e;
   }

Para voltar ao modo plugin, remova o ``auto_prepend_file`` ou retire a ``SENTRY_DSN``.
