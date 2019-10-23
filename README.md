# DataTable & Searchable Trait

## Searchable Trait

Add the trait to your model and the attribute "searcheable" with the fields to search.

```php
<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Gmlo\DataTable\Traits\Searchable;

class Products extends Model
{
    use Searchable;
    protected $searchables = ['name', 'code'];
}
```

#### Use 
```php
$result = Products::search()->orderBy('name')->get();
```
You can optionally send the parameters:
```php
$limit = 20; // Number of items to take. Default: 15.
$query_input = 'filter'; // Name of the field sent by the request to search. Default: 'query'
$result = Products::search($limit, $query_input)->orderBy('name')->get();
```

## DataTable
VueJs DataTable for Laravel 6

### Installation

Run composer
```sh
composer require gmlo/datatable
```

Publish the component to add on your js
```sh
php artisan vendor:publish --provider="Gmlo\DataTable\DataTableServiceProvider" --tag="vue-components"
```

Import on your js file
```js
require('./../vendor/datatable/app');
```

License
----

MIT


**Free Software, Hell Yeah!**
