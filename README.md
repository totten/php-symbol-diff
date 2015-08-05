# php-symbol-diff

This command compares two PHP source files and identifies the names of any
symbols (classes, functions, methods, properties) which were added, removed,
or modified.

```
# Compare two files
php-symbol-diff old.php new.php

# Compare a file with an older revision in git
git show abcd1234:/some/file.php | php-symbol-diff /dev/stdin some/file.php

# Compare all the files in different git commits
git-php-symbol-diff v1.0 v1.1
```
