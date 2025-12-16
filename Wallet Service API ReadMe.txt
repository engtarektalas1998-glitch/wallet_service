# Wallet Service API

REST API built with Laravel 10 that simulates a wallet service.

## Requirements
- PHP 8.1+
- Composer
- MySQL or any supported database

## Setup
1. Clone the repository
2. Run `composer install`
3. Copy `.env.example` to `.env`
4. Configure database credentials
5. Run `php artisan key:generate`
6. Run `php artisan migrate`
7. Run `php artisan serve`

## Endpoints

### Wallets
POST /api/wallets  
Body:
{
  "owner_name": "John",
  "currency": "USD"
}

GET /api/wallets  
Optional query params: owner, currency

GET /api/wallets/{id}

GET /api/wallets/{id}/balance

### Deposits
POST /api/wallets/{id}/deposit  
Headers:
Idempotency-Key: unique-key  
Body:
{
  "amount": 100
}

### Withdrawals
POST /api/wallets/{id}/withdraw  
Headers:
Idempotency-Key: unique-key  
Body:
{
  "amount": 50
}

### Transfers
POST /api/transfers  
Headers:
Idempotency-Key: unique-key  
Body:
{
  "from_wallet_id": 1,
  "to_wallet_id": 2,
  "amount": 30
}

### Transactions History
GET /api/wallets/{id}/transactions  
Optional query params: type, from, to  
Pagination enabled

### Health
GET /api/health  
Response:
{
  "status": "ok"
}

## Notes
- All monetary values are stored as integers
- All operations are atomic.
- No authentication is implemented.
- Each request in postman collection has example under it.
