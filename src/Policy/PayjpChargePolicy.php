<?php

declare(strict_types=1);

namespace Payjp\Policy;

use Authorization\IdentityInterface;
use Payjp\Model\Entity\PayjpCharge;

class PayjpChargePolicy
{
    public function canView(IdentityInterface $identity, PayjpCharge $resource): bool
    {
        return true;
    }

    public function canAdd(IdentityInterface $identity, PayjpCharge $resource): bool
    {
        return true;
    }

    public function canEdit(IdentityInterface $identity, PayjpCharge $resource): bool
    {
        return true;
    }

    public function canDelete(IdentityInterface $identity, PayjpCharge $resource): bool
    {
        return true;
    }
}
