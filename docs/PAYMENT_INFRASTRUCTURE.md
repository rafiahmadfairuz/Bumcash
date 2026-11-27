# Payment Gateway Infrastructure

## Current status

Online payment is disabled by default. Bumcash has no selected provider and makes no external payment requests. The existing cash/bank transfer submission and manager verification flow remains unchanged and is separate from this gateway infrastructure.

## Architecture

- `config/payment.php` holds provider-neutral configuration.
- `PaymentGateway` defines create, retrieve, cancel, and refund operations; internal DTOs isolate business code from provider payloads.
- `PaymentManager` resolves the configured adapter and rejects disabled/unknown providers.
- `PaymentService` creates decimal-amount transactions from the database invoice, scopes invoices to their owner, serializes creation, and enforces state transitions.
- Resident utility bill payment is a separate two-sided flow: a `UtilityBillProvider` performs bill inquiry/settlement with a biller aggregator, while `PaymentGateway` collects payment from the resident. Do not charge the resident before a valid provider quote exists, and do not mark the provider bill paid until a verified provider result is received.
- `PaymentTransaction` stores gateway transactions separately from the existing manual verification records.
- `MockPaymentGateway` stores references locally and is available only in local/testing when explicitly enabled.
- `WebhookVerifier` is a provider-specific verification contract. No public webhook route is active until a provider-specific verifier exists. `WebhookEventProcessor` accepts only `VerifiedWebhookEvent` DTOs and checks provider, internal reference, amount, currency, and provider transaction reference before status changes; provider event keys are deduplicated.

## Configuration

```dotenv
PAYMENT_ENABLED=false
PAYMENT_MODE=development
PAYMENT_PROVIDER=
PAYMENT_CURRENCY=IDR
PAYMENT_CALLBACK_URL=
PAYMENT_WEBHOOK_URL=
PAYMENT_WEBHOOK_SECRET=
PAYMENT_API_KEY=
PAYMENT_API_SECRET=
PAYMENT_TIMEOUT=30
PAYMENT_RETRY_ENABLED=false
PAYMENT_MOCK_ENABLED=false
UTILITY_BILL_PAYMENTS_ENABLED=false
UTILITY_BILL_PROVIDER=
```

Provider credentials are generic optional configuration slots only. No provider-specific authentication or request is implemented. Retry remains disabled until a future adapter can guarantee idempotent provider operations.

Do not enable mock in production. For local development, configure `PAYMENT_ENABLED=true`, `PAYMENT_PROVIDER=mock`, and `PAYMENT_MOCK_ENABLED=true`. Create and transition simulation records through `PaymentService::create` and `PaymentService::simulateMockTransition` in a local-only Tinker session. Example: create a payment for an owned invoice with an idempotency key, then pass the returned transaction and `PaymentStatus::Paid` (or `Failed`, `Expired`, `Cancelled`) to `simulateMockTransition`. This only changes local simulation records; it is not a real payment. No simulation HTTP endpoint is exposed.

## Status and flow

Internal statuses are `pending`, `paid`, `failed`, `expired`, `cancelled`, and `refunded`. Pending may move to paid/failed/expired/cancelled; paid may move to refunded. Other terminal transitions are rejected. Marking paid requires a verified result. Payment state is separate from invoice/order state; gateway infrastructure does not mark an invoice settled or create cash journal entries. A future integration must explicitly implement the business policy for syncing a verified online payment to invoice and accounting records.

## Database

Migration `2026_11_27_000001_create_payment_gateway_tables` adds `payment_transactions` (foreign keys to invoice and user, decimal amount, currency, provider references, idempotency key, bounded metadata) and `payment_webhook_events` (unique provider/event key). Apply with `php artisan migrate`; migration is additive and does not modify existing payment tables.

## Provider adapter steps

1. Implement `PaymentGateway` using the provider's official API and map responses to internal DTOs/statuses.
2. Implement `WebhookVerifier` with that provider's documented signature and replay protections.
3. Add normalized webhook routing only after verification is implemented and tested.
4. Bind the configured provider in `PaymentManager`; add provider credentials only to deployment secrets and provider-specific configuration.
5. Add tests for signatures, idempotency, amount/reference matching, retries, and provider error mapping before enabling production.

No provider credentials, card data, CVV, PIN, or external payment methods are stored here. No live provider is implemented.

`UtilityBillProvider` defines the separate bill inquiry and fulfillment contract for PLN/PDAM/PBB-like services. No biller aggregator or resident-facing utility checkout is active while `UTILITY_BILL_PAYMENTS_ENABLED=false` and `UTILITY_BILL_PROVIDER` is unset. The existing pengelola utility form records only BUMDes operating expenses.
