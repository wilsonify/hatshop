---
title: "Where things live"
weight: 20
---

# Where things live

A map of responsibilities, not of files. Each row answers "who owns this answer?"

| Question | Owner |
| --- | --- |
| What must the system do? | `openspec/specs/<capability>/spec.md` |
| What is being changed, and why? | `openspec/changes/<name>/` until it is archived |
| Why is it built this way? | `src/web/docs/architecture.md` |
| What was observed against a real deployment? | `src/web/docs/live-verification.md`, `src/web/docs/hyperdrive-verification.md` |
| How do I run, test or deploy it? | `src/web/README.md`, `package.json`, the `makefile`s |
| How is the Worker hosted? | the Terraform module in the FreshInstall repository, `cloudflare/hatshop` |
| What is in the database? | the database itself; a snapshot and generator live in `src/web/scripts` and `src/web/docs/schema-snapshot.json` |
| What did the scan find? | SonarQube; configuration is in `sonar-project.properties` |

## Rules of the map

- A document is not allowed to answer a question owned by another row. No
  inventory of routes, no table of functions, no copy of a command.
- Generated artifacts carry a header saying so and are rebuilt rather than edited.
- If two documents disagree, the spec wins for behaviour, the code wins for what
  exists, and the disagreement is a bug to fix in the document that is wrong.
