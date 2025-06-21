# Files

**File Managment in PHP**

- [Files](#files)
  - [Installation](#installation)
  - [Usage](#usage)
    - [Creating Files Objects](#creating-files-objects)
    - [Available File Types](#available-file-types)
    - [File Properties](#file-properties)
      - [RegularFile Properties](#regularfile-properties)
      - [Link Properties](#link-properties)
    - [File Methods](#file-methods)
      - [RegularFile Methods](#regularfile-methods)
    - [Directory Methods](#directory-methods)
    - [Link Methods](#link-methods)

***

## Installation

Install *files* via Composer:

```bash
composer require ali-eltaweel/files
```

## Usage

### Creating Files Objects

Instances of the `File` abstract class can be created using the `File::make()` which looks for the file type and returns the appropriate class instance, or by directly instantiating a concrete `File` class.

```php
use Files\{ Directory, File, RegularFile };

$file = File::make('path/to/file');
$file = new RegularFile('path/to/file');

$dir = File::make('path/to/dir');
$dir = new Directory('path/to/dir');
```

### Available File Types

- Fifo
- CharacterDevice
- Directory
- RegularFile
- Symlink


### File Properties

|    Property |               Type                |  Get  |  Set  |
| ----------: | :-------------------------------: | :---: | :---: |
|        path |              string               |   ✅   |   ❌   |
|         uid |         string \| integer         |   ✅   |   ✅   |
|         gid |         string \| integer         |   ✅   |   ✅   |
| permissions |              integer              |   ✅   |   ✅   |
|        mode |              integer              |   ✅   |   ✅   |
|       atime |              integer              |   ✅   |   ✅   |
|       ctime |              integer              |   ✅   |   ❌   |
|       mtime |              integer              |   ✅   |   ✅   |
|       inode |              integer              |   ✅   |   ❌   |
|        size |              integer              |   ✅   |   ❌   |
|        type | [FileType](#available-file-types) |   ✅   |   ❌   |
|        stat |               Stat                |   ✅   |   ❌   |

#### RegularFile Properties

| Property |  Type  |  Get  |  Set  |
| -------: | :----: | :---: | :---: |
|  content | string |   ✅   |   ✅   |

#### Link Properties

|    Property |       Type        |  Get  |  Set  |
| ----------: | :---------------: | :---: | :---: |
|   targetGid | string \| integer |   ✅   |   ✅   |
|   targetUid | string \| integer |   ✅   |   ✅   |
|      target |       ?File       |   ✅   |   ❌   |
| finalTarget |       ?File       |   ✅   |   ❌   |

### File Methods

- copy
```php
copy(Path|string $target): bool
```
- link
```php
link(Path|string $target): bool
```
- symlink
```php
symlink(Path|string $target): bool
```
- rename
```php
rename(Path|string $target): bool
```
- touch
```php
touch(?int $mtime = null, ?int $atime = null): bool
```
- remove
```php
remove(): bool
```
- open
```php
open(): Handles\Handle
```

#### RegularFile Methods

- transaction
```php
transaction(callable $work, string $mode = 'r', Lock $lock = Lock::Exclusive): mixed
```
- setContent
```php
setContent(string $content): int
```
- getContent
```php
getContent(): ?string
```

### Directory Methods

- mkdir
```php
mkdir(Path|string $name, int $permissions = 0777, bool $recursive = false): ?Directory
```
- remove
```php
remove(bool $force = false): bool
```
- foreachChild
```php
foreachChild(callable $callback): void
```

### Link Methods

- chgrpTarget
```php
chgrpTarget(string|int $group): bool
```
- chownTarget
```php
chownTarget(string|int $user): bool
```
- readlink
```php
readlink(): ?File
```
- readlinkRecursively
```php
readlinkRecursively(): ?File
```
