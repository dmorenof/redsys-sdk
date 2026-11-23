<?php
declare(strict_types=1);

namespace Redsys;

/** `Ds_Merchant_TransactionType`: what the terminal is asked to do. */
enum TransactionType: string
{
    case Authorisation = '0';
    case Preauthorisation = '1';
    case PreauthorisationConfirmation = '2';
    case Refund = '3';
    case RecurringTransaction = '5';
    case SuccessiveTransaction = '6';
    case Authentication = '7';
    case AuthenticationConfirmation = '8';
    case Void = '9';
    case ReplacementAuthorisation = '11';
    case Paygold = '15';
    case PaygoldConfirmation = '38';
    case Cancellation = '45';
    case VoidAuthenticationConfirmation = '47';
    case PartialConfirmation = '48';
    case DeferredAuthorisation = 'O';
    case DeferredAuthorisationConfirmation = 'P';
    case DeferredAuthorisationCancellation = 'Q';
    case DeferredFirstInstalment = 'R';
    case DeferredNextInstalment = 'S';
}
