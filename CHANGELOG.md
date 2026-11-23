# Changelog

## [0.1.0]

First release. Redsys TPV Virtual for PHP 8.4 on `dmorenof/sdk-runtime` ^1.12.0:

- Redirection payments (`RedirectForm`) and REST payments with 3-D Secure (`Gateway`: `iniciaPeticionREST` and `trataPeticionREST`).
- Signed requests (HMAC_SHA512_V2, plus the older HMAC_SHA256_V1 and SHA256_V1 versions) and verified answers (`InvalidSignatureException`).
- Typed parameters and results (`Dto\Parameters`, `Dto\Result`, `Dto\Emv3ds`), environments (development, integration, test, live) and response codes.
- Verified against the real Redsys test environment (see the README for what that covers and what it does not).
