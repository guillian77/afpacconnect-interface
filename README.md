# AfpaConnect Helper Interface

Ce module permet d'intégrer facilement une classe permettant le dialogue avec l'API AfpaConnect.

Packagist: [guillian/afpaconnect-interface](https://packagist.org/packages/guillian/afpaconnect-interface).
## Installation
### Prérequis
- php >= 7.4
- composer >= 2.0
- PHP7.4-http
- PHP7.4-curl
- PHP7.4-json
- PHP7.4-dom
- PHP7.4-xml
- PHP7.4-mbstring
- PHP7.4-pdo
- (PHP7.4-libxml)

### Procédure
```SH
composer require guillian/afpaconnect-interface
```

## Projets AFPA
Ce petit guide va vous expliquer comment intégrer 
1. Dans un invite de commande, se placer à la racine de votre projet.
2. Installer le module avec composer `composer require guillian/afpaconnect-interface`.
3. Utiliser l'**autoloader** de composer:

    Pour cela, dans le fichier `DEVS/web/route.php` placer après le `session_start()`.
    ```PHP
    require $GLOBALS_INI['PATH_HOME'].$GLOBALS_INI['PATH_CLASS'].'vendor/autoload.php';
    ```
   *Cette autoloader permet de charger dynamiquement les classes se trouvant dans le dossier **vendor**.*

4. Retoucher le fichier `DEVS/modules/initialize.php` afin d'inclure globalement l'API Helper dans tous les services.
    1. Avant le début de la classe, ajouter le **use**:
    ```PHP
   <?php
   require_once "database.php";
   require_once "security.php";
   
   use Guillian\AfpaConnect\AfpaConnect;
   
   /**
    * Class Initialize | file initialize.php
   ```
    2. Ajouter ces deux propriétés
    ```PHP
    public AfpaConnect $api;
    
    public static $_apiInstance = null;
    ```
    3. Dans le constructeur, créer une instance de la classe.
    ```PHP
   // Instance one time (singleton) AfpaConnect Interface Helper
    $this->api = self::getApi();
    ```
    4. Ajouter la méthode permettant de récupérer l'instance de la classe AfpaConnect
    ```PHP
    /**
     * Get AfpaConnect Interface Helper instance once.
     *
     * @return AfpaConnect
     */
    public static function getApi(): AfpaConnect
    {
        if (is_null(self::$_apiInstance)) {
            $conf = Configuration::getGlobalsINI();
            $publicKey = file_get_contents($conf['PATH_HOME'].$conf['API_PUBLIC_KEY']);
            self::$_apiInstance = new AfpaConnect($conf['API_HOSTNAME'], "afpanier", $publicKey);
        }
   
        return self::$_apiInstance;
    }
    ```
## Comment l'utiliser ?
Une nouvelle variable `$this->api` est maintenant disponible dans tous vos services.

### Exemple d'un POST
```PHP
$response = $this->api->post('register', [
    'username' => '123456789',
    'password' => 'test'
]);

// La réponse est au format JSON.
var_dump(json_decode($response));
```

### Exemple d'un GET
```PHP
$response = $this->api->get('user', [
    'username' => '123456789'
]);

// La réponse est au format JSON.
var_dump(json_decode($response));
```

Se référer à la documentation afin de connaître la [liste des routes](https://gitlab.com/afpaconnect/AfpaConnect/-/wikis/home).
