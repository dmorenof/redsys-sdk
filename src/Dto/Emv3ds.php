<?php
declare(strict_types=1);

namespace Redsys\Dto;

use Dmorenof\SdkRuntime\DTO;

/**
 * The 3-D Secure (EMV 3DS) data of a REST operation, in both directions. Fields Redsys adds are kept in `additionalProperties()`.
 */
final class Emv3ds extends DTO
{
    public const string ADDITIONAL_PROPERTIES_TYPE = 'mixed';
    public const bool ADDITIONAL_PROPERTIES_NULLABLE = true;

    /** 3-D Secure protocol version the card supports (answered to CardData, repeated afterwards). */
    public ?string $protocolVersion;

    /** The step of the flow: a {@see \Redsys\ThreeDsInfo} value when you send it; Redsys answers with others too (`CardConfiguration`). */
    public ?string $threeDSInfo;

    /** Identifier of the 3-D Secure transaction. */
    public ?string $threeDSServerTransID;

    /** Address of the card issuer's method page, when it has one. */
    public ?string $threeDSMethodURL;

    /** Address the challenge is posted to. */
    public ?string $acsURL;

    /** The challenge request to post to `acsURL` (the field `CReq`). */
    public ?string $creq;

    /** The challenge response the issuer posted back. */
    public ?string $cres;

    /** Where the issuer posts the result of the challenge. */
    public ?string $notificationURL;

    /** `Accept` header of the payer's browser. */
    public ?string $browserAcceptHeader;

    /** User agent of the payer's browser. */
    public ?string $browserUserAgent;

    /** Whether the browser runs Java. */
    public ?bool $browserJavaEnabled;

    /** Whether the browser runs JavaScript. */
    public ?bool $browserJavascriptEnabled;

    /** Language of the browser. */
    public ?string $browserLanguage;

    /** Colour depth of the screen. */
    public ?string $browserColorDepth;

    /** Height of the screen. */
    public ?string $browserScreenHeight;

    /** Width of the screen. */
    public ?string $browserScreenWidth;

    /** Time zone offset of the browser, in minutes. */
    public ?string $browserTZ;
}
