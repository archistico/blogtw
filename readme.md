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