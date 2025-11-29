# ✅ Basic Usage Examples

## Description

The Database class wrap, facilitate and secure interation with the db.
All of it's method are definied as static.

## Exemple usage

### 1. **Insert a row**

```php
use DB\Database;

$id = Database::insert("students", [
    "name" => "Alice",
    "grade" => "A"
]);

echo "Inserted rows: $id";
```

---

### 2. **Update rows**

```php
$updated = Database::update(
    "students",
    ["grade" => "A+"],
    "id = ?",
    [1]
);

echo "Updated rows: $updated";
```

---

### 3. **Delete rows**

```php
$deleted = Database::delete(
    "students",
    "id = ?",
    [1]
);

echo "Deleted rows: $deleted";
```

---

### 4. **Fetch one row**

```php
$student = Database::fetch(
    "SELECT * FROM students WHERE id = ?",
    [2]
);

print_r($student);
```

---

### 5. **Fetch all rows**

```php
$allStudents = Database::fetchAll("SELECT * FROM students");
print_r($allStudents);
```

---

### 6. **Run a raw query (insert/update/delete)**

```php
$rows = Database::execQuery(
    "UPDATE students SET grade = ? WHERE id = ?",
    ["B", 3]
);

echo "Affected rows: $rows";
```

---

### 7. **Transactions (nested safe)**

```php
try {
    $result = Database::useTransaction(function($DB, $pdo) {

        // Insert a vote
        $DB::insert("votes", [
            "candidate_id" => 12,
            "token_id"     => 45
        ]);

        // Nested transaction
        $DB::useTransaction(function($DB2, $pdo2) {
            $DB2::update(
                "tokens",
                ["is_used" => 1],
                "id = ?",
                [45]
            );
        });

        return "Vote registered successfully!";
    });

    echo $result;

} catch (Exception $e) {
    echo "Transaction failed: " . $e->getMessage();
}
```

---

### 8. **Key Notes**

* All methods automatically **use the singleton PDO instance**.
* `useTransaction` supports **nested transactions** via **named savepoints**.
* If any exception is thrown inside a transaction, **rollback happens automatically**.
* Always pass variables into closures using `use($var1, $var2)`.
