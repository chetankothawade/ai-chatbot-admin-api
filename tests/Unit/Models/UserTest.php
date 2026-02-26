<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_activity_description_for_known_events(): void
    {
        $user = new User();

        $this->assertSame('User activated', $user->getActivityDescription('activated'));
        $this->assertSame('User login', $user->getActivityDescription('login'));
        $this->assertSame('User custom_event', $user->getActivityDescription('custom_event'));
    }
}
