# Duplication

This small script creates backups of files existing in a specific location by copying them to to one or more other locations. Since this is meant to duplicate backups, changes to files are not checked for. All remote file reads are cached locally to minimize traffic while the script runs.

## Installation

Clone this repository to a machine you own. Then use `composer install` to install the dependencies.

## Configuration

Please copy the supplied config.example.yml to config.yml and adjust the values as required. We are using Contabo, this may or may not work with other storage providers.

### Type SSH

This will use sftp to connect to a server. This can be used for upload and download.

#### Read & Write

```yml
type: ssh
bucket-path: /backups
user: rotten
ssh-path: /backups
host: test-01.idrinth.de
password: abcdefghijklmnopqrstuvwxyz
private-key: /private/key.pem
port: 22
```

### Type Local

This will backup from or on the local machine.

#### Read

```yml
type: local
path: /backups
prefix: mine
```

#### Write

```yml
type: local
path: /backups
user: backup
group: backup
```

### Type Bucket

This uses an S3 bucket to read from or write to.

#### Read & Write

```yml
type: bucket
endpoint: buckets.idrinth.de/test-01
bucket: test-01
access-key: abcdefghijklmnopqrstuvwxyz
secret-access-key: 1234567890
```

## Support

If you need support, got an improvement idea or want to chat about the project, feel free to open an issue or join the [discord](https://discord.gg/xHSF8CGPTh).
