<?php

abstract class LinkIDPaymentState
{

    const STARTED = 0; // payment is being processed
    const WAITING_FOR_UPDATE = 1; // linkID stopped waiting for status update, SP will be informed on payment status change
    const FAILED = 2; // payment has failed
    const REFUNDED = 3; // payment has been refunded
    const REFUND_STARTED = 4; // payment refund has started
    const PAYED = 5; // completed

}

function parseLinkIDPaymentState($paymentState)
{

    if (null == $paymentState) return null;

    if ($paymentState == "STARTED") {
        return LinkIDPaymentState::STARTED;
    } else if ($paymentState == "WAITING_FOR_UPDATE") {
        return LinkIDPaymentState::WAITING_FOR_UPDATE;
    } else if ($paymentState == "FAILED") {
        return LinkIDPaymentState::FAILED;
    } else if ($paymentState == "REFUNDED") {
        return LinkIDPaymentState::REFUNDED;
    } else if ($paymentState == "REFUND_STARTED") {
        return LinkIDPaymentState::REFUND_STARTED;
    } else if ($paymentState == "PAYED" || $paymentState == "AUTHORIZED") {
        return LinkIDPaymentState::PAYED;
    }

    throw new Exception("Unexpected payment state: " . $paymentState);

}
