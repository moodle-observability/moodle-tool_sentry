OpenTelemetry e Sentry
======================

OpenTelemetry e Sentry não são equivalentes, e por isso o ``tool_otel`` convive com o ``tool_sentry`` em vez de substituí-lo.

* **OpenTelemetry (OTel)** é um padrão aberto com SDKs e um Collector para gerar e transportar telemetria. Ele **não armazena nem exibe nada**: precisa de um backend.
* **Sentry** é um produto completo, com backend, interface, alertas e fluxo de tratamento de issues.

O que o OpenTelemetry faz e o Sentry não faz (ou faz mal)
---------------------------------------------------------

* **Neutralidade de fornecedor**: instrumenta uma vez e exporta para qualquer backend (Grafana Tempo, Loki e Mimir, Jaeger, Prometheus, Datadog, Honeycomb e o próprio Sentry).
* **Collector**: pipeline intermediário que faz *tail-based sampling*, filtra, remove dados pessoais, enriquece e envia para vários destinos ao mesmo tempo.
* **Métricas de infraestrutura**: host, Kubernetes, containers, bancos de dados e filas, além de *scraping* de Prometheus via *receivers*. O Sentry não cobre essa área.
* **Métricas de aplicação completas**: *counters*, *histograms* e *gauges* com agregação no padrão Prometheus. No Sentry, as métricas são limitadas e mudaram de status várias vezes.
* **Logs genéricos** de qualquer fonte (arquivo, syslog, journald), correlacionados com o trace.
* **Convenções semânticas** padronizadas entre linguagens e fornecedores.
* **Sem dependência de fornecedor e sem custo de licença**, mas a operação do backend fica por sua conta.

O que o Sentry faz e o OpenTelemetry não faz
--------------------------------------------

* **Agrupamento de erros em issues** por *fingerprint*, com atribuição, resolução, descarte, detecção de regressão e contagem de usuários afetados.
* **Source maps e símbolos de depuração**: reconstrói stack traces de JavaScript minificado e de apps iOS e Android (ProGuard, dSYM).
* **Crashes nativos e ANR** em aplicativos móveis e desktop.
* **Session Replay** do frontend.
* **Releases**: saúde por versão, *suspect commits* e integração com o repositório.
* **Breadcrumbs e variáveis locais** no stack trace.
* **User Feedback**, **Cron Monitors** e **Uptime**.
* **Alertas e interface prontos**, sem montar nenhuma stack.
* **Profiling maduro**. O sinal de profiling do OpenTelemetry ainda está em desenvolvimento.

Onde os dois se sobrepõem
-------------------------

* **Traces distribuídos e desempenho**: os dois fazem.
* Os SDKs do Sentry (JavaScript a partir da v8, Python) já usam OpenTelemetry por baixo, e o Sentry aceita ingestão OTLP. Por isso o ``tool_otel`` pode exportar traces para o Sentry.

Combinação recomendada no Moodle
--------------------------------

* ``tool_otel`` e o OpenTelemetry Collector levam traces, métricas e logs para a stack Grafana (ou outro backend OTLP).
* ``tool_sentry`` fica com erros, exceções, releases e, no frontend, replay.
* O ``trace_id`` liga as duas ferramentas: a partir de um erro no Sentry, encontre o trace completo no backend de tracing.

.. note::

   O suporte a OTLP no Sentry e o status das métricas dele mudam com frequência. Confira a `documentação do Sentry <https://docs.sentry.io/>`_ antes de decidir a arquitetura.
