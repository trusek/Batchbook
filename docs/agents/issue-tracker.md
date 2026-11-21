# Issue tracker: GitHub Issues

Issues and specs live in GitHub Issues of `trusek/Batchbook`. Use the `gh` CLI for every operation.

## Conventions

- **Spec** (PRD): one issue per feature, label `spec`. Implementation tickets link to it with `Part of #<spec>` in the body.
- **Ticket**: one issue per vertical slice. Dependencies as a `Blocked by: #N, #M` line near the top of the body; a ticket is unblocked when every listed issue is closed.
- **Triage state**: a label from `triage-labels.md`.
- **Comments and conversation history**: issue comments (`gh issue comment <n> --body-file <file>`).
- **Closing**: the PR that implements a ticket says `Closes #N` in its body; merging it into `main` closes the ticket. Close by hand (`gh issue close <n> --comment "<commit or reason>"`) only for work that lands without a PR.

## When a skill says "publish to the issue tracker"

`gh issue create --title "<title>" --body-file <file> --label <label>`. Write the body to a file first; Polish text and Markdown survive that way.

## When a skill says "fetch the relevant ticket"

`gh issue view <n> --comments`. The user normally passes the issue number.

## Frontier

`gh issue list --label ready-for-agent --state open`, then drop issues whose `Blocked by:` issues are still open. Lowest number wins.

## History

The MVP was tracked as markdown files under `.scratch/mvp/` before the move to GitHub. Its spec and tickets were copied into issues; the files are in git history (commit `8dbee4d`).
