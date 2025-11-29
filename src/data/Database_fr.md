# ✅ Exemples d'utilisation de base

## Description

La classe Database encapsule, facilite et sécurise l'interaction avec la base de données.
Toutes ses méthodes sont définies comme statiques.

## Exemple d'utilisation

### 1. **Insérer une ligne**

```php
use DB\Database;

$id = Database::insert("students", [
    "name" => "Alice",
    "grade" => "A"
]);

echo "Lignes insérées : $id";
```

---

### 2. **Mettre à jour des lignes**

```php
$updated = Database::update(
    "students",
    ["grade" => "A+"],
    "id = ?",
    [1]
);

echo "Lignes mises à jour : $updated";
```

---

### 3. **Supprimer des lignes**

```php
$deleted = Database::delete(
    "students",
    "id = ?",
    [1]
);

echo "Lignes supprimées : $deleted";
```

---

### 4. **Récupérer une ligne**

```php
$student = Database::fetch(
    "SELECT * FROM students WHERE id = ?",
    [2]
);

print_r($student);
```

---

### 5. **Récupérer toutes les lignes**

```php
$allStudents = Database::fetchAll("SELECT * FROM students");
print_r($allStudents);
```

---

### 6. **Exécuter une requête brute (insérer/mettre à jour/supprimer)**

```php
$rows = Database::execQuery(
    "UPDATE students SET grade = ? WHERE id = ?",
    ["B", 3]
);

echo "Lignes affectées : $rows";
```

---

### 7. **Transactions (sécurisées et imbriquées)**

```php
try {
    $result = Database::useTransaction(function($DB, $pdo) {

        // Insérer un vote
        $DB::insert("votes", [
            "candidate_id" => 12,
            "token_id"     => 45
        ]);

        // Transaction imbriquée
        $DB::useTransaction(function($DB2, $pdo2) {
            $DB2::update(
                "tokens",
                ["is_used" => 1],
                "id = ?",
                [45]
            );
        });

        return "Vote enregistré avec succès !";
    });

    echo $result;

} catch (Exception $e) {
    echo "Échec de la transaction : " . $e->getMessage();
}
```

---

### 8. **Notes clés**

* Toutes les méthodes utilisent automatiquement **l'instance PDO singleton**.
* `useTransaction` prend en charge les **transactions imbriquées** via des **points de sauvegarde nommés**.
* Si une exception est levée dans une transaction, **un rollback est effectué automatiquement**.
* Passez toujours les variables dans les closures en utilisant `use($var1, $var2)`.
