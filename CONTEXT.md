# Ecommerce API — Context

## Stack
- **Framework**: Laravel 12 (PHP 8.2+)
- **Auth**: JWT (`tymon/jwt-auth ^2.3`)
- **Permissions**: `spatie/laravel-permission ^6.25`
- **API Docs**: `darkaonline/l5-swagger ^11.1` (OpenAPI via PHP Attributes)
- **DB**: SQLite (dev), configurable via `.env`

---

## Arsitektur

Request flow:
```
Request
  → GlobalExceptionHandler (bootstrap/app.php)
  → Route (routes/api.php)
  → Controller (extends BaseApiController)
  → Service (pure logic, return data)
  → Repository (implements Interface, akses Eloquent)
  → Model
```

### Pola yang digunakan
- **Repository Pattern** — semua akses DB lewat Repository
- **Service Layer** — business logic, return data murni (bukan JsonResponse)
- **BaseApiController** — helper response: `successResponse`, `createdResponse`, `updatedResponse`, `messageResponse`, `errorResponse`
- **Global Exception Handler** — tangani semua exception di `bootstrap/app.php` secara terpusat

---

## Struktur Database

### `users`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | string | |
| email | string unique | |
| email_verified_at | timestamp nullable | |
| password | string | hashed |
| remember_token | string nullable | |
| created_at / updated_at | timestamp | |

### `categories`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | string | |
| slug | string unique | auto-generate dari name |
| description | text nullable | |
| is_active | boolean | default: true |
| created_at / updated_at | timestamp | |

### `products`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| category_id | bigint FK → categories | cascade delete |
| name | string | |
| slug | string unique | auto-generate dari name |
| description | text nullable | |
| price | decimal(12,2) | |
| stock | integer | default: 0 |
| image | string nullable | path storage public |
| is_active | boolean | default: true |
| created_at / updated_at | timestamp | |

### Tabel lain (framework)
- `password_reset_tokens`, `sessions`, `cache`, `jobs`
- `roles`, `permissions`, `model_has_roles`, dst. (spatie/permission)

---

## API Endpoints

Base URL: `/api/v1`

### Auth (`/auth`)
| Method | Endpoint | Auth | Keterangan |
|---|---|---|---|
| POST | `/auth/register` | - | Register user baru, return JWT token |
| POST | `/auth/login` | - | Login, return JWT token |
| GET | `/auth/me` | ✓ | Data user yang sedang login |
| POST | `/auth/logout` | ✓ | Invalidate token |
| POST | `/auth/refresh` | ✓ | Refresh JWT token |

### Categories (`/categories`)
| Method | Endpoint | Auth | Keterangan |
|---|---|---|---|
| GET | `/categories` | - | List semua category (urut nama) |
| POST | `/categories` | - | Buat category baru |
| GET | `/categories/{id}` | - | Detail satu category |
| PUT | `/categories/{id}` | - | Update category |
| DELETE | `/categories/{id}` | - | Hapus category |

> ⚠️ Category belum di-protect `auth:api` middleware. Perlu ditambahkan nanti.

### Products (`/products`)
| Method | Endpoint | Auth | Keterangan |
|---|---|---|---|
| GET | `/products` | - | List produk dengan paginate, search, filter, sort |
| POST | `/products` | - | Buat produk baru (support upload image) |
| GET | `/products/{id}` | - | Detail satu produk |
| PUT | `/products/{id}` | - | Update produk (support upload image) |
| DELETE | `/products/{id}` | - | Hapus produk |

#### Query params `GET /products`
| Param | Keterangan |
|---|---|
| `search` | Cari di `name` atau `description` |
| `category_id` | Filter by category |
| `is_active` | Filter `true`/`false` |
| `min_price` | Harga minimum |
| `max_price` | Harga maksimum |
| `sort_by` | `name`, `price`, `stock`, `created_at` |
| `sort_dir` | `asc` / `desc` |
| `per_page` | Jumlah per halaman (default 15) |

> ⚠️ Product belum di-protect `auth:api` middleware. Perlu ditambahkan nanti.

---

## File Penting

### Controllers
```
app/Http/Controllers/Api/BaseApiController.php   ← base semua API controller
app/Http/Controllers/Api/CategoryController.php
app/Http/Controllers/Api/ProductController.php
app/Http/Controllers/Api/Auth/AuthController.php
```

### Services
```
app/Services/CategoryService.php   ← logic, return data murni
app/Services/ProductService.php    ← logic, return data murni, handle image upload
app/Services/AuthService.php       ← logic, return data murni
```

### Repositories
```
app/Interfaces/CategoryRepositoryInterface.php
app/Repositories/CategoryRepository.php
app/Interfaces/ProductRepositoryInterface.php
app/Repositories/ProductRepository.php   ← paginate + search + filter + sort
```

### Requests
```
app/Http/Requests/Auth/LoginRequest.php
app/Http/Requests/Auth/RegisterRequest.php
app/Http/Requests/Category/StoreCategoryRequest.php
app/Http/Requests/Category/UpdateCategoryRequest.php
app/Http/Requests/Product/StoreProductRequest.php
app/Http/Requests/Product/UpdateProductRequest.php
```

### Resources
```
app/Http/Resources/UserResource.php
app/Http/Resources/CategoryResource.php
app/Http/Resources/ProductResource.php   ← include category (whenLoaded)
```

### Providers
```
app/Providers/AppServiceProvider.php   ← binding:
                                          CategoryRepositoryInterface → CategoryRepository
                                          ProductRepositoryInterface  → ProductRepository
```

### Exception Handler
```
bootstrap/app.php   ← withExceptions() — handle ModelNotFound, NotFound, MethodNotAllowed,
                       AuthenticationException, AccessDenied, ValidationException
```

---

## Global Exception Handler

Semua exception API di-handle di `bootstrap/app.php → withExceptions()`:

| Exception | HTTP Status | Response |
|---|---|---|
| `ModelNotFoundException` | 404 | `{Model} not found.` |
| `NotFoundHttpException` | 404 | `Endpoint not found.` |
| `MethodNotAllowedHttpException` | 405 | `Method not allowed.` |
| `AuthenticationException` | 401 | pesan exception atau `Unauthenticated.` |
| `AccessDeniedHttpException` | 403 | `Forbidden.` |
| `ValidationException` | 422 | `Validation failed.` + `errors` object |

---

## Format Response

### Success
```json
{ "success": true, "data": {...} }
{ "success": true, "message": "...", "data": {...} }
{ "success": true, "message": "..." }
```

### Error
```json
{ "success": false, "message": "..." }
{ "success": false, "message": "Validation failed.", "errors": { "field": ["..."] } }
```

---

## Hal yang Belum Dikerjakan
- [x] ~~Product API (CRUD)~~
- [x] ~~Product search, filter, sort, paginate~~
- [ ] Cart API
- [ ] Checkout / Order API
- [ ] Role & Permission setup (spatie sudah install, belum dipakai)
- [ ] Category & Product endpoint belum di-protect `auth:api`
- [ ] Unit / Feature tests
