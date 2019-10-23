# DataTable & Searchable Trait

### Installation

Run composer
```sh
composer require gmlo89/datatable
```

Publish the component to add on your js
```sh
php artisan vendor:publish --provider="Gmlo\DataTable\DataTableServiceProvider" --tag="vue-components"
```

Import on your js file
```js
require('./../vendor/datatable/app');
```


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

### Example on FrontEnd
```html
<admin-table
    url-create="/product/create"
    url-api="/api/product"
    :headers="[
        { value: 'name', text: 'Product', sorteable: true },
        { value: 'price', text: 'Price' },
        { value: 'brand_name', text: 'Brand' },
        { value: 'button', slot: 'columnaction' }
    ]">
     <!-- Title of table -->
    <template v-slot:title>
        <h1><i class="fas fa-shopping-basket text-primary"></i> Products</h1>
    </template>

    <!-- customized cell -->
    <template v-slot:columnaction="{ item }">
        <a :href="`/product/${item.id}`" class="btn btn-sm">
            Detalles <i class="fas fa-caret-right"></i>
        </a>
    </template>
</admin-table>
```
### Backend
```php
    $query = Product::join('categories', 'products.category_id', '=', 'categories.id')
        ->join('brands', 'products.brand_id', '=', 'brands.id')
        ->selectRaw('products.*, categories.name as category_name, brands.name as brand_name');

    return dataTable()->query($query)
            ->setFilters(
                'categories.name', 'products.name',
                'products.code', 'products.ean',
                'products.upc', 'products.part_number',
                'products.model', 'brands.name'
            )
            ->configColumn('price', function($value, $row){
                return '$' . number_format($value, 2) . ' ' . $row->currency;
            })
            ->get();
```

License
----

MIT


**Free Software, Hell Yeah!**
