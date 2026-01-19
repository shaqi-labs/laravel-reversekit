# Laravel ReverseKit

[![Laravel](https://img.shields.io/badge/Laravel-10%2B%20%7C%2011%2B%20%7C%2012%2B-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## 🚀 Tagline

**“Write your response JSON. Get your complete Laravel backend.”**

---

## ✨ What is Laravel ReverseKit?

Laravel ReverseKit is a **rule-based scaffolding package** that generates your entire Laravel backend from a JSON structure—**no AI required**.
It’s perfect for:

* Rapid API prototyping
* SaaS apps and microservices
* Learning Laravel conventions
* Reducing repetitive CRUD boilerplate

**Core Idea:** Reverse your development workflow. Instead of writing controllers → responses, define your response first, and let ReverseKit generate the backend.

---

## ⚡ Features

| Feature                    | Description                                                                   |
| -------------------------- | ----------------------------------------------------------------------------- |
| **Zero AI Dependencies**   | Pure PHP, no external APIs required                                           |
| **Complete Scaffolding**   | Models, Controllers, Resources, Requests, Policies, Factories, Seeders, Tests |
| **Smart Type Inference**   | Detects types from JSON values                                                |
| **Relationship Detection** | `hasMany` and `belongsTo` from nested structures                              |
| **Customizable Stubs**     | Modify templates for your coding standards                                    |
| **Preview Mode**           | See what will be generated without writing files                              |
| **Multiple Input Sources** | JSON, API URL, OpenAPI/Swagger, Postman, Database                             |
| **Interactive Mode**       | Step-by-step generator prompts for full control                               |

---

## 💾 Installation

```bash
composer require shaqilabs/laravel-reversekit
```

Auto-discovery registers the service provider.
Optional publishing:

```bash
# Config file
php artisan vendor:publish --tag=reversekit-config

# Stubs for customization
php artisan vendor:publish --tag=reversekit-stubs
```

---

## ⚙️ Usage

### Generate from JSON File

```bash
php artisan reverse:generate path/to/your.json
```

### Generate from JSON String

```bash
php artisan reverse:generate '{"user":{"id":1,"name":"John"}}'
```

### Preview Mode

```bash
php artisan reverse:generate data.json --preview
```

### Custom Options

```bash
php artisan reverse:generate data.json \
    --only=model,migration,controller \
    --module=Blog \
    --namespace=App\\Domain \
    --force
```

### From API URL

```bash
php artisan reverse:generate --from-url=https://api.example.com/users --auth-token=token
```

### From OpenAPI / Postman

```bash
php artisan reverse:generate --from-openapi=spec.yaml
php artisan reverse:generate --from-postman=collection.json
```

### Interactive Mode

```bash
php artisan reverse:interactive
```

Guides you through models, fields, relationships, and generator selection.

---

## 🛠 Generated Components

| Component     | Description                              |
| ------------- | ---------------------------------------- |
| Models        | `$fillable`, `$casts`, and relationships |
| Migrations    | Column types inferred from JSON          |
| Controllers   | CRUD methods returning JSON              |
| API Resources | Maps models to JSON structure            |
| Form Requests | Validation for Store & Update            |
| Policies      | Ownership checks where applicable        |
| Factories     | Model factories with Faker               |
| Seeders       | Intelligent counts based on JSON         |
| Feature Tests | Test cases for all CRUD endpoints        |
| Routes        | Auto-registered via `apiResource`        |

---

## 📊 Type & Relationship Mapping

| JSON Value       | PHP Type   | Migration             | Relationship |
| ---------------- | ---------- | --------------------- | ------------ |
| String           | string     | VARCHAR(255)          | -            |
| Integer          | int        | INTEGER               | -            |
| Boolean          | bool       | BOOLEAN               | -            |
| Float            | float      | DECIMAL(10,2)         | -            |
| Null             | string     | nullable()            | -            |
| ISO 8601 Date    | datetime   | TIMESTAMP             | -            |
| Array of Objects | Collection | Foreign key on child  | hasMany      |
| Nested Object    | Model      | Foreign key on parent | belongsTo    |

**Example:**

```json
{
  "user": {
    "id": 1,
    "posts": [{"id":1,"title":"Hello"}]
  }
}
```

Generates:

* `User` model with `hasMany` `posts()`
* `Post` model with `belongsTo` `user()`
* Migration adds `user_id` foreign key

---

## ⚡ Quick Start Example

Input JSON:

```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@test.com",
    "posts": [
      {"id":1,"title":"First Post","body":"Content","published":true}
    ]
  }
}
```

Run:

```bash
php artisan reverse:generate input.json
```

Generates:

```
app/Models/User.php
app/Models/Post.php
app/Http/Controllers/UserController.php
app/Http/Controllers/PostController.php
app/Http/Resources/UserResource.php
app/Http/Resources/PostResource.php
app/Policies/UserPolicy.php
app/Policies/PostPolicy.php
database/migrations/xxxx_create_users_table.php
database/migrations/xxxx_create_posts_table.php
tests/Feature/UserTest.php
tests/Feature/PostTest.php
routes/api.php
```

---

## ⚙️ Configuration

```php
return [
    'generators' => [
        'model' => true,
        'migration' => true,
        'controller' => true,
        'resource' => true,
        'request' => true,
        'policy' => true,
        'factory' => true,
        'seeder' => true,
        'test' => true,
    ],
    'model' => ['use_soft_deletes' => false, 'use_uuid' => false],
    'controller' => ['use_form_requests' => true, 'use_policies' => true],
];
```

---

## 🎨 Customize Stubs

Edit published stubs in `resources/stubs/reversekit/` to match your coding style.

---

## ✅ Requirements

* PHP 8.2+
* Laravel 10, 11, 12+

---

## 📜 License

MIT License – Open source, free for commercial projects.

---

Made with ❤️ by **Shaqi Labs**
