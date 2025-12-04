<?php

class PdoGsb {
    private static $host = 'gsb';
    private static $port = 3306;
    private static $dbname = 'gsb_frais';
    private static $user = 'root';
    private static $pass = '';

    /** @var PDO|null */
    private static $pdo = null;

    private function __construct() { /* no-op */ }

    public static function getPdo(): PDO {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            self::$host,
            self::$port,
            self::$dbname
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            self::$pdo = new PDO($dsn, self::$user, self::$pass, $options);
        } catch (PDOException $e) {
            // Re-throw with a clearer message for CLI output
            throw new PDOException("DB connection failed: " . $e->getMessage(), (int)$e->getCode());
        }

        return self::$pdo;
    }
}


function isAlreadyHashed(string $s): bool {
    return (bool) preg_match('/^\$(2y|2a|2b)\$|^\$argon2/i', $s);
}

try {
    $pdo = PdoGsb::getPdo();
} catch (PDOException $e) {
    echo "Erreur de connexion BD: " . $e->getMessage() . PHP_EOL;
    echo "Vérifiez que MySQL tourne, que la base existe et que les paramètres (host/port/dbname/user/mdp) sont corrects." . PHP_EOL;
    exit(2);
}

echo "Récupération des mots de passe...\n";

$rows = $pdo->query("SELECT id, mdp FROM visiteur")->fetchAll();

$total   = count($rows);
$updates = 0;
$erreurs = 0;

if ($total === 0) {
    echo "Aucun enregistrement trouvé.\n";
    exit(0);
}

$updateStmt = $pdo->prepare("UPDATE visiteur SET mdp = :hash WHERE id = :id");

try {
    $pdo->beginTransaction();

    foreach ($rows as $r) {
        $id  = $r['id'];
        $mdp = $r['mdp'];

        if ($mdp === null || $mdp === '') {
            echo "[AVERTISSEMENT] id=$id mot de passe vide -> ignoré.\n";
            continue;
        }

        if (isAlreadyHashed($mdp)) {
            echo "[INFO] id=$id mot de passe semble déjà haché -> ignoré.\n";
            continue;
        }

        $hash = password_hash($mdp, PASSWORD_DEFAULT);
        if ($hash === false) {
            $erreurs++;
            echo "[ERREUR] id=$id échec password_hash().\n";
            continue;
        }

        try {
            $ok = $updateStmt->execute([
                ':hash' => $hash,
                ':id'   => $id
            ]);
            if ($ok) {
                $updates++;
            } else {
                $erreurs++;
                echo "[ERREUR] id=$id échec UPDATE (execute returned false).\n";
            }
        } catch (PDOException $e) {
            $erreurs++;
            echo "[ERREUR] id=$id " . $e->getMessage() . "\n";
        }
    }

    if ($erreurs === 0) {
        $pdo->commit();
        echo "Transaction validée.\n";
    } else {
        $pdo->rollBack();
        echo "Transaction annulée (rollback) à cause de $erreurs erreur(s). Aucune modification enregistrée.\n";
        exit(1);
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Erreur lors du traitement : " . $e->getMessage() . PHP_EOL;
    exit(3);
}

echo "-----------------------------\n";
echo "Total lignes lues     : $total\n";
echo "Hashés maintenant      : $updates\n";
echo "Erreurs                : $erreurs\n";
echo "-----------------------------\n";
echo "Terminé.\n";