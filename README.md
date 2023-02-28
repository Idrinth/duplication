# S3 Duplication

This small script creates backups of files existing in a specific bucket by copiing them over to one or more other buckets. Since this is meant to duplicate backups, changes to files are not checked for.

## Configuration

Please copy the supplied config.example.yml to config.yml and adjust the values as required. We are using Contabo, this may or may not work with other storage providers.