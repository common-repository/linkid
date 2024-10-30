<?php

abstract class LinkIDPaymentMethodType
{

    const UNKNOWN = 0;
    const VISA = 1;
    const MASTERCARD = 2;
    const SEPA = 3;
    const KLARNA = 4;

}

function parseLinkIDPaymentMethodType($paymentMethodType)
{

    if (null == $paymentMethodType) return null;

    if ($paymentMethodType == "UNKNOWN") {
        return LinkIDPaymentMethodType::UNKNOWN;
    } else if ($paymentMethodType == "VISA") {
        return LinkIDPaymentMethodType::VISA;
    } else if ($paymentMethodType == "MASTERCARD") {
        return LinkIDPaymentMethodType::MASTERCARD;
    } else if ($paymentMethodType == "SEPA") {
        return LinkIDPaymentMethodType::SEPA;
    } else if ($paymentMethodType == "KLARNA") {
        return LinkIDPaymentMethodType::KLARNA;
    }

    throw new Exception("Unexpected payment method type: " . $paymentMethodType);

}
