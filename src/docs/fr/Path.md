Vote/src/docs/Path_fr.md
```

```md
# Classe utilitaire Path

La classe `Path` fournit des méthodes utilitaires pour résoudre les chemins de fichiers dans le projet. Elle garantit une résolution cohérente et sécurisée des chemins pour les répertoires privés et publics.

## Méthodes

### 1. `resolvePrivatePath(string $path): string`

Résout un chemin relatif donné vers le répertoire privé.

#### **Paramètres** :
- `string $path` : Le chemin relatif à résoudre.

#### **Retourne** :
- `string` : Le chemin absolu vers le fichier ou répertoire spécifié dans le répertoire privé.

#### **Exemple d'utilisation** :
```php
use Utils\Path;

$cheminFichierPrive = Path::resolvePrivatePath("config/settings.json");
echo $cheminFichierPrive;
// Sortie : /chemin/absolu/vers/prive/config/settings.json
```

---

### 2. `resolvePublicPath(string $path): string`

Résout un chemin relatif donné vers le répertoire public.

#### **Paramètres** :
- `string $path` : Le chemin relatif à résoudre.

#### **Retourne** :
- `string` : Le chemin absolu vers le fichier ou répertoire spécifié dans le répertoire public.

#### **Exemple d'utilisation** :
```php
use Utils\Path;

$cheminFichierPublic = Path::resolvePublicPath("assets/images/logo.png");
echo $cheminFichierPublic;
// Sortie : /chemin/absolu/vers/public/assets/images/logo.png
```

---

## Notes clés

- Les deux méthodes utilisent `__DIR__` pour déterminer le répertoire actuel du fichier `Path.php` et y ajoutent le chemin relatif en conséquence.
- Assurez-vous que le paramètre `$path` ne contient pas d'entrée malveillante pour éviter les vulnérabilités de traversée de répertoires.

Cette classe utilitaire simplifie la gestion des chemins et garantit la cohérence dans tout le projet.