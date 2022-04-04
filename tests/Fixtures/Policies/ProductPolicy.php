<?php

namespace JeffersonSimaoGoncalves\Multitenancy\Tests\Fixtures\Policies;

class ProductPolicy
{
    public function view()
    {
        return false;
    }
}
