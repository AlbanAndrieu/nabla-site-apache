<!-- markdown-link-check-disable-next-line -->
---
layout: default
---

Text can be **bold**, _italic_, or ~~strikethrough~~.



# [![Nabla](https://nabla.albandrieu.com/assets/nabla/nabla-4.png)](https://github.com/AlbanAndrieu/nabla-site-apache) nabla-site-apache

[![License: APACHE](http://img.shields.io/:license-apache-blue.svg?style=flat-square)](http://www.apache.org/licenses/LICENSE-2.0.html)

[CHANGELOG](./CHANGELOG.html).

## Project Goal

This is a simple HTML project for Nabla company that promotes Alban Andrieu as an experienced DevSecOps professional.

Default nabla files for apache

```bash
npm run start-python
# Cloudflare wrangler
npm run start
```

For vercel

```
vercel deploy
vercel --prod
```

For vercel [php](https://github.com/vercel-community/php)

```bash
php -S localhost:8000 api/index.php
```

For Apache

```bash
aa-teardown
sudo service apache2 restart
sudo service php8.4-fpm restart
tail -f /var/log/apache2/error.log
```

[php-framework-symfony-microservice](https://github.com/contributte/vercel-examples/tree/master/php-framework-symfony-microservice)

```bash
# Python 2
python -m SimpleHTTPServer 8001

# Python 3
python -m http.server 8001
```

### Terraform

[terraform-s3-static-website-hosting](https://www.alexhyett.com/terraform-s3-static-website-hosting)

# Contributing

The [issue tracker](https://github.com/AlbanAndrieu/nabla-site-apache/issues) is the preferred channel for bug reports, features requests and submitting pull requests.

For pull requests, editor preferences are available in the [editor config](.editorconfig) for easy use in common text editors. Read more and download plugins at <http://editorconfig.org>.

## License

[Apache v2](http://www.apache.org/licenses/LICENSE-2.0.html)

______________________________________________________________________

Alban Andrieu [linkedin](https://fr.linkedin.com/in/nabla/)
