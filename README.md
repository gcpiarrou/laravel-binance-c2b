[![Persiscal](https://uploads-ssl.webflow.com/6261bc25231a0e9384ecec75/62c502efab21659e9c2d04af_logofirma_persiscal-p-500.png)](https://www.persiscal.com/)

<h2>
    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fc/Binance-coin-bnb-logo.png/240px-Binance-coin-bnb-logo.png" alt="binance" width="20" height="20" />
    Laravel Binance C2B integration
</h2>
Binance Merchant Acquiring (C2B) integration made for Laravel 8.


## Getting started

This integration covers the next API functions:
- [Create Order](https://developers.binance.com/docs/binance-pay/api-order-create-v2)
- [Query Order](https://developers.binance.com/docs/binance-pay/api-order-query-v2)
- [Close Order](https://developers.binance.com/docs/binance-pay/api-order-close)
- [Refund Order](https://developers.binance.com/docs/binance-pay/api-order-refund)
- [Query Refund Order](https://developers.binance.com/docs/binance-pay/api-order-refund-query)
- [Wallet Balance Query V2](https://developers.binance.com/docs/binance-pay/api-balance-query-v2)

This integration complies with the [protocol rules](https://developers.binance.com/docs/binance-pay/api-common#protocol-rules) and generates the [requests headers](https://developers.binance.com/docs/binance-pay/api-common#request-header) as specified by Binance.

## Installation

To start using the integration in your project you have to:
1. Install the composer package using ```composer require persiscal/binance```.
2. Configure your .env with the [.env](/src/.env) variables, replacing the default **BINANCE_KEY** and **BINANCE_SECRET** with your [Binance C2B credentials](https://developers.binance.com/docs/binance-pay/authentication).
3. Configure your web routes as in [the web routes](src/routes/web.php).
4. (Optional) Configure the [binance-api](src/config/binance-api.php) **successRouteName**, **cancelRouteName** and **webhookRouteName** if needed.

## Testing

This package contains two commands.

The first one will return the balance of the account.

```
php artisan binance:get-balance
```


The latter one may help to test and troubleshoot the creation, querying and closing of a test order.

```
php artisan binance:test-order
```

## Support
If you're having an issue or you find a bug, feel free to send me an email to [gcampana@persiscal.com](mailto:gcampana@persiscal.com).

## Roadmap

- [ ]  [Webhook signature verification](https://developers.binance.com/docs/binance-pay/webhook-common#verify-the-signature)
- [ ] [Webhook order verification](https://developers.binance.com/docs/binance-pay/order-notification)
- [ ] [Refund order verification](https://developers.binance.com/docs/binance-pay/refund-order-notification)


## Contributing

Please refer to each project's style and contribution guidelines for submitting patches and additions. In general, we follow the "fork-and-pull" Git workflow.

1. Fork the repo on GitLab
2. Clone the project to your own machine
3. Commit changes to your own branch
4. Push your work back up to your fork
5. Submit a Pull request so that we can review your changes

NOTE: Be sure to merge the latest from "upstream" before making a pull request!

## Authors
- [Gaston Campana Piarrou](https://github.com/gcpiarrou) - Member of [Persiscal](https://www.persiscal.com/)

## Aknowledgement
- Nonce string generation made by [Monyancha](https://github.com/Monyancha/binance-pay-api-php-laravel-curl)
- HandlesResponseErrors trait inspired on [TechTailor](https://github.com/TechTailor/Laravel-Binance-Api)


## License
The MIT License (MIT). Please see [License File](https://gitlab.com/gastoncampana/laravel-binance-c2b/-/blob/main/LICENSE) for more information.

