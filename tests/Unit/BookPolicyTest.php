<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\User;
use App\Policies\BookPolicy;
use App\Services\GuestSessionService;
use Tests\TestCase;

class BookPolicyTest extends TestCase
{
    public function test_social_media_crawler_is_granted_view_access(): void
    {
        $guestSession = $this->createMock(GuestSessionService::class);
        $policy = new BookPolicy($guestSession);

        request()->headers->set('User-Agent', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)');

        $book = new Book(['user_id' => 99]);

        $this->assertTrue($policy->view(null, $book));
    }

    public function test_unauthenticated_guest_can_view_demo_book(): void
    {
        $guestSession = $this->createMock(GuestSessionService::class);
        $guestSession->method('guestOwnsBook')->willReturn(false);
        $policy = new BookPolicy($guestSession);

        request()->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

        $book = new Book(['user_id' => null]);

        $this->assertTrue($policy->view(null, $book));
    }

    public function test_authenticated_owner_can_view_private_book(): void
    {
        $guestSession = $this->createMock(GuestSessionService::class);
        $policy = new BookPolicy($guestSession);

        request()->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

        $user = new User(['role' => 'user']);
        $user->id = 15;

        $book = new Book(['user_id' => 15]);

        $this->assertTrue($policy->view($user, $book));
    }

    public function test_other_user_cannot_view_private_book(): void
    {
        $guestSession = $this->createMock(GuestSessionService::class);
        $policy = new BookPolicy($guestSession);

        request()->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

        $user = new User(['role' => 'user']);
        $user->id = 15;

        $book = new Book(['user_id' => 99]);

        $this->assertFalse($policy->view($user, $book));
    }

    public function test_admin_can_view_any_private_book(): void
    {
        $guestSession = $this->createMock(GuestSessionService::class);
        $policy = new BookPolicy($guestSession);

        request()->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

        $admin = new User(['role' => 'admin']);
        $admin->id = 1;

        $book = new Book(['user_id' => 99]);

        $this->assertTrue($policy->view($admin, $book));
    }
}
