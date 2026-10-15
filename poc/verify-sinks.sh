#!/usr/bin/env bash
set -euo pipefail

repo="${1:-.}"
block="$repo/models/DataObject/ClassDefinition/Data/Block.php"
editable="$repo/models/Document/Editable.php"
webdav="$repo/models/Asset/WebDAV/Service.php"

for source in "$block" "$editable" "$webdav"; do
  [[ -f "$source" ]] || { printf 'Missing source file: %s\n' "$source" >&2; exit 1; }
done

check() {
  local file=$1
  local needle=$2
  if grep -Fq "$needle" "$file"; then
    printf '[VULNERABLE SINK] %s :: %s\n' "${file#"$repo"/}" "$needle"
  else
    printf '[NOT FOUND] %s :: %s\n' "${file#"$repo"/}" "$needle" >&2
    return 1
  fi
}

check "$block" 'Serialize::unserialize($data, true)'
check "$editable" 'Serialize::unserialize($data, true)'
check "$webdav" 'Serialize::unserialize($payload, true)'

if git -C "$repo" merge-base --is-ancestor 879c0a1 HEAD 2>/dev/null; then
  printf '[HISTORY] restore commit 879c0a1 is present in the checked-out revision.\n'
else
  printf '[WARNING] could not confirm restore commit 879c0a1 in this checkout.\n' >&2
fi

printf '\nThree unrestricted object-deserialization sinks confirmed.\n'
