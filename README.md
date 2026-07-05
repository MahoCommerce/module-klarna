# Maho Klarna

![Maho Commerce](https://img.shields.io/badge/Maho_Commerce-module-orange)
![License](https://img.shields.io/badge/license-Apache--2.0-blue)
![PHP](https://img.shields.io/badge/php-%3E%3D8.3-8892BF)
![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen)

**Klarna Payments** integration for [Maho Commerce](https://mahocommerce.com).

This is a port of Klarna's Magento 1.x plugin to Maho, comprising three modules:

- **Klarna_Core** — shared REST client, request/order-line builders, abstract payment method, and the `klarna/notification` callback controller.
- **Klarna_Payments** — the customer-facing Klarna Payments method (checkout iframe, session create/update, authorization).
- **Klarna_OrderManagement** — post-purchase operations (capture, refund, cancel, extend authorization) via Klarna's Order Management API.

See https://developers.klarna.com/documentation/klarna-payments/

## Requirements

- PHP >= 8.3
- Maho Commerce >= 26.5
- An account with [Klarna](https://www.klarna.com)

## Installation

```bash
composer require fballiano/module-klarna
```

Clear the cache after installation:

```bash
./maho cache:flush
```

## Disclaimer

The original Klarna Magento 1 plugins were offered as-is; with the deprecation of Magento 1, Klarna
no longer maintains them. This Maho port is an independent effort and is likewise provided **as-is**.
Klarna's API can change over time; the user assumes full responsibility for maintaining their store
and its Klarna integration, including updating for API changes and security fixes.

## License

Original code Copyright 2015-2020 Klarna Bank AB (publ), licensed under the
[Apache License, Version 2.0](LICENSE.txt). Maho port modifications are distributed under the same
license.

## Links

- [Maho Commerce](https://mahocommerce.com)
- [Klarna](https://www.klarna.com)
- [Klarna Payments documentation](https://developers.klarna.com/documentation/klarna-payments/)
