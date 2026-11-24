<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserSupportController extends Controller
{
    /**
     * Support tickets and product conversations were both consolidated into
     * My Inquiries. The old URLs remain as redirects so bookmarks still work.
     */
    public function index()
    {
        return redirect()->route('user.inquiries');
    }

    public function storeTicket(Request $request)
    {
        return app(DashboardController::class)->storeInquiry($request);
    }

    public function showTicket($ticket)
    {
        return $this->legacyRedirect();
    }

    public function replyTicket(Request $request, $ticket)
    {
        return $this->legacyRedirect();
    }

    public function showConversation($conversation)
    {
        return $this->legacyRedirect();
    }

    public function replyConversation(Request $request, $conversation)
    {
        return $this->legacyRedirect();
    }

    private function legacyRedirect()
    {
        return redirect()->route('user.inquiries')
            ->with('info', 'Support tickets and product conversations are now handled in My Inquiries.');
    }
}
