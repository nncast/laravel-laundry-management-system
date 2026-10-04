# Security Policy

## Reporting a Vulnerability

**Please do not open a public GitHub issue for security vulnerabilities.** Publicly disclosing a vulnerability before it's fixed gives attackers a head start against any shop running this system.

Instead, report it privately using one of these:

1. **GitHub Private Vulnerability Reporting (preferred)** — go to this repository's **Security** tab → **Report a vulnerability**. This opens a private conversation visible only to the maintainer, and lets you track the fix without exposing details publicly. ([GitHub's guide to reporting a vulnerability](https://docs.github.com/en/code-security/security-advisories/guidance-on-reporting-and-writing/privately-reporting-a-security-vulnerability))
2. **Email** — `[email removed]`, if you'd rather not use GitHub.

When reporting, please include:
- A description of the vulnerability and its potential impact
- Steps to reproduce it (a minimal example is ideal)
- The affected version/commit, if known
- Any suggested fix, if you have one — optional, but appreciated

## What to Expect

This is a small student project (not a funded security team), so please have reasonable patience — but every report will get a response acknowledging receipt, and a fix or mitigation plan once the issue is understood. Credit is happily given in the fix's release notes unless you'd prefer to stay anonymous.

## Scope

This covers the Laundry Management System application in this repo — sign-in and sessions, the Cashier / Manager / Admin role checks, the POS and payment logic, reports and CSV export, and database backup and restore.

Of particular interest: anything that lets a Cashier or Manager reach Admin-only pages (Reports, Staff, Master Settings, Backup & Restore), change order totals or payments outside the normal flow, download a backup without Admin access, or run arbitrary SQL through **Restore Backup**.

Out of scope: deployments still using the default `admin` / `admin123` account (the README says to change it on first login), issues that require an attacker to already have Admin access, and vulnerabilities in third-party dependencies themselves (please report those upstream — e.g. Laravel, Chart.js).

## Supported Versions

As a single-track project without parallel maintained release branches, only the **latest release** (see the [Releases page](https://github.com/nncast/laravel-laundry-management-system/releases)) receives security fixes. If you're running an older version, please update before reporting an issue that's already fixed in a later release.
