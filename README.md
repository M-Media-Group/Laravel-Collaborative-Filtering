# Very short description of the package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mmedia/laravel-collaborative-filtering.svg?style=flat-square)](https://packagist.org/packages/mmedia/laravel-collaborative-filtering)
[![Total Downloads](https://img.shields.io/packagist/dt/mmedia/laravel-collaborative-filtering.svg?style=flat-square)](https://packagist.org/packages/mmedia/laravel-collaborative-filtering)
![GitHub Actions](https://github.com/mmedia/laravel-collaborative-filtering/actions/workflows/main.yml/badge.svg)

Get related models for the current model. Commonly used for "similar products" sections.

## Installation

You can install the package via composer:

```bash
composer require mmedia/laravel-collaborative-filtering
```

## Usage

Imagine you have a model called `Product`, and each product has multiple `ProductCategory` records. You want to find products related to each other based on how many common categories they have (a.k.a using collaborative filtering). To do so, you can define a relationship in your `Product` model.

```php
use MMedia\LaravelCollaborativeFiltering\HasCollaborativeFiltering;

class Product extends Model {

    use HasCollaborativeFiltering;

    public function related()
    {
        return $this->hasManyRelatedThrough(ProductCategory::class, 'category_id');
    }

    public function relatedThroughLikes()
    {
        return $this->hasManyRelatedThrough(ProductLikes::class, 'user_id');
    }

}
```

Based on the article from [arctype](https://arctype.com/blog/collaborative-filtering-tutorial/).

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email contact@mmediagroup.fr instead of using the issue tracker.

## Credits

-   [M Media](https://github.com/mmedia)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
