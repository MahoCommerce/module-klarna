# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A **Magento 1.x (community codePool) extension** that integrates Klarna Payments. It ships as three
interdependent modules under `app/code/community/Klarna/`:

- **Klarna_Core** (v1.3.5) — shared foundation: REST client, request/order-line builders, abstract
  payment method, the `klarna/notification` front controller, and the `klarna_order` DB table.
- **Klarna_Payments** (v4.4.5) — the customer-facing payment method (checkout iframe, session
  create/update, order placement / authorization). Depends on Core.
- **Klarna_OrderManagement** (v1.2.2) — post-purchase operations against Klarna's Order Management
  API (capture, refund, cancel, extend authorization, update items/addresses). Depends on Core.

There is **no build system, package manager, or automated test suite** — it is plain PHP deployed
into a Magento 1 install. Note the module is unmaintained/EOL per `README.md` (Klarna deprecated M1),
but that disclaimer is Klarna's; treat the repo as a normal codebase for edits.

## Deployment

The repo is a [modman](https://github.com/colinmollenhour/modman) package (`modman` file at root),
symlinked into a Magento 1 document root. There is no way to "run" the code standalone — testing
means deploying into a working Magento 1 store (`modman deploy`, then flush cache under
`var/cache`). `.gitattributes` marks `modman`/`.gitignore` as `export-ignore` so they're stripped
from release archives.

## Architecture

The design leans hard on **config-driven abstraction** so behavior is selected by XML config, not
hardcoded classes. When adding features, follow the existing indirection rather than instantiating
concrete classes directly.

### API layering (Core → concrete)
Klarna API access is a three-tier abstraction, all rooted in Core:

1. **REST transport** — `Klarna_Core_Model_Api_Rest_Client` (Zend_Http wrapper) +
   `Client/Request`, `Client/Response`, `Client/Abstract`. Concrete endpoint groups extend
   `Client_Abstract`: `Klarna_Payments_Model_Api_Rest_Payments` (session/placeOrder) and
   `Klarna_OrderManagement_Model_Api_Rest_Ordermanagement` (capture/refund/cancel/etc).
2. **API type objects** — `ApiTypeAbstract` → `PurchaseAbstract` / `PostPurchaseAbstract`
   (with `PurchaseApiInterface` / `PostPurchaseApiInterface`). The concrete "Kasper" implementations
   live in `Klarna_Payments/Model/Api/Kasper/Purchase.php` and
   `Klarna_OrderManagement/Model/Api/Kasper/PostPurchase.php`.
3. **Request builders** — `Api_Builder_Abstract` turns a Magento quote/order into a Klarna request
   payload. `Klarna_Payments_Model_Api_Builder_Kasper` builds create/update/place/client_update
   payloads.

`Klarna_Core_Helper_Data` is the **resolver** that maps store config (API version/type per store)
to the correct concrete purchase / post-purchase instances (`getPurchaseApiInstance`,
`getPostPurchaseApiInstance`, `getApiVersion`, `getApiType`). API base URLs (production vs.
`testdrive`/playground, per region NA/EU/OC) are defined in `Klarna_Core/etc/config.xml` under
`klarna/api_versions`.

### Order lines (totals mapping)
The cart→Klarna order-line conversion is a **collector of pluggable builders** under
`Klarna_Core/Model/Api/Builder/Orderline/` (Items, Shipping, Tax, Discount, Giftcard, Surcharge,
Customerbalance, Reward…). New total types are registered in `config.xml` under the orderline node
and picked up by `Orderline/Collector`, not called directly. Tax/discount/FPT(Weee) handling is
config-sensitive — see the `get*` flags in `Klarna_Core_Helper_Data` (`getSeparateTaxLine`,
`getPriceExcludesVat`, `getTaxBeforeDiscount`, `getDisplayInSubtotalFPT`).

### Payment method flow
`Klarna_Payments_Model_Payment_Payments` extends `Klarna_Core_Model_Payment_Method_Abstract`
(itself a `Mage_Payment_Model_Method_Abstract`). Klarna Payments is a **dynamic payment method**:
one Magento method backs multiple Klarna payment categories, with `setCode`/`setTitle`/
`setCategoryInformation` populated at runtime. `authorize()`/`capture()`/`refund()`/`cancel()`/
`void()` delegate to the resolved API instances rather than talking to Klarna directly.

### Notifications
`Klarna_Core_NotificationController` (route `klarna/notification`, registered as a `secure_url`)
receives Klarna's server-to-server callbacks: `indexAction` (fraud/order push) and `pushAction`.
`_cancelFailedOrder` handles rejected reservations.

### Events
Cross-module wiring is via Magento observers (see `<events>` in each `config.xml`), not direct
calls. Key ones: Core observes `sales_order_payment_capture` (`prepareCapture`); Payments observes
payment-method availability, order-email sending, payment-type recording, and checkout-session
clearing, and hooks `klarna_payments_request_create_after` / the UA-string event. OrderManagement
appends its module version to the client User-Agent via `klarnaCoreClientUserAgentString`.

## Conventions

- **Magento 1 class autoloading**: PSR-0-style `Underscore_Separated` class names must match the
  directory path exactly (`Klarna_Core_Model_Api_Rest_Client` →
  `Klarna/Core/Model/Api/Rest/Client.php`). Config aliases are lowercase (`klarna_core/observer`).
- **Schema/version changes**: bump the `<version>` in the module's `etc/config.xml` AND add a
  matching `sql/<setup>/upgrade-X-Y.php` (or `install-X.php`) script. Magento runs these on version
  mismatch. See existing scripts in `Klarna_Payments/sql/klarna_payments_setup/`.
- **New admin config fields** go in `etc/system.xml` with defaults in `etc/config.xml`; option
  sources live under `Model/System/Config/Source/`.
- **Money**: pass amounts through `Klarna_Core_Helper_Data::toApiFloat()` — Klarna expects integer
  minor units.
- Frontend assets: templates in `app/design/frontend/base/default/template/klarnapayments/`
  (incl. OneStepCheckout `osc.phtml`/`header-osc.phtml` and `native.phtml`), layout in
  `app/design/frontend/base/default/layout/klarna_payments.xml`, JS in
  `skin/frontend/base/default/klarnapayment/js/`.
- User-facing strings go in the `app/locale/en_US/Klarna_*.csv` translation files.
- Record user-visible changes in `CHANGELOG.md` (reverse-chronological, `version / date` heading).
