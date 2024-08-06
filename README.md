# Laravel API
An e-commerce API developed using the Laravel Framework, following REST architectural standards. This project includes features such as user authentication with JWT, role-based access control, resource management, and support for various CRUD operations.

<a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a>

## Introduction

This project is a simple e-commerce API designed to manage users, products, orders, and authentication. It is built with the Laravel framework, leveraging JWT for secure authentication. Through this project, I have practiced writing APIs following REST architecture and gained deeper insights into backend development. The project will continue to be updated and improved over time (if I find the time 😅), but sure I will do that.

This is the second version of the project with many changes and improvements in authentication, endpoints, methods, and more. All changes are detailed below, and you can view the original project https://github.com/ptduy14/laravel-api.

## New Change
- Added Swagger for API documentation.
- Switched authentication to Sanctum for simpler token issuance without relying on external packages.
- Customized HTTP exceptions.
- Adjusted endpoint URLs for better user-friendliness.
- Fixed bugs in the cart module (update cart).
- Resolved authentication issues.
- Added enums to define order statuses.

## API Endpoints


### Authentication

- Register: `POST /auth/register`
- Login: `POST /auth/login`
- Get Me: `GET /auth/me` (requires auth:sanctum middleware)
- Logout: `GET /auth/logout` (requires auth:sanctum middleware)
- Update Profile: `PATCH /auth/update` (requires auth:sanctum middleware)
- Change Password: `PATCH /auth/change-password` (requires auth:sanctum middleware)

## Users

- Get All Users: `GET /users` (requires role:admin|super-admin middleware)
- Get User by ID: `GET /users/{id}` (requires role:admin|super-admin middleware)
- Create User: `POST /users` (requires role:admin|super-admin middleware)
- Update User: `PATCH /users/{id}` (requires role:admin|super-admin middleware)
- Delete User: `DELETE /users/{id}` (requires role:admin|super-admin middleware)

### User Orders (requires role:user middleware)

- Get User Orders: `GET /users/orders`
- Get User Order by ID: `GET /users/orders/{id}`
- Create User Order: `POST /users/orders`

### Carts (requires role:user middleware)

- Get Current Carts: `GET /carts`
- Add Product to Cart: `POST /carts/products`
- Update Products in Cart: `PATCH /carts/products`
- Delete Product from Cart: `DELETE /carts/products/{id}`

### Categories

- Get All Categories: `GET /categories`
- Get Category by ID: `GET /categories/{id}`
- Create Category: `POST /categories` (requires role:admin|super-admin middleware)
- Update Category: `PATCH /categories/{id}` (requires role:admin|super-admin middleware)
- Delete Category: `DELETE /categories/{id}` (requires role:admin|super-admin middleware)
- Get Products of Category: `GET /categories/{id}/products`

Products

- Get All Products: `GET /products`
- Get Product by ID: `GET /products/{id}`
- Create Product: `POST /products` (requires role:admin|super-admin middleware)
- Update Product: `PATCH /products/{id}` (requires role:admin|super-admin middleware)
- Delete Product: `DELETE /products/{id}` (requires role:admin|super-admin middleware)
- Get Product Detail: `GET /products/{id}/details`

### Product Details (requires role:admin|super-admin middleware)

- Create Product Detail: `POST /products/{id}/detail`
- Update Product Detail: `PATCH /products/{id}/detail`
- Delete Product Detail: `DELETE /products/{id}/detail`

### Orders

- Get All Orders: `GET /orders` (requires role:admin|super-admin middleware)
- Update Order Status: `PATCH /orders/{id}` (requires role:admin|super-admin middleware)

### Middleware:

- `auth:api`: Use Passport for OAuth2 authentication..
- `auth:jwt`:Use JWT for authentication.
- `role`:Check the user's permissions, for example `role:super-admin`, `role:admin|super-admin`, `role:user`
