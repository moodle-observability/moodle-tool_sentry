OpenTelemetry and Sentry
========================

OpenTelemetry and Sentry are not equivalent. That is why ``tool_otel`` works alongside ``tool_sentry`` instead of replacing it.

* **OpenTelemetry (OTel)** is an open standard with SDKs and a Collector that produce and transport telemetry. It **does not store or display anything**: it needs a backend.
* **Sentry** is a complete product, with a backend, a user interface, alerts and an issue workflow.

What OpenTelemetry does and Sentry does not (or does poorly)
------------------------------------------------------------

* **Vendor neutrality**: instrument once and export to any backend (Grafana Tempo, Loki and Mimir, Jaeger, Prometheus, Datadog, Honeycomb and Sentry itself).
* **Collector**: an intermediate pipeline that does tail-based sampling, filters, removes personal data, enriches and sends to several destinations at the same time.
* **Infrastructure metrics**: host, Kubernetes, containers, databases and queues, plus Prometheus scraping through receivers. Sentry does not cover this area.
* **Complete application metrics**: counters, histograms and gauges with Prometheus-style aggregation. In Sentry, metrics are limited and their status changed several times.
* **Generic logs** from any source (file, syslog, journald), correlated with the trace.
* **Semantic conventions** standardised across languages and vendors.
* **No vendor lock-in and no licence cost**, but you operate the backend yourself.

What Sentry does and OpenTelemetry does not
-------------------------------------------

* **Error grouping into issues** by fingerprint, with assignment, resolution, ignoring, regression detection and a count of affected users.
* **Source maps and debug symbols**: rebuilds stack traces from minified JavaScript and from iOS and Android apps (ProGuard, dSYM).
* **Native crashes and ANR** in mobile and desktop apps.
* **Session Replay** of the frontend.
* **Releases**: health per version, suspect commits and repository integration.
* **Breadcrumbs and local variables** in the stack trace.
* **User Feedback**, **Cron Monitors** and **Uptime**.
* **Ready-made alerts and user interface**, with no stack to build.
* **Mature profiling**. The OpenTelemetry profiling signal is still in development.

Where the two overlap
---------------------

* **Distributed traces and performance**: both do it.
* The Sentry SDKs (JavaScript from v8, Python) already use OpenTelemetry internally, and Sentry accepts OTLP ingestion. That is why ``tool_otel`` can export traces to Sentry.

Recommended combination for Moodle
----------------------------------

* ``tool_otel`` and the OpenTelemetry Collector send traces, metrics and logs to the Grafana stack (or another OTLP backend).
* ``tool_sentry`` handles errors, exceptions, releases and, on the frontend, replay.
* The ``trace_id`` links the two tools: from an error in Sentry, find the complete trace in the tracing backend.

.. note::

   Sentry's OTLP support and the status of its metrics change often. Check the `Sentry documentation <https://docs.sentry.io/>`_ before you decide on the architecture.
