import moodle_docs_theme

project = "moodle-tool_sentry"
copyright = "2026, Moodle Observability contributors"
author = "Kelson da Costa Medeiros"
release = "5.3.0"

extensions = [
    "sphinx.ext.githubpages",
    "moodle_docs_theme",
]

templates_path = []
exclude_patterns = ["_build", "Thumbs.db", ".DS_Store"]

language = "en"

html_theme = "moodle_docs_theme"
html_theme_path = [moodle_docs_theme.get_html_theme_path()]

html_theme_options = {
    "primary_color": "#ea580c",
    "secondary_color": "#0f172a",
    "project_name": "moodle-tool_sentry",
    "tagline": "Sentry error tracking and APM for Moodle",
    "github_url": "https://github.com/moodle-observability/moodle-tool_sentry",
    "github_repo": "moodle-observability/moodle-tool_sentry",
    "github_version": "main",
    "doc_path": "docs/en/",
    "show_edit_on_github": True,
    "enable_dark_mode": True,
    "navigation_links": "Home|index, Installation|installation, Configuration|configuration, Usage|usage, Sentry vs OTel|otel-vs-sentry",
}

html_static_path = []
