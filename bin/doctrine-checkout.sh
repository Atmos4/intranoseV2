#!/bin/bash

# Commit ref of current HEAD
start_commit="$(git rev-parse HEAD)"
echo "Your start commit is $start_commit"

# Commit ref of what is to be checked out
dest_commit="$1"
echo "Your destination commit is $dest_commit"

# First common ancestor commit between the two branches
ancestor="$(git merge-base HEAD "$dest_commit")"
echo "Your common ancestor commit is $ancestor"

# Shorthand for `C:/wamp64/www/intranoseV2/bin/doctrine`
appconsole="$(git rev-parse --show-toplevel)/bin/doctrine"

# Checkout the ancestor commit to find the first common migration between the
# two branches.  Migrate backwards to this version.
git config advice.detachedHead false
git checkout "$ancestor"
ancestor_migration="$($appconsole migrations:latest)"
# Remove the description as well as all the white spaces and any newline
ancestor_migration=$(echo "$ancestor_migration" | sed 's/-.*//g' | tr -d ' ' | tr -d '\n')
echo "Your common ancestor migration is $ancestor_migration"
git checkout "$start_commit"
$appconsole migrations:migrate "$ancestor_migration" --no-interaction

# Checkout the destination branch and migrate back up

git checkout "$dest_commit"
$appconsole migrations:migrate --no-interaction