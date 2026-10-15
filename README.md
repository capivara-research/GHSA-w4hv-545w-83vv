# GHSA-w4hv-545w-83vv

## Pimcore incomplete PHP Object Injection fix in Block, Document Editable and WebDAV delete-log sinks

Public research reference for the security report privately submitted to the Pimcore maintainers through GitHub Security Advisories.

- Advisory: [GHSA-w4hv-545w-83vv](https://github.com/pimcore/pimcore/security/advisories/GHSA-w4hv-545w-83vv)
- Related original advisory: [GHSA-w23p-wrp7-ch38](https://github.com/pimcore/pimcore/security/advisories/GHSA-w23p-wrp7-ch38)
- Affected package: `pimcore/pimcore`
- Affected versions: `>= 12.3.13, <= 2026.2.6`
- Weakness: CWE-502 — Deserialization of Untrusted Data
- Severity: High
- CVE status: No CVE assigned to this report
- Researcher credit: **Fernando Viana - Capivara Research**

## Submission evidence

The screenshot below records the private GitHub Security Advisory, affected versions, severity, weakness classification and reporter attribution.

![GHSA-w4hv-545w-83vv submission evidence](./pimcore.png)

## Disclosure note

This repository documents the existence, scope and attribution of the report. The report concerns an incomplete security fix and additional PHP object-deserialization sinks in Block, Document Editable and WebDAV delete-log handling. Detailed coordination and remediation remain with the vendor through the advisory linked above.

## Reproduction material

The [`poc/`](./poc/) directory contains a source verifier for all three affected sinks and a controlled PHP probe demonstrating the difference between unrestricted and disabled class reconstruction. The documented exploitability boundary distinguishes the proven deserialization primitive from the additional write primitive required for a remote attack.
