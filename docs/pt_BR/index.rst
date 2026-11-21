moodle-tool_sentry
==================

**Rastreamento de erros e APM com Sentry para o Moodle.**

Envia erros, exceções e dados de desempenho do Moodle para o `Sentry <https://sentry.io/>`_, na nuvem (sentry.io) ou self-hosted. Pode iniciar antes do Moodle, via ``auto_prepend_file``, e assim capturar até falhas de conexão com o banco de dados.

Funcionalidades
---------------

A coluna ``tool_otel`` indica se o plugin `moodle-tool_otel <https://moodle-observability.github.io/moodle-tool_otel/>`_ (tracing com OpenTelemetry, versão 0.1.0) também oferece a funcionalidade. Veja o comparativo completo em :doc:`otel-vs-sentry`.

.. list-table::
   :header-rows: 1
   :widths: 52 18 30

   * - Funcionalidade
     - tool_sentry
     - tool_otel
   * - Captura de exceções e erros PHP com o arquivo e a linha reais de origem.
     - ✅
     - ❌
   * - Captura de erros fatais (``E_ERROR``, ``E_PARSE``...) no encerramento do script.
     - ✅
     - ❌
   * - Modo prepend: inicia antes do ``config.php`` e captura erros anteriores ao banco de dados, como "Database connection failed".
     - ✅
     - ❌
   * - Tag ``moodle_component`` com o componente do Moodle onde o erro ocorreu.
     - ✅
     - ❌
   * - Contexto de tarefas agendadas e adhoc (tipo, classe, componente, id e log da tarefa).
     - ✅ Nos erros.
     - ✅ Spans de tarefas, com ``$CFG->task_log_class``.
   * - Erros de JavaScript do navegador, pelo JavaScript Loader do Sentry.
     - ✅
     - ❌
   * - Tracing de desempenho.
     - ⚠️ Parcial: ativa o tracing do SDK (``enable_tracing``, ``traces_sample_rate``), sem spans do Moodle.
     - ⚠️ Parcial: spans de requisições, web services e tarefas, agregados do banco por requisição; sem spans de ``curl``.
   * - Profiling (``profiles_sample_rate``).
     - ⚠️ Depende do tracing do SDK.
     - ❌
   * - Envio para o Sentry (sentry.io ou self-hosted).
     - ✅
     - ⚠️ Só traces, via OTLP.
   * - Envio para outros backends (Jaeger, Grafana Tempo e outros compatíveis com OTLP).
     - ❌
     - ✅
   * - Proteção de dados: valores de parâmetros sensíveis (``token``, ``password``, ``sesskey``...) trocados por ``[Filtered]`` e nenhum dado pessoal por padrão.
     - ⚠️ Só no modo prepend.
     - ✅ Spans sem dados pessoais e sem query strings; hook ``redact_span``.
   * - Id do evento capturado no header ``X-Sentry-Event-Id`` da resposta, para a página de erro do servidor web.
     - ⚠️ Só no modo prepend.
     - ❌
   * - Botão de teste de conexão (*Send Exception*) na página de configuração.
     - ✅
     - ❌

Legenda: ✅ disponível; ⚠️ parcial ou só na condição indicada; ❌ não oferecido.

Requisitos
----------

.. list-table::
   :header-rows: 1

   * - Versão do plugin
     - Moodle
     - PHP
   * - 5.3.x (branch ``TOOL_SENTRY_5_3``)
     - 5.3
     - 8.3+
   * - 1.0.x (branch ``TOOL_SENTRY_1_0``)
     - 4.1 e 4.5
     - 8.1+

Você também precisa de um projeto no Sentry e do DSN dele.

Links
-----

* `Código-fonte <https://github.com/moodle-observability/moodle-tool_sentry>`_
* `Issues <https://github.com/moodle-observability/moodle-tool_sentry/issues>`_
* `Diretório de Plugins Moodle <https://moodle.org/plugins/tool_sentry>`_
* `Moodle Observability <https://moodle-observability.github.io/>`_

.. toctree::
   :hidden:
   :maxdepth: 1

   installation
   configuration
   usage
   otel-vs-sentry
