<?php
declare(strict_types=1);

namespace Redsys;

/** The step of the 3-D Secure (EMV 3DS) flow a REST request is in (`emv3ds.threeDSInfo`). */
enum ThreeDsInfo: string
{
    /** Asking which verification the card needs: the first call, to `iniciaPeticionREST`. */
    case CardData = 'CardData';
    /** Sending the browser's data to start the verification. */
    case AuthenticationData = 'AuthenticationData';
    /** Giving back the result of the payer's challenge. */
    case ChallengeResponse = 'ChallengeResponse';
}
