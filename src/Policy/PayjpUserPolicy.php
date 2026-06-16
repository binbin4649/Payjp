<?php

declare(strict_types=1);

namespace Payjp\Policy;

use Authorization\IdentityInterface;
use Payjp\Model\Entity\PayjpUser;

class PayjpUserPolicy
{
    public function canView(IdentityInterface $identity, PayjpUser $resource): bool
    {
        return true;
    }

    public function canAdd(IdentityInterface $identity, PayjpUser $resource): bool
    {
        return true;
    }

    public function canEdit(IdentityInterface $identity, PayjpUser $resource): bool
    {
        return true;
    }

    public function canDelete(IdentityInterface $identity, PayjpUser $resource): bool
    {
        return true;
    }
}
