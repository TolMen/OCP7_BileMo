# API Bile Mo

> **Ce projet a été réalisé dans le cadre de mon apprentissage pour le parcours d'OpenClassrooms (Développeur d'application PHP/Symfony).**

BileMo is a company specializing in the sale of high-end mobile phones. 
It offers access to its catalog via an API, for other platforms, thus adopting a B2B (business to business) sales model.

Access to the API will be reserved for referenced clients, who must authenticate via a JWT Token. 
The data is exposed in JSON format, respecting levels 1, 2 and 3 of the Richardson model. 
Responses are cached to improve query performance.

## Installation

To install and run this project, follow the steps below :

### Prerequisites
- **PHP** (version 8.0 ou supérieure)
- **Symfony** (version 7 ou supérieure)
- **Composer**
- **Une base de données**

### Installation steps

<p><strong>1 - Git clone the project</strong></p>
<pre>
    <code>https://github.com/TolMen/OCBileMo</code>
</pre>

<p><strong>2 - Install libraries</strong></p>

- symfony console composer install

<p><strong>3 - Create database</strong></p>

- Update DATABASE_URL .env file with your database configuration :  <br>
DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name <br> <br>

- Create database : <br> symfony console doctrine:database:create <br> <br>

- Create database structure : <br> php bin/console doctrine:schema:update --force <br> <br>

- Insert fictive data (optional) : <br> symfony console doctrine:fixtures:load <br> <br>

<p><strong>4 - Configure JWT</strong></p>

- Install the JWT package by running the following command :  <br>
composer require lexik/jwt-authentication-bundle <br> <br>

- Generate a secret key for JWT : <br> php bin/console lexik:jwt:generate-keypair
 <br> <br>

- Make sure the paths to the private and public key files are set correctly in your .env file : <br> JWT_PASSPHRASE=VotrePhraseSecrète <br> <br>

## Author

[TolMen](https://github.com/TolMen) - [LinkedIn](https://www.linkedin.com/in/jessyfrachisse/)

## License

This project is licensed under MIT - View file [license](LICENSE) for more details.

Feel free to contact me with any questions or contributions. Have a nice visit on our blog !
