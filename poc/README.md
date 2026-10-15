# Proof of concept

This PoC validates the unrestricted PHP object-deserialization primitive and the three affected sinks in Pimcore revision `3f71d75fef8736d2f398843c1ccf8bc5722b736a`.

It is intentionally non-destructive. The probe uses a local demonstration class whose `__wakeup()` method creates a marker in a temporary directory. It does not contain a production gadget chain, command execution payload or remote delivery mechanism.

## Part 1: verify the affected sinks

```bash
git clone https://github.com/pimcore/pimcore.git
git -C pimcore checkout 3f71d75fef8736d2f398843c1ccf8bc5722b736a
bash ./verify-sinks.sh ./pimcore
```

The checker confirms these exact calls:

- `models/DataObject/ClassDefinition/Data/Block.php`: `Serialize::unserialize($data, true)`
- `models/Document/Editable.php`: `Serialize::unserialize($data, true)`
- `models/Asset/WebDAV/Service.php`: `Serialize::unserialize($payload, true)`

It also checks that commit `879c0a1` is present in the audited history. That change deliberately restored unrestricted object reconstruction after the earlier hardening caused compatibility problems.

## Part 2: demonstrate the primitive safely

From the root of a Composer-installed affected Pimcore checkout:

```bash
php /path/to/unserialize-probe.php .
```

Expected observations:

1. With `allowed_classes=true`, PHP instantiates `CapivaraResearchUnserializeProbe` and invokes its `__wakeup()` method, creating a marker under the system temporary directory.
2. With `allowed_classes=false`, the value becomes `__PHP_Incomplete_Class` and the probe's magic method does not execute.

This directly demonstrates the security significance of the boolean argument restored at all three sinks.

## Data flow and trigger locations

The vulnerable flow is:

```text
crafted serialized bytes
        |
        +-- DataObject Block resource column -> Block::getDataFromResource()
        +-- documents_elements.data          -> Editable::getUnserializedData()
        +-- webdav-delete.dat payload         -> WebDAV Service::restoreDeletedAsset()
                                                  |
                                                  v
                               Serialize::unserialize(..., true)
                                                  |
                                                  v
                            unrestricted object reconstruction / magic methods
```

The deserialized return value is shape-checked only after object reconstruction. Such a check cannot prevent `__wakeup()` from running because PHP invokes it during `unserialize()`.

## Exploitability boundary

The PoC proves the dangerous primitive and confirms that the three production sinks enable unrestricted classes. A complete remote attack additionally requires a route, privilege or separate vulnerability that allows crafted bytes to be written into the relevant database column or WebDAV deletion log. This repository does not claim that an unauthenticated remote write primitive has been demonstrated for every sink.

If a suitable write primitive exists, classes from installed dependencies may provide gadget chains. The related earlier advisory documented this class of impact as RCE-capable. VulDB and vendor testing should use a disposable local installation and a benign marker gadget first.

## Expected secure behavior

Untrusted or modified persistence values must not instantiate arbitrary PHP classes. A secure implementation should use a non-executable data representation, verify a server-side integrity signature before deserialization, or enforce a narrowly defined class allowlist.
