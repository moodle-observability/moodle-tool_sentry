OpenTelemetry e Sentry
======================

OpenTelemetry e Sentry não são equivalentes, e por isso o ``tool_otel`` convive com o ``tool_sentry`` em vez de substituí-lo.

* **OpenTelemetry (OTel)** é um padrão aberto com SDKs e um Collector para gerar e transportar telemetria. Ele **não armazena nem exibe nada**: precisa de um backend.
* **Sentry** é um produto completo, com backend, interface, alertas e fluxo de tratamento de issues.

O que o OpenTelemetry faz e o Sentry não faz (ou faz mal)
----------------------------------------------------------

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

O Sentry aceita OTLP: então ele substitui o OpenTelemetry?
----------------------------------------------------------

Não. Como produto pronto para erros e APM, o Sentry é bem mais completo. Como cobertura de observabilidade, não, e aceitar OTLP não faz o Sentry conter o OpenTelemetry.

* **O Sentry é um destino possível**: ele recebe os traces (e logs) que o OpenTelemetry gera, mas não faz o papel do OpenTelemetry. Não há Collector, métricas de infraestrutura nem envio para vários backends, e a telemetria fica presa ao Sentry.
* **Dados que chegam via OTLP rendem menos**: não trazem breadcrumbs, variáveis locais, source maps nem o agrupamento rico de issues. Isso vem dos SDKs do próprio Sentry.
* **Os plugins se completam**: o ``tool_sentry`` cuida dos erros, com detalhes que o OpenTelemetry não tem; o ``tool_otel`` cuida dos spans de desempenho do Moodle (requisições, tarefas, web services e agregados do banco), que o ``tool_sentry`` não cria. Os traces do ``tool_otel`` podem ir para o próprio Sentry via OTLP.

Resumo: o Sentry é mais completo como **destino**; o OpenTelemetry é o que **gera e transporta** a telemetria. Um não substitui o outro.

Combinação recomendada no Moodle
--------------------------------

* ``tool_otel`` e o OpenTelemetry Collector levam traces, métricas e logs para a stack Grafana (ou outro backend OTLP).
* ``tool_sentry`` fica com erros, exceções, releases e, no frontend, replay.
* O ``trace_id`` liga as duas ferramentas: a partir de um erro no Sentry, encontre o trace completo no backend de tracing.

.. note::

   O suporte a OTLP no Sentry e o status das métricas dele mudam com frequência. Confira a `documentação do Sentry <https://docs.sentry.io/>`_ antes de decidir a arquitetura.
