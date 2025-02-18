
# Bile Mo API 📱

> **This project was completed as part of my learning journey for the OpenClassrooms (PHP/Symfony Application Developer) program.**  
> --> *Version : [Français](README.md)* 📖 <br>
> --> *Documentation : [API Doc](https://github.com/TolMen/DocumentationAPIBileMo)* 📃

## 📖 Description

**Bile Mo API** is an API for a company specializing in the sale of high-end mobile phones, providing access to its catalog through a B2B (Business to Business) interface. <br>
Access to the API is restricted to registered clients who must authenticate via a JWT token. Data is exposed in JSON format, following levels 1, 2, and 3 of the Richardson model. <br>
Responses are cached to improve query performance.

## 🚀 Features

- **JWT Authentication** : Secure access via a JWT token to validate user identity.
- **Catalog Management** : Access to the company’s product catalog with detailed information on mobile phones.
- **Response Caching** : Responses are cached to optimize performance.
- **RESTful Architecture** : Follows the three-level Richardson model.
- **Secure Communication** : Secured exchanges with clients using JWT.

## 🚧 Installation

### Prerequisites

Before you start, ensure that you have the following installed on your machine :

- **PHP** (version 8.0 or higher)
- **Symfony** (version 7 or higher)
- **Composer**
- **MySQL Database**

### Installation steps

1. **Clone the repository**  
   Use Git to clone the project :  
   ```sh
   git clone https://github.com/TolMen/OCBileMo.git
   ```
2. **Install dependencies**  
   Run the following command to install the necessary libraries :  
   ```sh
   symfony console composer install
   ```

3. **Create the database**  
   Modify the `.env` file to configure your database :  
   ```sh
   DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name
   ```
   Then run the following commands :  
   ```sh
   symfony console doctrine:database:create
   php bin/console doctrine:schema:update --force
   ```

4. **Configure JWT**  
   Install the JWT package and generate secret keys :  
   ```sh
   composer require lexik/jwt-authentication-bundle
   php bin/console lexik:jwt:generate-keypair
   ```
   Make sure the paths to the private and public key files are correctly defined in your `.env` file :  
   ```sh
   JWT_PASSPHRASE=YourSecretPhrase
   ```
---

Thank you for exploring this project.  
Feel free to explore, modify, and improve it ! ✨  

**For any questions or collaboration, don't hesitate to contact me ! 📩**

[TolMen](https://github.com/TolMen) - [LinkedIn](https://www.linkedin.com/in/jessyfrachisse/)
