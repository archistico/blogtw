## Passaggi
```
symfony new blogtw --full  
cd blogtw  
symfony serve -d  
composer require symfony/webpack-encore-bundle  
npm install  
code .  
npm install -D tailwindcss postcss-loader purgecss-webpack-plugin glob-all path autoprefixer
```

### Configurare postcss
File postcss.config.js (alla radice del progetto)  
Info https://symfony.com/doc/current/frontend/encore/postcss.html
```
module.exports = {
    plugins: [
        require('tailwindcss'),
        require('autoprefixer'),
    ],
};
```
### Modificare webpack.config.js
Dopo la dichiarazione ```var Encore``` in linea 1
```
const PurgeCssPlugin = require('purgecss-webpack-plugin');
const glob = require('glob-all');
const path = require('path');
```
Successivamente attivare il PostCssLoader  
Prima del Feature config
```
.enablePostCssLoader()
```
### Aggiungere a assets/style/app.css
Info https://tailwindcss.com/docs/installation  
```
@import "tailwindcss/base";
@import "tailwindcss/components";
@import "tailwindcss/utilities";
```
### Test di una build
```
npm run build
```
oppure ```npm run watch```
### Creazione pagina di demo
Creazione di una route con un controller
```
symfony console make:controller Demo
```
### Modifica di base.html.twig in templates
Tolgo i commenti a  
```
{{ encore_entry_link_tags('app') }}
{{ encore_entry_script_tags('app') }}
```
### Attivazione PurgeCss solo per produzione
Per evitare css enormi https://markrailton.com/blog/using-tailwind-css-and-purgecss-with-symfony-encore  
Aggiungere il codice a webpack.config.js nella penultima riga
```
if (Encore.isProduction()) {
  Encore.addPlugin(new PurgeCssPlugin({
        paths: glob.sync([
            path.join(__dirname, 'templates/**/*.html.twig')
        ]),
        defaultExtractor: (content) => {
            return content.match(/[\w-/:]+(?<!:)/g) || [];
        }
    }));
}
```
### Spegnere il server in background
```
symfony local:server:stop
```
### Sistemare l'.env.local per avere il database sqlite
```
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```
Commento quello di default POSTGRES  
Creo la base dati  
```
symfony console d:d:c
```
### Creo il controller che voglio tenere riservato
```
symfony console make:controller Riservato
```
E modifico a piacimento il file html  
### Proteggo la route
Vado sul file config/packages/security.yaml  
Sotto ```access_control:```
```
- { path: ^/riservato, roles: ROLE_USER }
```
### Creazione degli User
```
symfony console make:user
symfony console make:migration
symfony console d:m:m
```
### Creazione parte Auth
```
symfony console make:auth
```
Ora ho una pagina di login e logout ```https://127.0.0.1:8000/login``` ma non ho il formulario di registrazione  
### Creazione pagina di registrazione
```
symfony console make:registration-form
```
In src/Security/AppAuthenticator.php decommentare la route su cui vuoi puntare
```
return new RedirectResponse($this->urlGenerator->generate('riservato'));
```
### Installazione di SchebTwoFactorBundle
Sulla base di https://yoandev.co/authentification-2fa-avec-symfony  
Si trova a https://symfony.com/bundles/SchebTwoFactorBundle/current/index.html
```
composer require 2fa
composer require scheb/2fa-email
```
In config/packages/security.yaml aggiungere sotto firewalls stesso livello di main
```
    two_factor:
        auth_form_path: 2fa_login    # The route name you have used in the routes.yaml
        check_path: 2fa_login_check  # The route name you have used in the routes.yaml
```
In config/packages/scheb_2fa.yaml decommentare solo questa linea (commenta la prima)
```
- Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken
```
E inoltre precisare che vogliamo un 2fa per email per cui aggiungiamo sempre sotto quel file (allo stesso livello di security_tokens)
```
email:
    digits: 6
    enabled: true
    sender_email: no-reply@test.com # Mail di invio
    sender_name: Emilie Rollandin  # Optional
```
Devo registrare queste info su User per cui modifico src/Entity/User.php
```
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
```
```
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
```
```
    /**
     * @ORM\Column(type="string", nullable=true)
    */
    private $authCode;
```
```
    public function isEmailAuthEnabled(): bool
    {
        return true; // This can be a persisted field to switch email code authentication on/off
    }

    public function getEmailAuthRecipient(): string
    {
        return $this->email;
    }

    public function getEmailAuthCode(): string
    {
        if (null === $this->authCode) {
            throw new \LogicException('The email authentication code was not set');
        }

        return $this->authCode;
    }

    public function setEmailAuthCode(string $authCode): void
    {
        $this->authCode = $authCode;
    }
```
Faccio la migrazione
```
symfony console make:migration
symfony console d:m:m
```
### Aggiunta trusted device
```
composer require scheb/2fa-trusted-device
```
In config/packages/scheb_2fa.yaml
```
trusted_device:
    enabled: true                 # If the trusted device feature should be enabled
    lifetime: 5184000              # Lifetime of the trusted device token
    extend_lifetime: false         # Automatically extend lifetime of the trusted cookie on re-login
    cookie_name: trusted_device    # Name of the trusted device cookie
    cookie_secure: false           # Set the 'Secure' (HTTPS Only) flag on the trusted device cookie
    cookie_same_site: "lax"        # The same-site option of the cookie, can be "lax" or "strict"
    cookie_path: "/"               # Path to use when setting the cookie
```
In security
```
two_factor:
    auth_form_path: 2fa_login    # The route name you have used in the routes.yaml
    check_path: 2fa_login_check  # The route name you have used in the routes.yaml
    trusted_parameter_name: _trusted  # Name of the parameter for the trusted device option
```
### Configurazione mailer
```
composer require symfony/mailer
composer require symfony/google-mailer
```
.env.local
```
MAILER_DSN=smtp://username:password@mail.tophost.it:587
```