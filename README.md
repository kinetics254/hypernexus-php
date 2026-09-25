# Hypernexus

A Laravel package for integrating with **Microsoft Dynamics 365 Business Central on-premises APIs**.

Hypernexus provides a Laravel-friendly interface for communicating with Business Central through its REST/OData APIs, with support for:

* Business Central on-premises
* NTLM authentication
* Basic authentication
* Configurable endpoints
* Default company configuration
* Switching between Business Central companies
* OData filtering
* Selecting fields
* Expanding related entities
* Ordering
* Pagination with OData skip tokens
* Creating records
* Updating records
* Deleting records
* GUID/system keys
* Composite/multiple primary keys
* Laravel's HTTP client
* Laravel 8 through Laravel 13
* PHP 8.1+

---

## Requirements

Hypernexus requires:

* PHP `8.1` or higher
* Laravel `8.x`, `9.x`, `10.x`, `11.x`, `12.x`, or `13.x`
* A reachable Microsoft Dynamics 365 Business Central on-premises instance
* Valid Business Central credentials
* Appropriate permissions for the Business Central APIs being accessed

For NTLM authentication, PHP must have cURL support enabled.

---

# Installation
## From GitHub

If the package has not yet been published to Packagist, add the GitHub repository to your application's `composer.json`:

```bash
"repositories": [ 
    { 
      "type": "vcs", 
      "url": "https://github.com/kinetics254/hypernexus-php" 
    }
]
```

Then require the package with a specific version:

```bash
composer require kinetics254/hypernexus:^0.1
```
For example, if the latest GitHub release is `v0.1.0`, Composer will resolve the `^0.1` constraint to a compatible `0.1.x` release.
Laravel package discovery automatically registers the package service provider.

You can verify the installation with:

```bash
composer show kinetics254/hypernexus
```

---

# Configuration

Publish the package configuration:

```bash
php artisan vendor:publish --tag=hypernexus-config
```

This creates:

```text
config/hypernexus.php
```

A typical configuration looks like:

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Business Central Base URL
    |--------------------------------------------------------------------------
    */

    'base_url' => env('BC_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    'username' => env('BC_USERNAME'),

    'password' => env('BC_PASSWORD'),

    'auth_type' => env('BC_AUTH_TYPE', 'NTLM'),

    /*
    |--------------------------------------------------------------------------
    | Default Company
    |--------------------------------------------------------------------------
    */

    'company' => env('BC_COMPANY'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeouts
    |--------------------------------------------------------------------------
    */

    'timeout' => env('BC_TIMEOUT', 300),

    'connect_timeout' => env('BC_CONNECT_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | API
    |--------------------------------------------------------------------------
    */

    'api' => [
        'per_page' => env('BC_PER_PAGE', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Endpoints
    |--------------------------------------------------------------------------
    */

    'endpoints' => [
        'customers' => '/api/v2.0/customers',

        'items' => '/api/v2.0/items',

        'sales_orders' => '/api/v2.0/salesOrders',
    ],

];
```

The endpoint configuration intentionally uses a simple structure:

```php
'endpoints' => [
    'customers' => '/api/v2.0/customers',
],
```

The key is the name you use in your application, while the value is the Business Central endpoint path.

---

# Environment Variables

Add your Business Central connection details to `.env`:

```dotenv
BC_BASE_URL=http://your-server:7048/BC
BC_USERNAME=your_username
BC_PASSWORD=your_password
BC_AUTH_TYPE=NTLM
BC_COMPANY=CRONUS International Ltd.
BC_TIMEOUT=300
BC_CONNECT_TIMEOUT=60
BC_PER_PAGE=50
```

For example:

```dotenv
BC_BASE_URL=http://192.168.1.100:7048/BC
BC_USERNAME=administrator
BC_PASSWORD=secret
BC_AUTH_TYPE=NTLM
BC_COMPANY=CRONUS International Ltd.
```

Do not commit Business Central credentials to source control.

---

# Authentication

Hypernexus supports:

* NTLM
* Basic Authentication

## NTLM

NTLM is commonly used with Business Central on-premises installations.

Configure:

```dotenv
BC_AUTH_TYPE=NTLM
```

and provide the appropriate credentials:

```dotenv
BC_USERNAME=DOMAIN\username
BC_PASSWORD=your-password
```

The exact username format depends on the Windows/server authentication configuration.

Hypernexus configures the underlying HTTP client to use cURL's NTLM authentication.

---

## Basic Authentication

If the Business Central environment is configured for Basic Authentication:

```dotenv
BC_AUTH_TYPE=BASIC
```

Hypernexus will use the configured username and password for HTTP Basic Authentication.

---

# Registering Endpoints

Business Central exposes many APIs and OData endpoints.

Hypernexus keeps endpoint registration in the package configuration rather than hard-coding endpoint URLs throughout your application.

For example:

```php
'endpoints' => [
    'customers' => '/api/v2.0/customers',
    'items' => '/api/v2.0/items',
    'sales_orders' => '/api/v2.0/salesOrders',
],
```

You can then access an endpoint using its configured name:

```php
Hypernexus::endpoint('customers');
```

This means your application code does not need to repeatedly know the actual Business Central URL.

For example, instead of:

```php
Http::get(
    $baseUrl . '/api/v2.0/customers'
);
```

your application uses:

```php
Hypernexus::endpoint('customers');
```

This keeps Business Central endpoint definitions centralized in configuration.

---

# Endpoint Names

Endpoint names are application-level aliases.

For example:

```php
'endpoints' => [
    'customers' => '/api/v2.0/customers',
    'sales_orders' => '/api/v2.0/salesOrders',
],
```

The following are equivalent mappings:

```text
customers    → /api/v2.0/customers
sales_orders → /api/v2.0/salesOrders
```

Your application uses:

```php
Hypernexus::endpoint('customers');
```

or:

```php
Hypernexus::endpoint('sales_orders');
```

This also means you can change the underlying Business Central URL without changing application code.

---

# Company Context

Hypernexus uses a company context for Business Central requests.

The default company is configured using:

```dotenv
BC_COMPANY=CRONUS International Ltd.
```

or:

```php
'company' => env('BC_COMPANY'),
```

The configured company is used when communicating with registered endpoints.

For example:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->get();
```

uses the configured Business Central company.

---

# Changing the Company

You can override the configured company for a specific operation:

```php
$customers = Hypernexus::company('Another Company')
    ->endpoint('customers')
    ->query()
    ->get();
```

This allows the same Laravel application to communicate with multiple Business Central companies.

For example:

```php
$companyACustomers = Hypernexus::company('Company A')
    ->endpoint('customers')
    ->query()
    ->get();

$companyBCustomers = Hypernexus::company('Company B')
    ->endpoint('customers')
    ->query()
    ->get();
```

Changing the company this way does not modify the configured default company.

---

# Using the Facade

Import the Hypernexus facade:

```php
use KTL\Hypernexus\Facades\Hypernexus;
```

You can then access Business Central:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->get();
```

---

# Querying Business Central

The query builder provides a Laravel-friendly way of constructing OData queries.

Basic example:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->get();
```

Filters and other OData parameters can then be chained onto the query.

---

# Filtering

## Basic Where

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->where('displayName', '=', 'John Doe')
    ->get();
```

This generates an OData filter equivalent to:

```text
displayName eq 'John Doe'
```

---

## Supported Operators

Hypernexus supports the following operators:

| Operator | OData |
| -------- | ----- |
| `=`      | `eq`  |
| `==`     | `eq`  |
| `!=`     | `ne`  |
| `<>`     | `ne`  |
| `>`      | `gt`  |
| `>=`     | `ge`  |
| `<`      | `lt`  |
| `<=`     | `le`  |

Example:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->where('balance', '>', 1000)
    ->get();
```

---

# Multiple Conditions

Conditions can be chained:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->where('blocked', '=', false)
    ->where('balance', '>', 1000)
    ->get();
```

The resulting conditions are joined with `and`.

---

# OR Conditions

Use `orWhere()`:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->where('countryRegionCode', '=', 'KE')
    ->orWhere('countryRegionCode', '=', 'UG')
    ->get();
```

---

# Array Conditions

Multiple equality conditions can be passed as an array:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->where([
        'countryRegionCode' => 'KE',
        'blocked' => false,
    ])
    ->get();
```

---

# whereIn

Use `whereIn()` to match multiple values:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->whereIn('countryRegionCode', [
        'KE',
        'UG',
        'TZ',
    ])
    ->get();
```

This generates an OData expression equivalent to:

```text
(countryRegionCode eq 'KE'
    or countryRegionCode eq 'UG'
    or countryRegionCode eq 'TZ')
```

---

# whereNotIn

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->whereNotIn('countryRegionCode', [
        'KE',
        'UG',
    ])
    ->get();
```

---

# Contains

Use `contains()` for string searches:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->contains('displayName', 'Limited')
    ->get();
```

This generates:

```text
contains(displayName, 'Limited')
```

---

# Raw Values

Hypernexus automatically formats values for OData.

If you need to provide an OData expression directly, use the `raw` parameter:

```php
$query = Hypernexus::endpoint('customers')
    ->query()
    ->where(
        'someField',
        '=',
        'someODataExpression',
        raw: true
    );
```

When `raw` is `true`, Hypernexus does not quote or otherwise convert the supplied value.

Raw values should only be used when the supplied value is already valid OData.

---

# GUID Values

Hypernexus automatically recognizes GUID values.

For example:

```php
$customer = Hypernexus::endpoint('customers')
    ->query()
    ->where(
        'id',
        '=',
        'ae523e3b-aa76-f111-9e2d-000c2910b0e7'
    )
    ->first();
```

The resulting OData filter is:

```text
id eq ae523e3b-aa76-f111-9e2d-000c2910b0e7
```

rather than:

```text
id eq 'ae523e3b-aa76-f111-9e2d-000c2910b0e7'
```

This is important for Business Central fields whose OData type is `Edm.Guid`.

---

# Selecting Fields

Use `select()` to specify the fields returned by Business Central:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->select([
        'id',
        'number',
        'displayName',
        'email',
    ])
    ->get();
```

You can also pass fields directly to `get()`:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->get([
        'id',
        'displayName',
        'email',
    ]);
```

---

# Expanding Related Entities

Use `with()` to generate OData's `$expand` parameter.

For example:

```php
$orders = Hypernexus::endpoint('sales_orders')
    ->query()
    ->with('customer')
    ->get();
```

Multiple relationships can be expanded:

```php
$orders = Hypernexus::endpoint('sales_orders')
    ->query()
    ->with([
        'customer',
        'salesOrderLines',
    ])
    ->get();
```

Multiple `with()` calls can also be chained:

```php
$query = Hypernexus::endpoint('sales_orders')
    ->query()
    ->with('customer')
    ->with('salesOrderLines');
```

---

# Ordering

Order results using `orderBy()`:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->orderBy('displayName')
    ->get();
```

Descending order:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->orderBy('displayName', 'desc')
    ->get();
```

Supported directions are:

```text
asc
desc
```

---

# Limiting Results

Use `top()`:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->top(10)
    ->get();
```

This generates:

```text
$top=10
```

---

# Skipping Results

Use `skip()`:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->skip(20)
    ->top(10)
    ->get();
```

---

# Counting Records

Use `count()`:

```php
$count = Hypernexus::endpoint('customers')
    ->query()
    ->where('blocked', '=', false)
    ->count();
```

Hypernexus requests the OData count from Business Central and returns it as an integer.

---

# Getting the First Record

Use `first()` when only one record is required:

```php
$customer = Hypernexus::endpoint('customers')
    ->query()
    ->where(
        'id',
        '=',
        'ae523e3b-aa76-f111-9e2d-000c2910b0e7'
    )
    ->first();
```

The method returns the first matching record or `null` if no record is found.

---

# Pagination

Hypernexus supports Business Central's OData pagination and skip-token mechanism.

```php
$page = Hypernexus::endpoint('customers')
    ->query()
    ->paginate(50);
```

The paginator provides:

```php
$page->items();
$page->currentCursor();
$page->nextCursor();
$page->hasMorePages();
$page->total();
$page->perPage();
$page->count();
```

It is also iterable:

```php
foreach ($page as $customer) {
    // Process customer
}
```

Check whether another page exists:

```php
if ($page->hasMorePages()) {
    $nextCursor = $page->nextCursor();
}
```

---

## Continuing With a Cursor

Pass the returned cursor to the next request:

```php
$page = Hypernexus::endpoint('customers')
    ->query()
    ->paginate(
        perPage: 50,
        cursor: $nextCursor,
    );
```

Hypernexus handles the Business Central `$skiptoken` internally.

---

## Pagination Data

The paginator can be converted to an array:

```php
$page->toArray();
```

The result contains:

```php
[
    'data' => [...],
    'currentCursor' => '...',
    'nextCursor' => '...',
    'total' => 250,
    'perPage' => 50,
    'hasMorePages' => true,
]
```

---

# Creating Records

Create a Business Central record with `create()`:

```php
$customer = Hypernexus::endpoint('customers')
    ->create([
        'displayName' => 'John Doe',
        'email' => 'john@example.com',
    ]);
```

The query builder also supports creation:

```php
$customer = Hypernexus::endpoint('customers')
    ->query()
    ->create([
        'displayName' => 'John Doe',
        'email' => 'john@example.com',
    ]);
```

Both send a `POST` request to the configured endpoint.

---

# Updating Records

Update a Business Central record using its key.

For a GUID/system key:

```php
$customer = Hypernexus::endpoint('customers')
    ->update(
        'ae523e3b-aa76-f111-9e2d-000c2910b0e7',
        [
            'displayName' => 'Jane Doe',
        ]
    );
```

The query builder provides the same functionality:

```php
$customer = Hypernexus::endpoint('customers')
    ->query()
    ->update(
        'ae523e3b-aa76-f111-9e2d-000c2910b0e7',
        [
            'displayName' => 'Jane Doe',
        ]
    );
```

Hypernexus formats GUID keys as OData GUID values rather than quoted strings.

---

# Composite / Multiple Primary Keys

Business Central entities do not necessarily use a single GUID as their key.

Hypernexus supports entities addressed using multiple key fields.

Pass an associative array:

```php
$record = Hypernexus::endpoint('some_endpoint')
    ->update(
        [
            'documentType' => 'Order',
            'documentNo' => 'SO-10001',
            'lineNo' => 10000,
        ],
        [
            'quantity' => 5,
        ]
    );
```

The key portion is formatted using OData value rules:

```text
documentType='Order'
documentNo='SO-10001'
lineNo=10000
```

GUID values are also handled correctly:

```php
[
    'id' => 'ae523e3b-aa76-f111-9e2d-000c2910b0e7',
    'lineNo' => 10000,
]
```

The GUID remains unquoted while numeric values remain numeric.

---

# Deleting Records

Delete a record using its system/GUID key:

```php
Hypernexus::endpoint('customers')
    ->delete(
        'ae523e3b-aa76-f111-9e2d-000c2910b0e7'
    );
```

Or through the query builder:

```php
Hypernexus::endpoint('customers')
    ->query()
    ->delete(
        'ae523e3b-aa76-f111-9e2d-000c2910b0e7'
    );
```

Composite keys are also supported:

```php
Hypernexus::endpoint('some_endpoint')
    ->delete([
        'documentType' => 'Order',
        'documentNo' => 'SO-10001',
        'lineNo' => 10000,
    ]);
```

---

# Query Builder Chaining

The query builder is designed to allow multiple OData operations to be composed naturally.

For example:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->select([
        'id',
        'number',
        'displayName',
        'email',
    ])
    ->where('blocked', '=', false)
    ->where('countryRegionCode', '=', 'KE')
    ->contains('displayName', 'Limited')
    ->orderBy('displayName')
    ->top(50)
    ->get();
```

This allows application code to express the intended Business Central query without manually constructing an OData query string.

---

# Raw OData Parameters

For OData functionality that does not have a dedicated query-builder method, use `query()`:

```php
$customers = Hypernexus::endpoint('customers')
    ->query()
    ->query([
        '$count' => 'true',
        '$top' => 100,
    ])
    ->get();
```

Parameters are merged into the existing query parameters.

This provides an escape hatch for Business Central-specific OData features.

---

# Direct Endpoint Requests

You do not always need the query builder.

For a simple GET:

```php
$response = Hypernexus::endpoint('customers')
    ->get();
```

You can also pass query parameters directly:

```php
$response = Hypernexus::endpoint('customers')
    ->get([
        '$top' => 10,
    ]);
```

This is useful when you already have the OData parameters you need.

---

# Custom Business Central Endpoints

Hypernexus is not restricted to the standard Business Central APIs.

You can register custom Business Central endpoints:

```php
'endpoints' => [
    'customer_balances' => '/ODataV4/CustomerBalances',
],
```

Then use them exactly like standard endpoints:

```php
$balances = Hypernexus::endpoint('customer_balances')
    ->query()
    ->get();
```

This allows the same package to work with:

* Standard Business Central APIs
* Custom APIs
* OData pages
* OData queries
* Other Business Central endpoints exposed by your installation

---

# Dependency Injection

The facade is convenient, but the main Business Central service can also be injected.

```php
use KTL\Hypernexus\BusinessCentral;

class CustomerService
{
    public function __construct(
        protected BusinessCentral $businessCentral,
    ) {
    }

    public function customers(): array
    {
        return $this->businessCentral
            ->endpoint('customers')
            ->query()
            ->get();
    }
}
```

This is useful when you prefer explicit dependencies over facades.

---

# Error Handling

Hypernexus provides package-specific exceptions for Business Central/API errors.

The package includes exceptions such as:

```text
BusinessCentralException
ApiException
AuthenticationException
AuthorizationException
EndpointNotRegisteredException
NotFoundException
ValidationException
```

You can catch the base exception:

```php
use KTL\Hypernexus\Exceptions\BusinessCentralException;

try {
    $customers = Hypernexus::endpoint('customers')
        ->query()
        ->get();
} catch (BusinessCentralException $e) {
    report($e);
}
```

Specific exceptions can also be caught where appropriate.

---

# API Exceptions

`ApiException` contains information about the HTTP response:

```php
use KTL\Hypernexus\Exceptions\ApiException;

try {
    $customer = Hypernexus::endpoint('customers')
        ->create($data);
} catch (ApiException $e) {
    $status = $e->status;
    $response = $e->response;

    report($e);
}
```

This allows applications to inspect the Business Central response when troubleshooting API failures.

---

# EndpointNotRegisteredException

If you request an endpoint that has not been configured:

```php
Hypernexus::endpoint('unknown_endpoint');
```

Hypernexus throws:

```text
EndpointNotRegisteredException
```

Make sure the endpoint exists in:

```text
config/hypernexus.php
```

For example:

```php
'endpoints' => [
    'customers' => '/api/v2.0/customers',
],
```

---

# HTTP Timeouts

Hypernexus provides configurable HTTP timeouts.

The defaults are:

```php
'timeout' => 300,
'connect_timeout' => 60,
```

They can be changed through `.env`:

```dotenv
BC_TIMEOUT=120
BC_CONNECT_TIMEOUT=30
```

`BC_TIMEOUT` controls the maximum duration of the HTTP request.

`BC_CONNECT_TIMEOUT` controls how long the client waits while establishing the connection.

---

# Business Central Updates and Deletes

For modifying Business Central resources, Hypernexus uses the appropriate HTTP methods:

| Operation | HTTP method |
| --------- | ----------- |
| Retrieve  | `GET`       |
| Create    | `POST`      |
| Update    | `PATCH`     |
| Replace   | `PUT`       |
| Delete    | `DELETE`    |

For resource modifications, Hypernexus also sends the Business Central-compatible:

```text
If-Match: *
```

header where required.

---

# Laravel Example

A typical Laravel service can encapsulate Business Central operations:

```php
<?php

namespace App\Services;

use KTL\Hypernexus\Facades\Hypernexus;

class BusinessCentralCustomerService
{
    public function find(string $id): ?array
    {
        return Hypernexus::endpoint('customers')
            ->query()
            ->where('id', '=', $id)
            ->first();
    }

    public function search(string $name): array
    {
        return Hypernexus::endpoint('customers')
            ->query()
            ->contains('displayName', $name)
            ->orderBy('displayName')
            ->get();
    }

    public function create(array $data): array
    {
        return Hypernexus::endpoint('customers')
            ->create($data);
    }

    public function update(string $id, array $data): array
    {
        return Hypernexus::endpoint('customers')
            ->update($id, $data);
    }

    public function delete(string $id): array
    {
        return Hypernexus::endpoint('customers')
            ->delete($id);
    }
}
```

Your controller can then focus on application logic:

```php
class CustomerController
{
    public function __construct(
        protected BusinessCentralCustomerService $customers,
    ) {
    }

    public function show(string $id)
    {
        $customer = $this->customers->find($id);

        return response()->json($customer);
    }
}
```

---

# Multiple Business Central Companies

A Laravel application can communicate with multiple Business Central companies.

For example:

```php
$kenyaCustomers = Hypernexus::company('Kenya Company')
    ->endpoint('customers')
    ->query()
    ->get();

$ugandaCustomers = Hypernexus::company('Uganda Company')
    ->endpoint('customers')
    ->query()
    ->get();
```

The company override only applies to that Hypernexus instance. The configured default company remains unchanged.

---

# Testing

Hypernexus uses Laravel's HTTP client internally, making the package suitable for testing with Laravel HTTP fakes.

For example:

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    '*' => Http::response([
        'value' => [
            [
                'id' => 'ae523e3b-aa76-f111-9e2d-000c2910b0e7',
                'displayName' => 'Test Customer',
            ],
        ],
    ], 200),
]);
```

Your application can then execute Hypernexus normally without making a real Business Central request.

For integration testing, configure Hypernexus against an actual Business Central test environment.

---

# Compatibility

| Component                    | Supported |
| ---------------------------- | --------- |
| PHP                          | 8.1+      |
| Laravel 8                    | Yes       |
| Laravel 9                    | Yes       |
| Laravel 10                   | Yes       |
| Laravel 11                   | Yes       |
| Laravel 12                   | Yes       |
| Laravel 13                   | Yes       |
| Business Central On-Premises | Yes       |
| NTLM                         | Yes       |
| Basic Authentication         | Yes       |

Hypernexus is primarily designed for **Business Central on-premises environments**, particularly installations where NTLM authentication is required.

---

# Architecture

Hypernexus separates the major responsibilities of the integration.

```text
BusinessCentral
       │
       ├── EndpointRegistry
       │       └── Resolves configured endpoint paths
       │
       ├── Endpoint
       │       └── Represents a Business Central resource
       │
       ├── QueryBuilder
       │       └── Builds OData queries
       │
       ├── Paginator
       │       └── Handles OData pagination
       │
       └── BusinessCentralClient
               ├── Authentication
               ├── HTTP requests
               ├── Company context
               └── API responses
```

The application interacts primarily with:

```text
BusinessCentral
Endpoint
QueryBuilder
Paginator
```

while the HTTP and authentication details remain inside the package.

---

# Design Philosophy

Hypernexus is built around several principles.

## Laravel-friendly

The package should feel natural inside a Laravel application:

```php
Hypernexus::endpoint('customers')
    ->query()
    ->where('blocked', false)
    ->get();
```

## Configuration-driven

Business Central endpoint URLs are registered in:

```text
config/hypernexus.php
```

rather than being scattered throughout application code.

## OData-aware

Hypernexus understands common OData functionality including:

* `$filter`
* `$select`
* `$expand`
* `$orderby`
* `$top`
* `$skip`
* `$skiptoken`
* `$count`

## Business Central-aware

Hypernexus is designed specifically around Business Central integration requirements, including:

* Company context
* NTLM authentication
* Basic authentication
* Business Central resource keys
* GUID keys
* Composite keys
* `If-Match` headers
* OData pagination
* Custom Business Central endpoints

---

# Complete Example

A complete example of querying customers:

```php
use KTL\Hypernexus\Facades\Hypernexus;

$page = Hypernexus::endpoint('customers')
    ->query()
    ->select([
        'id',
        'number',
        'displayName',
        'email',
    ])
    ->where('blocked', '=', false)
    ->contains('displayName', 'Ltd')
    ->orderBy('displayName')
    ->paginate(50);

foreach ($page as $customer) {
    echo $customer['displayName'];
}
```

Creating a customer:

```php
$customer = Hypernexus::endpoint('customers')
    ->create([
        'displayName' => 'Example Company Ltd',
        'email' => 'accounts@example.com',
    ]);
```

Updating the customer:

```php
Hypernexus::endpoint('customers')
    ->update(
        $customer['id'],
        [
            'displayName' => 'Updated Company Ltd',
        ]
    );
```

Deleting the customer:

```php
Hypernexus::endpoint('customers')
    ->delete($customer['id']);
```

---

# Security

Business Central credentials should be stored in environment variables or another secure secret-management system.

Use:

```dotenv
BC_USERNAME=...
BC_PASSWORD=...
```

rather than hard-coding credentials in PHP source code.

Do not commit `.env` files containing Business Central credentials to source control.

For production environments, use your deployment platform's secret/environment-variable management facilities where possible.

---

# Troubleshooting

## Authentication failures

Check:

1. `BC_BASE_URL` is correct.
2. `BC_USERNAME` is correct.
3. `BC_PASSWORD` is correct.
4. `BC_AUTH_TYPE` matches your Business Central/server configuration.
5. PHP has cURL enabled.
6. The Laravel server can reach the Business Central server.
7. The configured Business Central user has the required permissions.

For NTLM:

```dotenv
BC_AUTH_TYPE=NTLM
```

For Basic authentication:

```dotenv
BC_AUTH_TYPE=BASIC
```

---

## 401 Unauthorized

A `401 Unauthorized` response generally indicates an authentication problem.

Verify the credentials and authentication configuration.

For NTLM environments, also verify that the Laravel application server can reach the Business Central host and port.

---

## 403 Forbidden

A `403 Forbidden` response generally means the authenticated user reached Business Central but does not have sufficient permissions for the requested resource.

Check the user's Business Central permissions and permission sets.

---

## GUID filter errors

If Business Central reports:

```text
A binary operator with incompatible types was detected.
Found operand types 'Edm.Guid' and 'Edm.String'
```

the GUID may have been sent as a quoted OData string.

Hypernexus automatically handles standard GUID values:

```php
->where(
    'id',
    '=',
    'ae523e3b-aa76-f111-9e2d-000c2910b0e7'
)
```

as:

```text
id eq ae523e3b-aa76-f111-9e2d-000c2910b0e7
```

rather than:

```text
id eq 'ae523e3b-aa76-f111-9e2d-000c2910b0e7'
```

---

# Contributing

Contributions are welcome.

Before submitting a pull request:

1. Add or update tests for the change.
2. Ensure compatibility with supported Laravel versions.
3. Follow the existing project structure and coding conventions.
4. Keep Business Central-specific functionality reusable where possible.
5. Update the documentation when changing the public API.

---

# License

Hypernexus is open-source software licensed under the:

**GNU General Public License v3.0 or later (GPL-3.0-or-later).**

See the `LICENSE` file for the full license text.

---

# Author

**Peter Karuga**

**kinetics254/hypernexus**

A Laravel package for integrating applications with Microsoft Dynamics 365 Business Central on-premises.
