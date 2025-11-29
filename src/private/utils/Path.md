# Path Utility Class

The `Path` class provides utility methods to resolve file paths within the project. It ensures consistent and secure path resolution for both private and public directories.

## Methods

### 1. `resolvePrivatePath(string $path): string`

Resolves a given relative path to the private directory.

#### **Parameters**:
- `string $path`: The relative path to resolve.

#### **Returns**:
- `string`: The absolute path to the specified file or directory within the private directory.

#### **Example Usage**:
```php
use Utils\Path;

$privateFilePath = Path::resolvePrivatePath("config/settings.json");
echo $privateFilePath;
// Output: /absolute/path/to/private/config/settings.json
```

---

### 2. `resolvePublicPath(string $path): string`

Resolves a given relative path to the public directory.

#### **Parameters**:
- `string $path`: The relative path to resolve.

#### **Returns**:
- `string`: The absolute path to the specified file or directory within the public directory.

#### **Example Usage**:
```php
use Utils\Path;

$publicFilePath = Path::resolvePublicPath("assets/images/logo.png");
echo $publicFilePath;
// Output: /absolute/path/to/public/assets/images/logo.png
```

---

## Key Notes

- Both methods use `__DIR__` to determine the current directory of the `Path.php` file and append the relative path accordingly.
- Ensure that the `$path` parameter does not contain malicious input to avoid directory traversal vulnerabilities.

This utility class simplifies path management and ensures consistency across the project.
